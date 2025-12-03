<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Worker extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'worker_id',
        'name',
        'phone',
        'email',
        'date_of_birth',
        'joining_date',
        'skill_level',
        'photo',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'date_of_birth' => 'date',
        'joining_date' => 'date',
    ];

    // Get current active assignment for today
    public function currentAssignment(): HasOne
    {
        return $this->hasOne(TableAssignment::class)
            ->where('status', 'active')
            ->whereDate('assigned_date', today());
    }

    // All assignments
    public function assignments(): HasMany
    {
        return $this->hasMany(TableAssignment::class);
    }

    // All production logs
    public function productionLogs(): HasMany
    {
        return $this->hasMany(ProductionLog::class);
    }

    // All light indicators
    public function lightIndicators(): HasMany
    {
        return $this->hasMany(LightIndicator::class);
    }

    // Get today's production count
    public function getTodayProduction(): int
    {
        return $this->productionLogs()
            ->whereDate('production_date', today())
            ->sum('garments_count');
    }

    // Get current table
    public function getCurrentTable()
    {
        return $this->currentAssignment?->table;
    }

    // Scope for active workers
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Get photo URL
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return asset('assets/images/default-worker.png');
    }
}
