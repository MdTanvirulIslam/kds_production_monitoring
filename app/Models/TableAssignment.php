<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableAssignment extends Model
{
    protected $fillable = [
        'table_id',
        'worker_id',
        'assigned_date',
        'shift_start',
        'shift_end',
        'status',
        'notes',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'shift_start' => 'datetime:H:i',
        'shift_end' => 'datetime:H:i',
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

    // Production logs for this assignment
    public function productionLogs(): HasMany
    {
        return $this->table->productionLogs()
            ->where('worker_id', $this->worker_id)
            ->whereDate('production_date', $this->assigned_date);
    }

    // Scope for active assignments
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for today's assignments
    public function scopeToday($query)
    {
        return $query->whereDate('assigned_date', today());
    }
}
