<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_LABELS = [
        'owner' => 'Owner',
        'admin' => 'Admin',
        'front_desk' => 'Front Desk',
        'trainer' => 'Trainer',
    ];

    public const SHIFT_LABELS = [
        'on_shift' => 'On Shift',
        'off_duty' => 'Off Duty',
        'on_leave' => 'On Leave',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'shift_start',
        'shift_status',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function scopeStaff($query)
    {
        return $query->where('role', '!=', 'owner');
    }

    public function getInitialsAttribute(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LABELS[$this->role] ?? ucfirst($this->role);
    }

    public function getShiftLabelAttribute(): string
    {
        return $this->shift_start
            ? Carbon::parse($this->shift_start)->format('g:i A')
            : '—';
    }

    public function getShiftStatusLabelAttribute(): string
    {
        if (! $this->is_active) {
            return 'Deactivated';
        }

        return self::SHIFT_LABELS[$this->shift_status] ?? ucfirst($this->shift_status);
    }

    public function getLastLoginLabelAttribute(): string
    {
        return $this->last_login_at
            ? ($this->last_login_at->isToday()
                ? 'Today '.$this->last_login_at->format('g:i A')
                : $this->last_login_at->format('M j, g:i A'))
            : 'Never';
    }
}
