<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'is_active',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'is_active' => 'boolean',
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
     * Get the supervisor (user)
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Scope for active indicators
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by color
     */
    public function scopeColor($query, $color)
    {
        return $query->where('light_color', $color);
    }

    /**
     * Deactivate this indicator
     */
    public function deactivate()
    {
        $this->update([
            'is_active' => false,
            'deactivated_at' => now(),
        ]);
    }
}
