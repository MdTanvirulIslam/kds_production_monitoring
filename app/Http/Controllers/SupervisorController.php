<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Worker;
use App\Models\ProductionLog;
use App\Models\LightIndicator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SupervisorController extends Controller
{
    /**
     * Show QR Scanner Page
     */
    public function scan()
    {
        return view('supervisor.scan');
    }

    /**
     * Process scanned QR Code
     */
    public function processScan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $qrCode = $request->qr_code;

        // Parse QR code format: TABLE:T001:1 or TABLE:T001:1:timestamp
        $parts = explode(':', $qrCode);

        if (count($parts) < 3 || $parts[0] !== 'TABLE') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR Code format. Expected TABLE:NUMBER:ID'
            ], 400);
        }

        $tableNumber = $parts[1];
        $tableId = $parts[2];

        // Find table by ID or table_number
        $table = Table::where('id', $tableId)
            ->orWhere('table_number', $tableNumber)
            ->first();

        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Table not found!'
            ], 404);
        }

        // Get current worker assignment
        $assignment = $table->currentAssignment;
        $worker = $assignment?->worker;

        // Get today's production
        $todayProduction = $table->getTodayProduction();

        return response()->json([
            'success' => true,
            'message' => 'Table found successfully!',
            'data' => [
                'table' => [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'table_name' => $table->table_name,
                    'current_light_status' => $table->current_light_status ?? 'off',
                    'esp32_ip' => $table->esp32_ip,
                ],
                'worker' => $worker ? [
                    'id' => $worker->id,
                    'worker_id' => $worker->worker_id,
                    'name' => $worker->name,
                    'skill_level' => $worker->skill_level ?? 'beginner',
                    'photo' => $worker->photo ? asset('storage/' . $worker->photo) : null,
                ] : null,
                'assignment' => $assignment ? [
                    'id' => $assignment->id,
                    'shift_start' => $assignment->shift_start ?? '08:00',
                    'shift_end' => $assignment->shift_end ?? '17:00',
                ] : null,
                'today_production' => $todayProduction,
            ]
        ]);
    }

    /**
     * Store production log
     */
    public function storeProduction(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'worker_id' => 'required|exists:workers,id',
            'garments_count' => 'required|integer|min:1',
            'product_type' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        // Use Bangladesh timezone for production hour
        $now = Carbon::now('Asia/Dhaka');

        $productionLog = ProductionLog::create([
            'table_id' => $validated['table_id'],
            'worker_id' => $validated['worker_id'],
            'supervisor_id' => auth()->id(),
            'production_date' => $now->toDateString(),
            'production_hour' => $now->format('H:00'),
            'garments_count' => $validated['garments_count'],
            'product_type' => $validated['product_type'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Production logged successfully!',
            'data' => [
                'id' => $productionLog->id,
                'garments_count' => $productionLog->garments_count,
                'production_hour' => $productionLog->production_hour,
                'logged_at' => $now->format('h:i A'), // Show user-friendly time
            ]
        ]);
    }

    /**
     * Set light indicator for a table
     */
    public function setLight(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'light_color' => 'required|in:red,green,blue,off',
            'reason' => 'nullable|string|max:255',
        ]);

        $table = Table::findOrFail($validated['table_id']);
        $worker = $table->currentAssignment?->worker;

        // Use Bangladesh timezone
        $now = Carbon::now('Asia/Dhaka');

        // Deactivate previous light indicators for this table
        LightIndicator::where('table_id', $table->id)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'deactivated_at' => $now,
            ]);

        // Create new light indicator if not 'off'
        if ($validated['light_color'] !== 'off') {
            LightIndicator::create([
                'table_id' => $table->id,
                'worker_id' => $worker?->id,
                'supervisor_id' => auth()->id(),
                'light_color' => $validated['light_color'],
                'reason' => $validated['reason'] ?? $this->getReasonForColor($validated['light_color']),
                'activated_at' => $now,
                'is_active' => true,
            ]);
        }

        // Update table's current light status
        $table->current_light_status = $validated['light_color'];
        $table->save();

        // Send command to ESP32 if IP is configured
        $esp32Response = null;
        if ($table->esp32_ip) {
            $esp32Response = $this->sendToESP32($table->esp32_ip, $validated['light_color']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Light indicator set to ' . strtoupper($validated['light_color']),
            'data' => [
                'table_id' => $table->id,
                'light_color' => $validated['light_color'],
                'esp32_response' => $esp32Response,
            ]
        ]);
    }

    /**
     * Get default reason for light color
     */
    private function getReasonForColor($color)
    {
        return match($color) {
            'red' => 'Quality Issue / Alert',
            'green' => 'Good Work',
            'blue' => 'Need Help',
            default => 'Reset'
        };
    }

    /**
     * Send light command to ESP32
     */
    private function sendToESP32($ip, $color)
    {
        try {
            $response = Http::timeout(3)->post("http://{$ip}/led", [
                'color' => $color,
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Show supervisor's activity for today
     */
    public function myActivity()
    {
        $today = Carbon::today('Asia/Dhaka');

        $productionLogs = ProductionLog::where('supervisor_id', auth()->id())
            ->whereDate('production_date', $today)
            ->with(['table', 'worker'])
            ->orderBy('created_at', 'desc')
            ->get();

        $lightIndicators = LightIndicator::where('supervisor_id', auth()->id())
            ->whereDate('activated_at', $today)
            ->with(['table', 'worker'])
            ->orderBy('activated_at', 'desc')
            ->get();

        $totalGarments = $productionLogs->sum('garments_count');

        return view('supervisor.my-activity', compact('productionLogs', 'lightIndicators', 'totalGarments'));
    }

    /**
     * Quick select table (without QR scanning)
     */
    public function quickSelect()
    {
        $tables = Table::where('is_active', true)
            ->with('currentAssignment.worker')
            ->orderBy('table_number')
            ->get();

        return view('supervisor.quick-select', compact('tables'));
    }
}
