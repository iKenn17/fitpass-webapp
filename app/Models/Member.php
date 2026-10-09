<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    public const TYPES = ['Regular', 'Student', 'Senior'];

    public const PLANS = ['Monthly Unlimited', 'Quarterly'];

    /** Days before expiry that count as "Expiring soon". */
    public const EXPIRING_WINDOW_DAYS = 7;

    protected $fillable = [
        'member_code',
        'name',
        'email',
        'phone',
        'type',
        'plan',
        'joined_at',
        'expires_at',
    ];

    protected $casts = [
        'joined_at' => 'date',
        'expires_at' => 'date',
    ];

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Active | Expiring soon | Expired */
    public function getStatusAttribute(): string
    {
        $today = today();

        if ($this->expires_at->lt($today)) {
            return 'Expired';
        }

        if ($this->expires_at->lte($today->copy()->addDays(self::EXPIRING_WINDOW_DAYS))) {
            return 'Expiring soon';
        }

        return 'Active';
    }

    public function getInitialsAttribute(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('member_code', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function scopeActive($query)
    {
        return $query->whereDate('expires_at', '>=', today()->toDateString());
    }

    public function scopeExpired($query)
    {
        return $query->whereDate('expires_at', '<', today()->toDateString());
    }

    public function scopeExpiringSoon($query)
    {
        return $query->whereBetween('expires_at', [
            today()->toDateString(),
            today()->addDays(self::EXPIRING_WINDOW_DAYS)->toDateString(),
        ]);
    }

    public static function nextCode(): string
    {
        return 'FTS-'.str_pad((string) (((int) static::max('id')) + 1), 6, '0', STR_PAD_LEFT);
    }
}
