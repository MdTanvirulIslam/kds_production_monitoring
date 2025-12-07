<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductionTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'target_date',
        'shift_id',
        'hourly_target',
        'daily_target',
        'product_type',
        'notes',
    ];

    protected $casts = [
        'target_date' => 'date',
        'shift_id' => 'integer',
        'hourly_target' => 'integer',
        'daily_target' => 'integer',
    ];

    /**
     * Get the shift
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get today's target (all shifts combined or general)
     */
    public static function getToday()
    {
        return static::whereDate('target_date', Carbon::today('Asia/Dhaka'))
            ->whereNull('shift_id')
            ->first();
    }

    /**
     * Get today's target for a specific shift
     */
    public static function getTodayForShift($shiftId)
    {
        return static::whereDate('target_date', Carbon::today('Asia/Dhaka'))
            ->where('shift_id', $shiftId)
            ->first();
    }

    /**
     * Get all targets for today (all shifts)
     */
    public static function getTodayAll()
    {
        return static::whereDate('target_date', Carbon::today('Asia/Dhaka'))
            ->with('shift')
            ->get();
    }

    /**
     * Get target for a specific date
     */
    public static function getForDate($date, $shiftId = null)
    {
        $query = static::whereDate('target_date', $date);
        
        if ($shiftId) {
            $query->where('shift_id', $shiftId);
        } else {
            $query->whereNull('shift_id');
        }
        
        return $query->first();
    }

    /**
     * Get total daily target for a date (sum of all shifts)
     */
    public static function getTotalDailyTarget($date = null)
    {
        $date = $date ?? Carbon::today('Asia/Dhaka');
        
        return static::whereDate('target_date', $date)->sum('daily_target') ?: 0;
    }

    /**
     * Scope for a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('target_date', $date);
    }

    /**
     * Scope for a specific shift
     */
    public function scopeForShift($query, $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }
}
