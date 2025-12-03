<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LightIndicator extends Model
{
    protected $fillable = [
        'table_id',
        'worker_id',
        'supervisor_id',
        'light_color',
        'reason',
        'notes',
        'activated_at',
        'deactivated_at',
        'duration_seconds',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    // Relationship to table
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    // Relationship to worker
    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    // Relationship to supervisor
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    // Deactivate this light
    public function deactivate(): void
    {
        $this->deactivated_at = now();
        $this->duration_seconds = $this->activated_at->diffInSeconds($this->deactivated_at);
        $this->save();
    }

    // Scope for active lights
    public function scopeActive($query)
    {
        return $query->whereNull('deactivated_at');
    }

    // Scope for specific color
    public function scopeColor($query, $color)
    {
        return $query->where('light_color', $color);
    }
}
