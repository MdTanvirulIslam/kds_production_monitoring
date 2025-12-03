<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Table;
use App\Models\LightIndicator;
use Illuminate\Support\Facades\Http;

class LightControlController extends Controller
{
    /**
     * Set light color for a table
     */
    public function setLight(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'light_color' => 'required|in:red,green,blue,off',
            'reason' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
        ]);

        $table = Table::findOrFail($validated['table_id']);
        $worker = $table->getCurrentWorker();

        // Deactivate previous active lights
        LightIndicator::where('table_id', $table->id)
            ->whereNull('deactivated_at')
            ->each(function ($light) {
                $light->deactivate();
            });

        // Create new light indicator (unless turning off)
        if ($validated['light_color'] !== 'off') {
            LightIndicator::create([
                'table_id' => $table->id,
                'worker_id' => $worker?->id,
                'supervisor_id' => auth()->id(),
                'light_color' => $validated['light_color'],
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'activated_at' => now(),
            ]);
        }

        // Update table status
        $table->current_light_status = $validated['light_color'];
        $table->save();

        // Send to ESP32
        $esp32Response = $this->sendToESP32($table, $validated['light_color']);

        return response()->json([
            'success' => true,
            'message' => 'Light set successfully',
            'data' => [
                'table_id' => $table->id,
                'light_color' => $validated['light_color'],
                'esp32_response' => $esp32Response,
            ],
        ]);
    }

    /**
     * Get light status for a table
     */
    public function getStatus(Table $table)
    {
        $activeLight = LightIndicator::where('table_id', $table->id)
            ->whereNull('deactivated_at')
            ->with(['worker', 'supervisor'])
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'table_id' => $table->id,
                'current_status' => $table->current_light_status,
                'active_indicator' => $activeLight,
            ],
        ]);
    }

    /**
     * Get all active alerts (red lights)
     */
    public function getActiveAlerts()
    {
        $alerts = LightIndicator::with(['table', 'worker', 'supervisor'])
            ->where('light_color', 'red')
            ->whereNull('deactivated_at')
            ->orderByDesc('activated_at')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $alerts->count(),
            'data' => $alerts,
        ]);
    }

    /**
     * Send light command to ESP32
     */
    private function sendToESP32(Table $table, string $color)
    {
        if (!$table->esp32_ip) {
            return ['status' => 'skipped', 'reason' => 'No ESP32 IP configured'];
        }

        try {
            $response = Http::timeout(5)->post("http://{$table->esp32_ip}/led", [
                'color' => $color,
            ]);

            return [
                'status' => 'sent',
                'response' => $response->json(),
            ];
        } catch (\Exception $e) {
            \Log::warning("ESP32 communication failed for {$table->esp32_ip}: " . $e->getMessage());
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }
}
