<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LightIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'worker_id',
        'supervisor_id',
        'light_color',
        'reason',
        'activated_at',
        'deactivated_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    /**
     * Get the table
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Get the worker
     */
    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    /**
     * Get the supervisor who set this
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Scope for active indicators (not deactivated)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deactivated_at');
    }

    /**
     * Scope for today's indicators
     */
    public function scopeToday($query)
    {
        return $query->whereDate('activated_at', Carbon::today('Asia/Dhaka'));
    }

    /**
     * Scope for a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('activated_at', $date);
    }

    /**
     * Scope for a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('activated_at', [$startDate, $endDate]);
    }

    /**
     * Scope for red alerts
     */
    public function scopeRedAlerts($query)
    {
        return $query->where('light_color', 'red');
    }

    /**
     * Scope for a specific color
     */
    public function scopeByColor($query, $color)
    {
        return $query->where('light_color', $color);
    }
}
