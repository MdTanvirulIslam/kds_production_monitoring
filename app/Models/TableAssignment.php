<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TableAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'worker_id',
        'assigned_date',
        'assigned_by',
        'shift_id',
        'shift_start',
        'shift_end',
        'status',
        'notes',
    ];

    protected $casts = [
        'assigned_date' => 'date',
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
     * Get the shift (if using shifts table)
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the user who assigned
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get formatted shift time
     */
    public function getShiftTimeAttribute()
    {
        if ($this->shift_start && $this->shift_end) {
            return Carbon::parse($this->shift_start)->format('h:i A') . ' - ' .
                Carbon::parse($this->shift_end)->format('h:i A');
        }
        return null;
    }

    /**
     * Scope for active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for today's assignments
     */
    public function scopeToday($query)
    {
        return $query->whereDate('assigned_date', Carbon::today('Asia/Dhaka'));
    }

    /**
     * Scope for a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('assigned_date', $date);
    }

    /**
     * Scope for a specific shift
     */
    public function scopeForShift($query, $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }
}
