<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get assignments for this shift
     */
    public function assignments()
    {
        return $this->hasMany(TableAssignment::class);
    }

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute()
    {
        return Carbon::parse($this->start_time)->format('h:i A') . ' - ' .
            Carbon::parse($this->end_time)->format('h:i A');
    }

    /**
     * Get shift icon based on time
     */
    public function getIconAttribute()
    {
        $hour = Carbon::parse($this->start_time)->hour;

        if ($hour < 12) {
            return '🌅'; // Morning
        } elseif ($hour < 18) {
            return '☀️'; // Day
        } else {
            return '🌙'; // Night
        }
    }

    /**
     * Scope for active shifts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
