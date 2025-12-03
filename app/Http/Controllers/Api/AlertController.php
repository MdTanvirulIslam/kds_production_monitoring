<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LightIndicator;

class AlertController extends Controller
{
    /**
     * Get all active alerts
     */
    public function getActive()
    {
        $alerts = LightIndicator::with(['table', 'worker', 'supervisor'])
            ->whereNull('deactivated_at')
            ->orderByDesc('activated_at')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $alerts->count(),
            'red_alerts' => $alerts->where('light_color', 'red')->count(),
            'data' => $alerts,
        ]);
    }

    /**
     * Get pending alerts (for ESP32 polling)
     */
    public function getPending()
    {
        $alerts = LightIndicator::where('light_color', 'red')
            ->whereNull('deactivated_at')
            ->count();

        return response()->json([
            'count' => $alerts,
            'alerts' => LightIndicator::with('table')
                ->where('light_color', 'red')
                ->whereNull('deactivated_at')
                ->get(['id', 'table_id', 'activated_at']),
        ]);
    }

    /**
     * Acknowledge/deactivate an alert
     */
    public function acknowledge(LightIndicator $alert)
    {
        $alert->deactivate();

        // Update table status
        $alert->table->current_light_status = 'off';
        $alert->table->save();

        return response()->json([
            'success' => true,
            'message' => 'Alert acknowledged',
        ]);
    }
}
