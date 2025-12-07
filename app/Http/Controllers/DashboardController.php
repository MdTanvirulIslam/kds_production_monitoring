<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Worker;
use App\Models\ProductionLog;
use App\Models\LightIndicator;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index()
    {
        // Get today's date in Bangladesh timezone
        $today = Carbon::today('Asia/Dhaka');

        // Statistics
        $stats = [
            'total_tables' => Table::where('is_active', true)->count(),
            'total_workers' => Worker::where('is_active', true)->count(),
            'today_production' => ProductionLog::whereDate('production_date', $today)->sum('garments_count'),
            'active_alerts' => Table::where('current_light_status', 'red')->count(),
            'daily_target' => config('factory.daily_target', 1000),
        ];

        // Calculate progress percentage
        $stats['target_progress'] = $stats['daily_target'] > 0
            ? round(($stats['today_production'] / $stats['daily_target']) * 100, 1)
            : 0;

        // Tables with red alerts
        $alertTables = Table::where('current_light_status', 'red')
            ->where('is_active', true)
            ->with('currentAssignment.worker')
            ->get();

        // Top workers today - Fixed for MySQL strict mode
        $topWorkers = Worker::where('is_active', true)
            ->with('currentAssignment.table')
            ->get()
            ->map(function ($worker) use ($today) {
                $worker->today_production = ProductionLog::where('worker_id', $worker->id)
                    ->whereDate('production_date', $today)
                    ->sum('garments_count');
                return $worker;
            })
            ->filter(function ($worker) {
                return $worker->today_production > 0;
            })
            ->sortByDesc('today_production')
            ->take(10)
            ->values();

        // Recent production logs
        $recentLogs = ProductionLog::whereDate('production_date', $today)
            ->with(['table', 'worker', 'supervisor'])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        // Hourly production - using production_hour column directly
        // The production_hour is stored as "08:00", "09:00", etc.
        $hourlyProduction = ProductionLog::whereDate('production_date', $today)
            ->select('production_hour', DB::raw('SUM(garments_count) as total'))
            ->groupBy('production_hour')
            ->orderBy('production_hour')
            ->get()
            ->mapWithKeys(function ($item) {
                // Extract just the hour part: "08:00" -> "08", "11:00" -> "11"
                $hour = substr($item->production_hour, 0, 2);
                return [$hour => (int) $item->total];
            })
            ->toArray();

        // Get shifts
        $shifts = $this->getShifts();

        // Calculate production per shift
        $shiftProduction = $this->calculateShiftProduction($hourlyProduction, $shifts);

        return view('dashboard.index', compact(
            'stats',
            'alertTables',
            'topWorkers',
            'recentLogs',
            'hourlyProduction',
            'shifts',
            'shiftProduction'
        ));
    }

    /**
     * Get shifts from database or return defaults
     */
    private function getShifts()
    {
        // Try to get from database if Shift model exists
        if (class_exists('App\Models\Shift')) {
            try {
                $shifts = Shift::where('is_active', true)->orderBy('start_time')->get();
                if ($shifts->count() > 0) {
                    return $shifts;
                }
            } catch (\Exception $e) {
                // Table might not exist, use defaults
            }
        }

        // Return default shifts as collection
        return collect([
            (object)[
                'id' => 1,
                'name' => 'Morning',
                'start_time' => '06:00',
                'end_time' => '14:00',
            ],
            (object)[
                'id' => 2,
                'name' => 'Day',
                'start_time' => '14:00',
                'end_time' => '22:00',
            ],
            (object)[
                'id' => 3,
                'name' => 'Night',
                'start_time' => '22:00',
                'end_time' => '06:00',
            ],
        ]);
    }

    /**
     * Calculate production totals per shift
     */
    private function calculateShiftProduction($hourlyProduction, $shifts)
    {
        // Define hours for each shift
        $shiftHours = [
            1 => ['06', '07', '08', '09', '10', '11', '12', '13'], // Morning 6AM - 2PM
            2 => ['14', '15', '16', '17', '18', '19', '20', '21'], // Day 2PM - 10PM
            3 => ['22', '23', '00', '01', '02', '03', '04', '05'], // Night 10PM - 6AM
        ];

        $shiftProduction = [];

        foreach ($shifts as $shift) {
            $hours = $shiftHours[$shift->id] ?? [];
            $total = 0;

            foreach ($hours as $hour) {
                $total += $hourlyProduction[$hour] ?? 0;
            }

            $shiftProduction[$shift->id] = $total;
        }

        return $shiftProduction;
    }

    /**
     * Display live monitor
     */
    public function monitor()
    {
        $today = Carbon::today('Asia/Dhaka');
        $currentTime = Carbon::now('Asia/Dhaka');

        // Get all active tables with assignments
        $tables = Table::where('is_active', true)
            ->with(['currentAssignment.worker', 'currentAssignment.shift'])
            ->orderBy('table_number')
            ->get();

        // Get shifts
        $shifts = $this->getShifts();

        // Determine current shift based on time
        $currentShift = null;
        $currentHour = $currentTime->format('H:i');

        foreach ($shifts as $shift) {
            // Handle different time formats (H:i or H:i:s)
            $start = substr($shift->start_time, 0, 5); // Get only HH:MM
            $end = substr($shift->end_time, 0, 5);     // Get only HH:MM

            // Handle overnight shift (e.g., 22:00 - 06:00)
            if ($start > $end) {
                if ($currentHour >= $start || $currentHour < $end) {
                    $currentShift = $shift;
                    break;
                }
            } else {
                if ($currentHour >= $start && $currentHour < $end) {
                    $currentShift = $shift;
                    break;
                }
            }
        }

        // Calculate time remaining in current shift
        $shiftTimeRemaining = null;
        if ($currentShift) {
            try {
                // Get end time as HH:MM format
                $endTimeStr = substr($currentShift->end_time, 0, 5);
                $endTime = Carbon::createFromFormat('H:i', $endTimeStr, 'Asia/Dhaka');

                // Set to today's date
                $endTime->setDate($currentTime->year, $currentTime->month, $currentTime->day);

                // If end time is before current time, it means shift ends tomorrow
                if ($endTime->lt($currentTime)) {
                    $endTime->addDay();
                }

                $diff = $currentTime->diff($endTime);
                $shiftTimeRemaining = $diff->format('%H:%I');
            } catch (\Exception $e) {
                $shiftTimeRemaining = null;
            }
        }

        // Get production per shift for today
        $shiftProduction = [];
        $shiftHours = [
            1 => ['06', '07', '08', '09', '10', '11', '12', '13'], // Morning 6AM - 2PM
            2 => ['14', '15', '16', '17', '18', '19', '20', '21'], // Day 2PM - 10PM
            3 => ['22', '23', '00', '01', '02', '03', '04', '05'], // Night 10PM - 6AM
        ];

        // Get hourly production
        $hourlyProduction = ProductionLog::whereDate('production_date', $today)
            ->select('production_hour', DB::raw('SUM(garments_count) as total'))
            ->groupBy('production_hour')
            ->get()
            ->mapWithKeys(function ($item) {
                $hour = substr($item->production_hour, 0, 2);
                return [$hour => (int) $item->total];
            })
            ->toArray();

        // Calculate production per shift
        foreach ($shifts as $shift) {
            $hours = $shiftHours[$shift->id] ?? [];
            $total = 0;
            foreach ($hours as $hour) {
                $total += $hourlyProduction[$hour] ?? 0;
            }
            $shiftProduction[$shift->id] = $total;
        }

        // Get alerts per shift (tables with red light grouped by their assigned shift)
        $shiftAlerts = [];
        foreach ($shifts as $shift) {
            $shiftAlerts[$shift->id] = $tables->filter(function($table) use ($shift) {
                return $table->current_light_status === 'red'
                    && $table->currentAssignment
                    && $table->currentAssignment->shift_id == $shift->id;
            })->count();
        }

        // Count tables per shift
        $shiftTableCounts = [];
        foreach ($shifts as $shift) {
            $shiftTableCounts[$shift->id] = $tables->filter(function($table) use ($shift) {
                return $table->currentAssignment && $table->currentAssignment->shift_id == $shift->id;
            })->count();
        }

        // Get stats
        $stats = [
            'total_tables' => $tables->count(),
            'assigned_tables' => $tables->filter(function($t) {
                return $t->currentAssignment !== null;
            })->count(),
            'red_alerts' => $tables->where('current_light_status', 'red')->count(),
            'green_status' => $tables->where('current_light_status', 'green')->count(),
            'blue_status' => $tables->where('current_light_status', 'blue')->count(),
            'today_production' => array_sum($hourlyProduction),
        ];

        return view('dashboard.monitor', compact(
            'tables',
            'stats',
            'shifts',
            'currentShift',
            'shiftTimeRemaining',
            'shiftProduction',
            'shiftAlerts',
            'shiftTableCounts'
        ));
    }

}
