<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Table;

class TableController extends Controller
{
    /**
     * Get all tables with current status
     */
    public function getStatus()
    {
        $tables = Table::with(['currentAssignment.worker'])
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get()
            ->map(function ($table) {
                return [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'table_name' => $table->table_name,
                    'light_status' => $table->current_light_status,
                    'worker' => $table->currentAssignment?->worker ? [
                        'id' => $table->currentAssignment->worker->id,
                        'name' => $table->currentAssignment->worker->name,
                    ] : null,
                    'today_production' => $table->today_production,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $tables,
        ]);
    }

    /**
     * Get single table info
     */
    public function getInfo(Table $table)
    {
        $table->load(['currentAssignment.worker']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'table_name' => $table->table_name,
                'light_status' => $table->current_light_status,
                'esp32_ip' => $table->esp32_ip,
                'worker' => $table->currentAssignment?->worker,
                'today_production' => $table->today_production,
            ],
        ]);
    }
}
