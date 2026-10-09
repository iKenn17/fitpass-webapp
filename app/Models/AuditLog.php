<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Write one line to the audit trail. */
    public static function record(string $message, ?int $userId = null): self
    {
        return static::create([
            'user_id' => $userId,
            'message' => $message,
        ]);
    }

    /** "Today 5:00 AM" for today, "Oct 8, 5:00 PM" otherwise. */
    public function getWhenLabelAttribute(): string
    {
        return $this->created_at->isToday()
            ? 'Today '.$this->created_at->format('g:i A')
            : $this->created_at->format('M j, g:i A');
    }
}
