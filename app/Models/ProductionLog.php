<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'worker_id',
        'supervisor_id',
        'garments_count',
        'product_type',
        'production_date',
        'production_hour',
        'shift_id',
        'notes',
    ];

    protected $casts = [
        'garments_count' => 'integer',
        'production_date' => 'date',
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
     * Get the supervisor who logged this
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get the shift
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Scope for today's logs
     */
    public function scopeToday($query)
    {
        return $query->whereDate('production_date', Carbon::today('Asia/Dhaka'));
    }

    /**
     * Scope for a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('production_date', $date);
    }

    /**
     * Scope for a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('production_date', [$startDate, $endDate]);
    }

    /**
     * Scope for a specific shift
     */
    public function scopeForShift($query, $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    /**
     * Scope for a specific worker
     */
    public function scopeForWorker($query, $workerId)
    {
        return $query->where('worker_id', $workerId);
    }

    /**
     * Scope for a specific table
     */
    public function scopeForTable($query, $tableId)
    {
        return $query->where('table_id', $tableId);
    }
}
