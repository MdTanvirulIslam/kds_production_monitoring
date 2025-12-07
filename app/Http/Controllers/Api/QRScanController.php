<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Table;

class QRScanController extends Controller
{
    /**
     * Process QR code scan
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'qr_code' => 'required|string',
        ]);

        // Parse QR code format: TABLE:T001:1
        $parts = explode(':', $validated['qr_code']);

        if (count($parts) < 2 || $parts[0] !== 'TABLE') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code format',
            ], 400);
        }

        $tableNumber = $parts[1];

        // Find table
        $table = Table::where('table_number', $tableNumber)
            ->orWhere('qr_code', $validated['qr_code'])
            ->with(['currentAssignment.worker'])
            ->first();

        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Table not found',
            ], 404);
        }

        $worker = $table->currentAssignment?->worker;

        return response()->json([
            'success' => true,
            'data' => [
                'table' => [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'table_name' => $table->table_name,
                    'current_light_status' => $table->current_light_status,
                ],
                'worker' => $worker ? [
                    'id' => $worker->id,
                    'worker_id' => $worker->worker_id,
                    'name' => $worker->name,
                    'skill_level' => $worker->skill_level,
                    'photo' => $worker->photo_url,
                    'today_production' => $worker->today_production,
                ] : null,
            ],
        ]);
    }
}
