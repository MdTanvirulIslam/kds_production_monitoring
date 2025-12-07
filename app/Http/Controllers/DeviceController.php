<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\ButtonNotification;
use App\Http\Controllers\Api\ESP32Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeviceController extends Controller
{
    /**
     * Check if device is online using direct DB query
     */
    private function getOnlineStatus($tableId)
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
     * Get last seen text
     */
    private function getLastSeenText($tableId)
    {
        $result = DB::selectOne("
            SELECT TIMESTAMPDIFF(SECOND, esp32_last_seen, NOW()) as seconds_ago
            FROM `tables`
            WHERE id = ?
        ", [$tableId]);

        if (!$result || $result->seconds_ago === null) {
            return 'Never';
        }

        $seconds = $result->seconds_ago;

        if ($seconds < 60) {
            return $seconds . 's ago';
        } elseif ($seconds < 3600) {
            return floor($seconds / 60) . 'm ago';
        } elseif ($seconds < 86400) {
            return floor($seconds / 3600) . 'h ago';
        } else {
            return floor($seconds / 86400) . 'd ago';
        }
    }

    /**
     * Extract shift name from various data formats
     */
    private function getShiftName($shift)
    {
        if (!$shift) {
            return null;
        }

        // If it's a string (JSON), decode it
        if (is_string($shift)) {
            $decoded = json_decode($shift, true);
            if ($decoded && isset($decoded['name'])) {
                return $decoded['name'];
            }
            return null;
        }

        // If it's an object
        if (is_object($shift)) {
            return $shift->name ?? null;
        }

        // If it's an array
        if (is_array($shift)) {
            return $shift['name'] ?? null;
        }

        return null;
    }

    /**
     * Display device management page
     */
    public function index()
    {
        $tables = Table::whereNotNull('esp32_device_id')
            ->with(['currentAssignment.worker', 'currentAssignment'])
            ->orderBy('table_number')
            ->get();

        $devices = $tables->map(function ($table) {
            return [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'device_id' => $table->esp32_device_id,
                'ip_address' => $table->esp32_ip,
                'rssi' => $table->esp32_rssi ?? null,
                'current_color' => $table->current_light_status ?? 'off',
                'online' => $this->getOnlineStatus($table->id),
                'last_seen' => $table->esp32_last_seen,
                'last_seen_text' => $this->getLastSeenText($table->id),
                'worker' => $table->currentAssignment?->worker?->name ?? 'Unassigned',
                'shift' => $this->getShiftName($table->currentAssignment?->shift),
            ];
        });

        $notifications = collect();
        $unreadCount = 0;
        try {
            $notifications = ButtonNotification::with(['table.currentAssignment', 'worker'])
                ->orderBy('pressed_at', 'desc')
                ->limit(50)
                ->get();
            $unreadCount = ButtonNotification::where('is_read', false)->count();
        } catch (\Exception $e) {}

        $stats = [
            'total_devices' => $devices->count(),
            'online_devices' => $devices->where('online', true)->count(),
            'offline_devices' => $devices->where('online', false)->count(),
            'unread_alerts' => $unreadCount,
        ];

        return view('devices.index', compact('devices', 'notifications', 'stats'));
    }

    /**
     * Get devices status (AJAX) - for silent refresh
     */
    public function getDevicesStatus()
    {
        $tables = Table::whereNotNull('esp32_device_id')
            ->with(['currentAssignment.worker', 'currentAssignment'])
            ->orderBy('table_number')
            ->get();

        $devices = $tables->map(function ($table) {
            $result = DB::selectOne("
                SELECT TIMESTAMPDIFF(SECOND, esp32_last_seen, NOW()) as seconds_ago
                FROM `tables` WHERE id = ?
            ", [$table->id]);

            $secondsAgo = $result->seconds_ago ?? 9999;
            $isOnline = $secondsAgo < 30;

            // Format last seen text
            if ($secondsAgo < 60) {
                $lastSeenText = $secondsAgo . 's ago';
            } elseif ($secondsAgo < 3600) {
                $lastSeenText = floor($secondsAgo / 60) . 'm ago';
            } elseif ($secondsAgo < 86400) {
                $lastSeenText = floor($secondsAgo / 3600) . 'h ago';
            } else {
                $lastSeenText = floor($secondsAgo / 86400) . 'd ago';
            }

            return [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'device_id' => $table->esp32_device_id,
                'current_color' => $table->current_light_status ?? 'off',
                'online' => $isOnline,
                'seconds_ago' => $secondsAgo,
                'last_seen' => $lastSeenText,
                'worker' => $table->currentAssignment?->worker?->name ?? 'Unassigned',
                'shift' => $this->getShiftName($table->currentAssignment?->shift),
            ];
        });

        return response()->json([
            'success' => true,
            'devices' => $devices,
            'stats' => [
                'total' => $devices->count(),
                'online' => $devices->where('online', true)->count(),
                'offline' => $devices->where('online', false)->count(),
            ],
        ]);
    }

    /**
     * Get notifications (AJAX) - for silent refresh
     */
    public function getNotifications(Request $request)
    {
        try {
            // If only count needed
            if ($request->has('count_only')) {
                return response()->json([
                    'success' => true,
                    'unread_count' => ButtonNotification::where('is_read', false)->count(),
                ]);
            }

            $notifications = ButtonNotification::with(['table.currentAssignment', 'worker'])
                ->orderBy('pressed_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($notif) {
                    return [
                        'id' => $notif->id,
                        'table_id' => $notif->table_id,
                        'device_id' => $notif->device_id,
                        'table_number' => $notif->table_number,
                        'alert_type' => $notif->alert_type,
                        'previous_color' => $notif->previous_color,
                        'is_read' => $notif->is_read,
                        'pressed_at' => $notif->pressed_at?->toIso8601String(),
                        'pressed_at_formatted' => $notif->pressed_at?->format('h:i A'),
                        'pressed_at_date' => $notif->pressed_at?->format('M d, Y'),
                        'worker' => $notif->worker ? [
                            'id' => $notif->worker->id,
                            'name' => $notif->worker->name,
                        ] : null,
                        'shift' => $this->getShiftName($notif->table?->currentAssignment?->shift),
                    ];
                });

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => ButtonNotification::where('is_read', false)->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'notifications' => [],
                'unread_count' => 0,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            ButtonNotification::where('id', $id)->update([
                'is_read' => true,
                'read_at' => now(),
                'read_by' => auth()->id(),
            ]);
        } catch (\Exception $e) {}

        return response()->json(['success' => true]);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        try {
            ButtonNotification::where('is_read', false)->update([
                'is_read' => true,
                'read_at' => now(),
                'read_by' => auth()->id(),
            ]);
        } catch (\Exception $e) {}

        return response()->json(['success' => true]);
    }

    /**
     * Delete notification
     */
    public function deleteNotification($id)
    {
        try {
            ButtonNotification::where('id', $id)->delete();
        } catch (\Exception $e) {}

        return response()->json(['success' => true]);
    }

    /**
     * Clear all notifications
     */
    public function clearAllNotifications()
    {
        try {
            ButtonNotification::truncate();
        } catch (\Exception $e) {}

        return response()->json(['success' => true]);
    }

    /**
     * Send command to device
     */
    public function sendCommand(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'color' => 'required|in:red,green,blue,yellow,white,off',
        ]);

        ESP32Controller::queueCommand($validated['table_id'], $validated['color']);

        return response()->json([
            'success' => true,
            'message' => 'Command sent',
        ]);
    }
}
