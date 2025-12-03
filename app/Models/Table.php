<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Table extends Model
{
    protected $fillable = [
        'table_number',
        'table_name',
        'qr_code',
        'esp32_ip',
        'esp32_device_id',
        'current_light_status',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

    // Generate QR code for this table
    public function generateQRCode(): string
    {
        $qrString = "TABLE:{$this->table_number}:{$this->id}";
        $this->qr_code = $qrString;
        $this->save();
        return $qrString;
    }

    // Get QR code image
    public function getQRCodeImage($size = 300)
    {
        return QrCode::size($size)->generate($this->qr_code);
    }

    // Get current worker assigned to this table
    public function getCurrentWorker()
    {
        return $this->currentAssignment?->worker;
    }

    // Get today's production count
    public function getTodayProduction(): int
    {
        return $this->productionLogs()
            ->whereDate('production_date', today())
            ->sum('garments_count');
    }

    // Get active light indicator
    public function getActiveLight()
    {
        return $this->lightIndicators()
            ->whereNull('deactivated_at')
            ->latest()
            ->first();
    }

    // Scope for active tables
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
