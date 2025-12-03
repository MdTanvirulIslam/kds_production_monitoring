<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionTarget extends Model
{
    protected $fillable = [
        'target_date',
        'hourly_target',
        'daily_target',
        'product_type',
        'notes',
    ];

    protected $casts = [
        'target_date' => 'date',
        'hourly_target' => 'integer',
        'daily_target' => 'integer',
    ];

    // Get target for specific date
    public static function getForDate($date)
    {
        return self::whereDate('target_date', $date)->first();
    }

    // Get today's target
    public static function getToday()
    {
        return self::whereDate('target_date', today())->first();
    }
}
