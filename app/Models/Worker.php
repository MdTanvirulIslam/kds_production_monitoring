<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'name',
        'phone',
        'email',
        'photo',
        'skill_level',
        'department',
        'joining_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'is_active' => 'boolean',
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
     * Scope for active workers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for workers with production in date range
     */
    public function scopeWithProductionBetween($query, $startDate, $endDate)
    {
        return $query->whereHas('productionLogs', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('production_date', [$startDate, $endDate]);
        });
    }
}
