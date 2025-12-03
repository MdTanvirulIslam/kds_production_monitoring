<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'role',
        'is_active',
        'phone',
        'last_login_at',
        'profile_picture',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Check if user is admin
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Check if user is supervisor
    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    // Check if user is monitor
    public function isMonitor(): bool
    {
        return $this->role === 'monitor';
    }

    // Supervisor's production logs
    public function productionLogs()
    {
        return $this->hasMany(ProductionLog::class, 'supervisor_id');
    }

    // Supervisor's light indicator actions
    public function lightIndicators()
    {
        return $this->hasMany(LightIndicator::class, 'supervisor_id');
    }
}
