<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ESP32Controller;
use App\Models\Table;
use App\Models\Worker;
use App\Models\ProductionLog;
use App\Models\LightIndicator;
use App\Models\ButtonNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupervisorController extends Controller
{
    /**
     * Alert timeout in seconds (must match ESP32 ALERT_DURATION)
     */
    private const ALERT_TIMEOUT = 65; // 60 seconds + 5 buffer

    /**
     * Check if ESP32 device is online using direct DB query
     */
    private function isDeviceOnline($tableId)
    {
        $result = DB::selectOne("
            SELECT TIMESTAMPDIFF(SECOND, esp32_last_seen, NOW()) as seconds_ago
            FROM `tables`
            WHERE id = ?
        ", [$tableId]);

        if (!$result || $result->seconds_ago === null) {
            return false;
        }

        return $result->seconds_ago < 30;
    }

    /**
     * Check and auto-restore expired yellow alerts using direct SQL
     * Returns the actual current light status
     */
    private function getActualLightStatus($table)
    {
        $currentStatus = $table->current_light_status ?? 'off';

        // If not yellow, return as-is
        if ($currentStatus !== 'yellow') {
            return $currentStatus;
        }

        // Check if there's an expired yellow alert using direct SQL (avoid timezone issues)
        try {
            $result = DB::selectOne("
                SELECT
                    id,
                    previous_color,
                    TIMESTAMPDIFF(SECOND, pressed_at, NOW()) as seconds_ago
                FROM button_notifications
                WHERE table_id = ?
                AND alert_type = 'button_press'
                ORDER BY pressed_at DESC
                LIMIT 1
            ", [$table->id]);

            if ($result && $result->seconds_ago !== null) {
                // If alert has expired, restore previous color
                if ($result->seconds_ago > self::ALERT_TIMEOUT) {
                    $previousColor = $result->previous_color ?? 'off';

                    // Update database
                    DB::table('tables')
                        ->where('id', $table->id)
                        ->update(['current_light_status' => $previousColor]);

                    return $previousColor;
                }
            }
        } catch (\Exception $e) {
            // If button_notifications table doesn't exist, just return current status
            \Log::error('getActualLightStatus error: ' . $e->getMessage());
        }

        return $currentStatus;
    }

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
        $todayProduction = $table->today_production;

        // Check if ESP32 is online using direct DB query
        $esp32Online = $this->isDeviceOnline($table->id);
        $esp32Status = ESP32Controller::getStatus($table->id);

        // Get actual light status (auto-restore if expired)
        $actualLightStatus = $this->getActualLightStatus($table);

        return response()->json([
            'success' => true,
            'message' => 'Table found successfully!',
            'data' => [
                'table' => [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'table_name' => $table->table_name,
                    'current_light_status' => $actualLightStatus,
                    'esp32_ip' => $table->esp32_ip,
                    'esp32_online' => $esp32Online,
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
                'logged_at' => $now->format('h:i A'),
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
            'light_color' => 'required|in:red,green,blue,yellow,off',
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

        // Method 1: Queue command for ESP32 to poll (for cPanel hosting)
        ESP32Controller::queueCommand($table->id, $validated['light_color']);

        // Method 2: Try direct connection if on same network (optional fallback)
        $esp32Response = null;
        if ($table->esp32_ip && $this->isLocalNetwork()) {
            $esp32Response = $this->sendToESP32($table->esp32_ip, $validated['light_color']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Light indicator set to ' . strtoupper($validated['light_color']),
            'data' => [
                'table_id' => $table->id,
                'light_color' => $validated['light_color'],
                'command_queued' => true,
                'esp32_direct' => $esp32Response,
            ]
        ]);
    }

    /**
     * Check if running on local network (not cPanel)
     */
    private function isLocalNetwork()
    {
        $serverIp = $_SERVER['SERVER_ADDR'] ?? '';
        return str_starts_with($serverIp, '192.168.') ||
            str_starts_with($serverIp, '10.') ||
            str_starts_with($serverIp, '172.') ||
            $serverIp === '127.0.0.1';
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
            'yellow' => 'Warning / Attention',
            default => 'Reset'
        };
    }

    /**
     * Send light command to ESP32 (direct connection)
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

        // Add tables for quick select grid with online status
        $tables = Table::where('is_active', true)
            ->with('currentAssignment.worker')
            ->orderBy('table_number')
            ->get()
            ->map(function ($table) {
                $table->esp32_online = $this->isDeviceOnline($table->id);
                // Auto-restore expired yellow alerts
                $table->current_light_status = $this->getActualLightStatus($table);
                return $table;
            });

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

        return view('supervisor.my-activity', compact('tables', 'productionLogs', 'lightIndicators', 'totalGarments'));
    }

    /**
     * Quick select table (without QR scanning)
     */
    public function quickSelect()
    {
        $tables = Table::where('is_active', true)
            ->with('currentAssignment.worker')
            ->orderBy('table_number')
            ->get()
            ->map(function ($table) {
                // Add online status to each table
                $table->esp32_online = $this->isDeviceOnline($table->id);
                // Auto-restore expired yellow alerts
                $table->current_light_status = $this->getActualLightStatus($table);
                return $table;
            });

        return view('supervisor.quick-select', compact('tables'));
    }

    /**
     * API endpoint to get device status (for AJAX refresh)
     */
    public function getDeviceStatus(Request $request)
    {
        $tableId = $request->query('table_id');

        if ($tableId) {
            // Single table status
            $table = Table::find($tableId);
            if (!$table) {
                return response()->json(['success' => false, 'error' => 'Table not found']);
            }

            return response()->json([
                'success' => true,
                'table_id' => $table->id,
                'online' => $this->isDeviceOnline($table->id),
                'current_light_status' => $this->getActualLightStatus($table),
            ]);
        }

        // All tables status
        $tables = Table::where('is_active', true)->get();
        $statuses = [];

        foreach ($tables as $table) {
            $statuses[$table->id] = [
                'online' => $this->isDeviceOnline($table->id),
                'current_light_status' => $this->getActualLightStatus($table),
            ];
        }

        return response()->json([
            'success' => true,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Debug endpoint to check yellow alert status
     */
    public function debugYellowAlert(Request $request)
    {
        $tableId = $request->query('table_id');

        if (!$tableId) {
            return response()->json(['error' => 'table_id required']);
        }

        $table = Table::find($tableId);
        if (!$table) {
            return response()->json(['error' => 'Table not found']);
        }

        // Get latest notification
        $result = DB::selectOne("
            SELECT
                id,
                previous_color,
                pressed_at,
                TIMESTAMPDIFF(SECOND, pressed_at, NOW()) as seconds_ago,
                NOW() as server_now
            FROM button_notifications
            WHERE table_id = ?
            AND alert_type = 'button_press'
            ORDER BY pressed_at DESC
            LIMIT 1
        ", [$tableId]);

        return response()->json([
            'table_id' => $tableId,
            'current_light_status_in_db' => $table->current_light_status,
            'actual_light_status' => $this->getActualLightStatus($table),
            'alert_timeout_seconds' => self::ALERT_TIMEOUT,
            'latest_notification' => $result ? [
                'id' => $result->id,
                'previous_color' => $result->previous_color,
                'pressed_at' => $result->pressed_at,
                'seconds_ago' => $result->seconds_ago,
                'server_now' => $result->server_now,
                'expired' => $result->seconds_ago > self::ALERT_TIMEOUT,
            ] : null,
        ]);
    }
}
