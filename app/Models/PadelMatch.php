<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PadelMatch extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'matches';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'session_id',
        'match_number',
        'team_a_score',
        'team_b_score',
        'status',
        'notes',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'team_a_score' => 'integer',
        'team_b_score' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Match status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    /**
     * Get the players in this match.
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'match_players')
                    ->withPivot('team')
                    ->withTimestamps();
    }

    /**
     * Get the match players relationship.
     */
    public function matchPlayers(): HasMany
    {
        return $this->hasMany(MatchPlayer::class, 'match_id');
    }

    /**
     * Get the session that this match belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(PadelSession::class, 'session_id');
    }

    /**
     * Scope to get only confirmed matches.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope to get only pending matches.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get completed matches.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to get matches by session.
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Check if the match is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the match is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_CONFIRMED && $this->started_at !== null;
    }

    /**
     * Get the winning team.
     */
    public function getWinningTeam(): ?string
    {
        if (!$this->isCompleted()) {
            return null;
        }

        if ($this->team_a_score > $this->team_b_score) {
            return MatchPlayer::TEAM_A;
        }

        if ($this->team_b_score > $this->team_a_score) {
            return MatchPlayer::TEAM_B;
        }

        return null; // Tie
    }

    /**
     * Get the match score as a string.
     */
    public function getScoreString(): string
    {
        return "{$this->team_a_score} - {$this->team_b_score}";
    }
} 