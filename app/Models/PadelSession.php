<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PadelSession extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'padel_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'start_time',
        'end_time',
        'location',
        'status',
        'notes',
        'created_by',
        'max_players',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'max_players' => 'integer',
    ];

    /**
     * Session status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    /**
     * Get the matches in this session.
     */
    public function matches(): HasMany
    {
        return $this->hasMany(PadelMatch::class, 'session_id');
    }

    /**
     * Get the session participants.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(SessionParticipant::class, 'session_id');
    }

    /**
     * Get the session invitations.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(SessionInvitation::class, 'session_id');
    }

    /**
     * Get the user who created this session.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get only confirmed sessions.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope to get only pending sessions.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get sessions for a specific date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->where('start_time', '>=', $startDate)
                    ->where('start_time', '<=', $endDate);
    }

    /**
     * Scope to get upcoming sessions.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
                    ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Scope to get past sessions.
     */
    public function scopePast($query)
    {
        return $query->where('start_time', '<', now());
    }

    /**
     * Check if the session is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if the session is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Get the number of participants in this session.
     */
    public function getParticipantCount(): int
    {
        return $this->participants()->count();
    }

    /**
     * Check if the session has exactly 4 players (required for doubles).
     */
    public function hasExactPlayerCount(): bool
    {
        return $this->getParticipantCount() === 4;
    }

    /**
     * Check if the session has enough players for at least one match.
     */
    public function hasEnoughPlayers(): bool
    {
        return $this->getParticipantCount() >= 4;
    }

    /**
     * Check if the session is full (4 players maximum).
     */
    public function isFull(): bool
    {
        return $this->getParticipantCount() >= 4;
    }

    /**
     * Check if all invitations have been accepted.
     */
    public function allInvitationsAccepted(): bool
    {
        $pendingInvitations = $this->invitations()
            ->where('status', SessionInvitation::STATUS_PENDING)
            ->count();
        
        return $pendingInvitations === 0;
    }

    /**
     * Confirm the session when all invitations are accepted.
     */
    public function confirmSession(): void
    {
        if ($this->allInvitationsAccepted() && $this->hasExactPlayerCount()) {
            $this->update(['status' => self::STATUS_CONFIRMED]);
        }
    }
} 