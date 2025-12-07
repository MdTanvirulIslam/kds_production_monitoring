<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ButtonNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'device_id',
        'table_number',
        'alert_type',
        'previous_color',
        'worker_id',
        'is_read',
        'read_by',
        'pressed_at',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'pressed_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    // Relationships
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function readByUser()
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('pressed_at', today());
    }

    public function scopeRecent($query, $minutes = 60)
    {
        return $query->where('pressed_at', '>=', now()->subMinutes($minutes));
    }

    // Accessors
    public function getTimeAgoAttribute()
    {
        return $this->pressed_at?->diffForHumans();
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_read
            ? '<span class="badge bg-secondary">Read</span>'
            : '<span class="badge bg-warning">Unread</span>';
    }
}
