<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductionLog;
use App\Models\Table;
use App\Models\Worker;

class ProductionLogController extends Controller
{
    /**
     * Store new production log
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'worker_id' => 'required|exists:workers,id',
            'garments_count' => 'required|integer|min:1',
            'product_type' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $log = ProductionLog::create([
            'table_id' => $validated['table_id'],
            'worker_id' => $validated['worker_id'],
            'supervisor_id' => auth()->id(),
            'production_date' => today(),
            'production_hour' => now()->format('H:00:00'),
            'garments_count' => $validated['garments_count'],
            'product_type' => $validated['product_type'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Production logged successfully',
            'data' => $log->load(['table', 'worker']),
        ], 201);
    }

    /**
     * Get production logs for a worker
     */
    public function getByWorker(Worker $worker, Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));

        $logs = $worker->productionLogs()
            ->with(['table', 'supervisor'])
            ->whereDate('production_date', $date)
            ->orderBy('production_hour')
            ->get();

        $total = $logs->sum('garments_count');

        return response()->json([
            'success' => true,
            'data' => [
                'worker' => [
                    'id' => $worker->id,
                    'name' => $worker->name,
                ],
                'date' => $date,
                'total_garments' => $total,
                'logs' => $logs,
            ],
        ]);
    }

    /**
     * Get today's production summary
     */
    public function getTodaySummary()
    {
        $summary = ProductionLog::today()
            ->selectRaw('production_hour, SUM(garments_count) as total, COUNT(DISTINCT worker_id) as workers')
            ->groupBy('production_hour')
            ->orderBy('production_hour')
            ->get();

        $totalGarments = ProductionLog::today()->sum('garments_count');
        $totalWorkers = ProductionLog::today()->distinct('worker_id')->count('worker_id');

        return response()->json([
            'success' => true,
            'data' => [
                'date' => today()->format('Y-m-d'),
                'total_garments' => $totalGarments,
                'active_workers' => $totalWorkers,
                'hourly_breakdown' => $summary,
            ],
        ]);
    }
}
