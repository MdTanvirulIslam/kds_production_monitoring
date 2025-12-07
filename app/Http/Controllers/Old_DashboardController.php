<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Worker;
use App\Models\ProductionLog;
use App\Models\LightIndicator;
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

        return view('dashboard.index', compact(
            'stats',
            'alertTables',
            'topWorkers',
            'recentLogs',
            'hourlyProduction'
        ));
    }

    /**
     * Display live monitor
     */
    public function monitor()
    {
        $tables = Table::where('is_active', true)
            ->with('currentAssignment.worker')
            ->orderBy('table_number')
            ->get();

        // Group tables by rows (8 per row for better display)
        $tableRows = $tables->chunk(8);

        // Get stats
        $stats = [
            'total_tables' => $tables->count(),
            'assigned_tables' => $tables->filter(function($t) {
                return $t->currentAssignment !== null;
            })->count(),
            'red_alerts' => $tables->where('current_light_status', 'red')->count(),
            'green_status' => $tables->where('current_light_status', 'green')->count(),
            'blue_status' => $tables->where('current_light_status', 'blue')->count(),
        ];

        return view('dashboard.monitor', compact('tables', 'tableRows', 'stats'));
    }
}
