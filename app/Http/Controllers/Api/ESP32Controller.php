<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\LightIndicator;
use App\Models\ButtonNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ESP32Controller extends Controller
{
    /**
     * ESP32 polls this endpoint to get pending commands
     * GET /api/esp32/poll?table=T001&device_id=ESP32_001
     */
    public function poll(Request $request)
    {
        $tableNumber = $request->query('table');
        $deviceId = $request->query('device_id');

        if (!$tableNumber) {
            return response()->json([
                'success' => false,
                'error' => 'Table number required'
            ], 400);
        }

        // Find table
        $table = Table::where('table_number', $tableNumber)->first();

        if (!$table) {
            return response()->json([
                'success' => false,
                'error' => 'Table not found'
            ], 404);
        }

        // Update device info using DB query builder
        DB::table('tables')
            ->where('id', $table->id)
            ->update([
                'esp32_device_id' => $deviceId,
                'esp32_last_seen' => now(),
            ]);

        // Check if there's a pending command in cache
        $cacheKey = "esp32_command_{$table->id}";
        $pendingCommand = Cache::get($cacheKey);

        // Clear the command after reading (one-time delivery)
        if ($pendingCommand) {
            Cache::forget($cacheKey);
        }

        return response()->json([
            'success' => true,
            'table_id' => $table->id,
            'table_number' => $table->table_number,
            'current_color' => $table->current_light_status ?? 'off',
            'command' => $pendingCommand,
            'timestamp' => now()->timestamp,
        ]);
    }

    /**
     * ESP32 reports its status
     * POST /api/esp32/status
     */
    public function status(Request $request)
    {
        $validated = $request->validate([
            'table_number' => 'required|string',
            'device_id' => 'required|string',
            'current_color' => 'nullable|string',
            'ip_address' => 'nullable|string',
            'rssi' => 'nullable|integer',
        ]);

        $table = Table::where('table_number', $validated['table_number'])->first();

        if ($table) {
            // Update ESP32 info using DB query builder
            DB::table('tables')
                ->where('id', $table->id)
                ->update([
                    'esp32_ip' => $validated['ip_address'] ?? null,
                    'esp32_last_seen' => now(),
                ]);

            // Store device status in cache
            Cache::put("esp32_status_{$table->id}", [
                'device_id' => $validated['device_id'],
                'ip_address' => $validated['ip_address'] ?? null,
                'rssi' => $validated['rssi'] ?? null,
                'current_color' => $validated['current_color'] ?? 'off',
                'last_seen' => now()->toDateTimeString(),
                'online' => true,
            ], 60);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated',
            'server_time' => now()->toDateTimeString(),
        ]);
    }

    /**
     * ESP32 sends alert (button press)
     * POST /api/esp32/alert
     */
    public function alert(Request $request)
    {
        // Log incoming request
        Log::info('ESP32 Alert Received', [
            'data' => $request->all(),
            'ip' => $request->ip(),
        ]);

        $validated = $request->validate([
            'table_number' => 'required|string',
            'device_id' => 'required|string',
            'alert_type' => 'required|string',
            'previous_color' => 'nullable|string',
        ]);

        $table = Table::where('table_number', $validated['table_number'])->first();

        if (!$table) {
            Log::warning('ESP32 Alert: Table not found', ['table_number' => $validated['table_number']]);
            return response()->json([
                'success' => false,
                'error' => 'Table not found'
            ], 404);
        }

        // Update table info
        DB::table('tables')
            ->where('id', $table->id)
            ->update([
                'esp32_device_id' => $validated['device_id'],
                'esp32_last_seen' => now(),
                'current_light_status' => 'yellow',
            ]);

        // Create light indicator record
        try {
            LightIndicator::create([
                'table_id' => $table->id,
                'worker_id' => $table->currentAssignment?->worker_id,
                'supervisor_id' => null,
                'light_color' => 'yellow',
                'reason' => 'ESP32 Button Alert: ' . $validated['alert_type'],
                'activated_at' => now(),
                'is_active' => true,
            ]);
            Log::info('ESP32 Alert: LightIndicator created');
        } catch (\Exception $e) {
            Log::error('ESP32 Alert: LightIndicator failed', ['error' => $e->getMessage()]);
        }

        // Create button notification
        try {
            $notification = ButtonNotification::create([
                'table_id' => $table->id,
                'device_id' => $validated['device_id'],
                'table_number' => $validated['table_number'],
                'alert_type' => $validated['alert_type'],
                'previous_color' => $validated['previous_color'] ?? 'unknown',
                'worker_id' => $table->currentAssignment?->worker_id,
                'is_read' => false,
                'pressed_at' => now(),
            ]);

            Log::info('ESP32 Alert: ButtonNotification created', ['id' => $notification->id]);

        } catch (\Exception $e) {
            Log::error('ESP32 Alert: ButtonNotification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Alert received',
            'table_id' => $table->id,
        ]);
    }

    /**
     * Queue a command for ESP32
     */
    public static function queueCommand($tableId, $color, $blink = false)
    {
        $cacheKey = "esp32_command_{$tableId}";

        Cache::put($cacheKey, [
            'color' => $color,
            'blink' => $blink,
            'timestamp' => now()->timestamp,
        ], 300);

        // Update table's light status
        DB::table('tables')
            ->where('id', $tableId)
            ->update(['current_light_status' => $color]);

        return true;
    }

    /**
     * Check if ESP32 is online
     */
    public static function isOnline($tableId)
    {
        $result = DB::selectOne("
            SELECT TIMESTAMPDIFF(SECOND, esp32_last_seen, NOW()) as seconds_ago
            FROM `tables` WHERE id = ?
        ", [$tableId]);

        return $result && $result->seconds_ago < 30;
    }

    /**
     * Get ESP32 status
     */
    public static function getStatus($tableId)
    {
        return Cache::get("esp32_status_{$tableId}");
    }
}
