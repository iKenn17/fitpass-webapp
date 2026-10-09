<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'time_in',
        'time_out',
        'method',
        'recorded_by',
    ];

    protected $casts = [
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getIsInsideAttribute(): bool
    {
        return $this->time_out === null;
    }

    public function getMethodLabelAttribute(): string
    {
        return $this->method === 'qr' ? 'QR Code' : 'Manual';
    }

    public function scopeToday($query)
    {
        return $query->whereBetween('time_in', [today()->startOfDay(), today()->endOfDay()]);
    }
}
