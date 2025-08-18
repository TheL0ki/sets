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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'team_a_score' => 'integer',
        'team_b_score' => 'integer'
    ];

    /**
     * Score validation constants.
     */
    public const MIN_SCORE = 0;
    public const MAX_SCORE = 20;

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
     * Scope to get matches by session.
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Get the winning team.
     */
    public function getWinningTeam(): ?string
    {
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
        return "{$this->team_a_score}:{$this->team_b_score}";
    }

    /**
     * Validate if a score is within the acceptable range.
     */
    public static function isValidScore(int $score): bool
    {
        return $score >= self::MIN_SCORE && $score <= self::MAX_SCORE;
    }

    /**
     * Validate if both scores are valid.
     */
    public function hasValidScores(): bool
    {
        return self::isValidScore($this->team_a_score) && self::isValidScore($this->team_b_score);
    }

    /**
     * Get the next match number for a session.
     */
    public static function getNextMatchNumber(int $sessionId): int
    {
        $maxMatchNumber = self::where('session_id', $sessionId)->max('match_number');
        return ($maxMatchNumber ?? 0) + 1;
    }

    /**
     * Check if a match number already exists in a session.
     */
    public static function matchNumberExists(int $sessionId, int $matchNumber): bool
    {
        return self::where('session_id', $sessionId)
                   ->where('match_number', $matchNumber)
                   ->exists();
    }
} 