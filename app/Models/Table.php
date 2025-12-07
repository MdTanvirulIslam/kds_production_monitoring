<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_number',
        'table_name',
        'qr_code',
        'location',
        'department',
        'current_light_status',
        'esp32_device_id',
        'esp32_ip',
        'esp32_last_seen',
        'hourly_target',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hourly_target' => 'integer',
        'esp32_last_seen' => 'datetime',
    ];

    /**
     * Get current assignment for today
     */
    public function currentAssignment()
    {
        return $this->hasOne(TableAssignment::class)
            ->whereDate('assigned_date', Carbon::today('Asia/Dhaka'))
            ->where('status', 'active');
    }

    /**
     * Get all assignments
     */
    public function assignments()
    {
        return $this->hasMany(TableAssignment::class);
    }

    /**
     * Get all production logs
     */
    public function productionLogs()
    {
        return $this->hasMany(ProductionLog::class);
    }

    /**
     * Get all light indicators
     */
    public function lightIndicators()
    {
        return $this->hasMany(LightIndicator::class);
    }

    /**
     * Get today's production count
     */
    public function getTodayProductionAttribute()
    {
        return $this->productionLogs()
            ->whereDate('production_date', Carbon::today('Asia/Dhaka'))
            ->sum('garments_count');
    }

    /**
     * Get this month's production count
     */
    public function getMonthlyProductionAttribute()
    {
        return $this->productionLogs()
            ->whereMonth('production_date', Carbon::now('Asia/Dhaka')->month)
            ->whereYear('production_date', Carbon::now('Asia/Dhaka')->year)
            ->sum('garments_count');
    }

    /**
     * Check if ESP32 is online (cached status)
     */
    public function getEsp32OnlineAttribute()
    {
        return Cache::has("esp32_status_{$this->id}");
    }

    /**
     * Get ESP32 status details
     */
    public function getEsp32StatusAttribute()
    {
        return Cache::get("esp32_status_{$this->id}");
    }

    /**
     * Scope for active tables
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for tables with red light (alerts)
     */
    public function scopeWithAlerts($query)
    {
        return $query->where('current_light_status', 'red');
    }

    /**
     * Scope for tables with production in date range
     */
    public function scopeWithProductionBetween($query, $startDate, $endDate)
    {
        return $query->whereHas('productionLogs', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('production_date', [$startDate, $endDate]);
        });
    }
}
