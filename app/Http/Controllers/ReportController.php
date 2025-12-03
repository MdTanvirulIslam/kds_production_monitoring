<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Table;
use App\Models\Worker;
use App\Models\ProductionLog;
use App\Models\ProductionTarget;
use App\Models\LightIndicator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Reports Dashboard
     */
    public function index()
    {
        // Get overview statistics
        $todayStats = [
            'production' => ProductionLog::today()->sum('garments_count'),
            'target' => ProductionTarget::getToday()?->daily_target ?? 0,
            'workers' => ProductionLog::today()->distinct('worker_id')->count('worker_id'),
            'alerts' => LightIndicator::whereDate('activated_at', today())->count(),
        ];

        // Last 7 days production
        $weeklyProduction = ProductionLog::selectRaw('production_date, SUM(garments_count) as total')
            ->whereBetween('production_date', [now()->subDays(7), now()])
            ->groupBy('production_date')
            ->orderBy('production_date')
            ->get();

        // Top 5 workers this month
        $topWorkers = Worker::withSum(['productionLogs as monthly_production' => function ($query) {
            $query->whereMonth('production_date', now()->month);
        }], 'garments_count')
            ->having('monthly_production', '>', 0)
            ->orderByDesc('monthly_production')
            ->take(5)
            ->get();

        return view('reports.index', compact('todayStats', 'weeklyProduction', 'topWorkers'));
    }

    /**
     * Daily Report
     */
    public function daily(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));

        // Hourly production breakdown
        $hourlyProduction = ProductionLog::whereDate('production_date', $date)
            ->selectRaw('production_hour, SUM(garments_count) as total, COUNT(DISTINCT worker_id) as workers')
            ->groupBy('production_hour')
            ->orderBy('production_hour')
            ->get();

        // Table-wise production
        $tableProduction = Table::withSum(['productionLogs as daily_production' => function ($query) use ($date) {
            $query->whereDate('production_date', $date);
        }], 'garments_count')
            ->with(['currentAssignment.worker'])
            ->having('daily_production', '>', 0)
            ->orderByDesc('daily_production')
            ->get();

        // Worker-wise production
        $workerProduction = Worker::withSum(['productionLogs as daily_production' => function ($query) use ($date) {
            $query->whereDate('production_date', $date);
        }], 'garments_count')
            ->having('daily_production', '>', 0)
            ->orderByDesc('daily_production')
            ->get();

        // Get target
        $target = ProductionTarget::whereDate('target_date', $date)->first();

        // Light indicator alerts
        $alerts = LightIndicator::with(['table', 'worker', 'supervisor'])
            ->whereDate('activated_at', $date)
            ->orderByDesc('activated_at')
            ->get();

        // Total production
        $totalProduction = ProductionLog::whereDate('production_date', $date)->sum('garments_count');

        return view('reports.daily', compact(
            'date',
            'hourlyProduction',
            'tableProduction',
            'workerProduction',
            'target',
            'alerts',
            'totalProduction'
        ));
    }

    /**
     * Monthly Report
     */
    public function monthly(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        // Daily production for the month
        $dailyProduction = ProductionLog::selectRaw('production_date, SUM(garments_count) as total')
            ->whereBetween('production_date', [$startDate, $endDate])
            ->groupBy('production_date')
            ->orderBy('production_date')
            ->get();

        // Weekly totals
        $weeklyTotals = ProductionLog::selectRaw('WEEK(production_date) as week_number, SUM(garments_count) as total')
            ->whereBetween('production_date', [$startDate, $endDate])
            ->groupBy('week_number')
            ->get();

        // Top workers for the month
        $topWorkers = Worker::withSum(['productionLogs as monthly_production' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('production_date', [$startDate, $endDate]);
        }], 'garments_count')
            ->having('monthly_production', '>', 0)
            ->orderByDesc('monthly_production')
            ->take(10)
            ->get();

        // Table performance
        $tablePerformance = Table::withSum(['productionLogs as monthly_production' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('production_date', [$startDate, $endDate]);
        }], 'garments_count')
            ->having('monthly_production', '>', 0)
            ->orderByDesc('monthly_production')
            ->get();

        // Alert statistics
        $alertStats = LightIndicator::selectRaw('light_color, COUNT(*) as count')
            ->whereBetween('activated_at', [$startDate, $endDate])
            ->groupBy('light_color')
            ->get()
            ->pluck('count', 'light_color');

        // Summary
        $summary = [
            'total_production' => ProductionLog::whereBetween('production_date', [$startDate, $endDate])->sum('garments_count'),
            'working_days' => ProductionLog::whereBetween('production_date', [$startDate, $endDate])->distinct('production_date')->count('production_date'),
            'active_workers' => ProductionLog::whereBetween('production_date', [$startDate, $endDate])->distinct('worker_id')->count('worker_id'),
        ];
        $summary['avg_daily'] = $summary['working_days'] > 0 ? round($summary['total_production'] / $summary['working_days']) : 0;

        return view('reports.monthly', compact(
            'month',
            'dailyProduction',
            'weeklyTotals',
            'topWorkers',
            'tablePerformance',
            'alertStats',
            'summary'
        ));
    }

    /**
     * Worker-specific Report
     */
    public function worker(Request $request, Worker $worker)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Daily production
        $dailyProduction = $worker->productionLogs()
            ->selectRaw('production_date, SUM(garments_count) as total')
            ->whereBetween('production_date', [$startDate, $endDate])
            ->groupBy('production_date')
            ->orderBy('production_date')
            ->get();

        // Summary statistics
        $summary = [
            'total_production' => $worker->productionLogs()->whereBetween('production_date', [$startDate, $endDate])->sum('garments_count'),
            'working_days' => $worker->productionLogs()->whereBetween('production_date', [$startDate, $endDate])->distinct('production_date')->count('production_date'),
            'red_alerts' => $worker->lightIndicators()->where('light_color', 'red')->whereBetween('activated_at', [$startDate, $endDate])->count(),
            'green_lights' => $worker->lightIndicators()->where('light_color', 'green')->whereBetween('activated_at', [$startDate, $endDate])->count(),
        ];
        $summary['avg_daily'] = $summary['working_days'] > 0 ? round($summary['total_production'] / $summary['working_days']) : 0;

        // Recent logs
        $recentLogs = $worker->productionLogs()
            ->with(['table', 'supervisor'])
            ->whereBetween('production_date', [$startDate, $endDate])
            ->orderByDesc('production_date')
            ->orderByDesc('production_hour')
            ->paginate(20);

        return view('reports.worker', compact('worker', 'dailyProduction', 'summary', 'recentLogs', 'startDate', 'endDate'));
    }

    /**
     * Export Report to CSV
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'daily');
        $date = $request->input('date', today()->format('Y-m-d'));

        switch ($type) {
            case 'daily':
                return $this->exportDailyCSV($date);
            case 'monthly':
                return $this->exportMonthlyCSV($request->input('month', now()->format('Y-m')));
            default:
                return back()->with('error', 'Invalid export type');
        }
    }

    /**
     * Export Daily Report as CSV
     */
    private function exportDailyCSV($date)
    {
        $logs = ProductionLog::with(['table', 'worker', 'supervisor'])
            ->whereDate('production_date', $date)
            ->orderBy('production_hour')
            ->get();

        $filename = "daily_production_{$date}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs, $date) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, ['Daily Production Report - ' . $date]);
            fputcsv($file, []); // Empty row
            fputcsv($file, ['Hour', 'Table', 'Worker', 'Garments', 'Product Type', 'Supervisor', 'Notes']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->production_hour,
                    $log->table->table_number,
                    $log->worker->name,
                    $log->garments_count,
                    $log->product_type ?? '-',
                    $log->supervisor->name,
                    $log->notes ?? '-',
                ]);
            }

            // Total row
            fputcsv($file, []);
            fputcsv($file, ['TOTAL', '', '', $logs->sum('garments_count'), '', '', '']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Monthly Report as CSV
     */
    private function exportMonthlyCSV($month)
    {
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $dailyTotals = ProductionLog::selectRaw('production_date, SUM(garments_count) as total, COUNT(DISTINCT worker_id) as workers')
            ->whereBetween('production_date', [$startDate, $endDate])
            ->groupBy('production_date')
            ->orderBy('production_date')
            ->get();

        $filename = "monthly_production_{$month}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($dailyTotals, $month) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Monthly Production Report - ' . $month]);
            fputcsv($file, []);
            fputcsv($file, ['Date', 'Total Garments', 'Workers']);

            foreach ($dailyTotals as $day) {
                fputcsv($file, [
                    $day->production_date->format('Y-m-d'),
                    $day->total,
                    $day->workers,
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['TOTAL', $dailyTotals->sum('total'), '-']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
