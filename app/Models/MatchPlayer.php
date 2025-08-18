<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchPlayer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'match_id',
        'user_id',
        'team',
    ];

    /**
     * Team constants.
     */
    public const TEAM_A = 'A';
    public const TEAM_B = 'B';

    /**
     * Get the match that this player belongs to.
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(PadelMatch::class, 'match_id');
    }

    /**
     * Get the user that is playing in this match.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scope to get players by team.
     */
    public function scopeByTeam($query, $team)
    {
        return $query->where('team', $team);
    }
} 