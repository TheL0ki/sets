<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionParticipant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'session_id',
        'user_id',
        'status',
        'confirmed_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    /**
     * Participant status constants.
     */
    public const STATUS_INVITED = 'invited';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the session that this participant belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(PadelSession::class, 'session_id');
    }

    /**
     * Get the user that is participating in this session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the participant has confirmed their participation.
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if the participant has declined.
     */
    public function isDeclined(): bool
    {
        return $this->status === self::STATUS_DECLINED;
    }

    /**
     * Check if the participant is still invited (pending response).
     */
    public function isInvited(): bool
    {
        return $this->status === self::STATUS_INVITED;
    }

    /**
     * Scope to get only confirmed participants.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope to get only invited participants.
     */
    public function scopeInvited($query)
    {
        return $query->where('status', self::STATUS_INVITED);
    }

    /**
     * Scope to get only declined participants.
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', self::STATUS_DECLINED);
    }

    /**
     * Scope to get participants for a specific session.
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }
} 