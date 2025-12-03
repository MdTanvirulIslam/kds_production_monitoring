<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'worker_id',
        'supervisor_id',
        'production_date',
        'production_hour',
        'garments_count',
        'product_type',
        'notes',
    ];

    protected $casts = [
        'production_date' => 'date',
        'garments_count' => 'integer',
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
     * Scope for today's logs
     */
    public function scopeToday($query)
    {
        return $query->whereDate('production_date', today());
    }

    /**
     * Scope for specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('production_date', $date);
    }
}
