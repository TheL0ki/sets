<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'skill_level',
        'preferred_frequency_per_week',
        'phone',
        'is_active',
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
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user's availabilities.
     */
    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    /**
     * Get the sessions where this user is a participant.
     */
    public function sessionParticipants(): HasMany
    {
        return $this->hasMany(SessionParticipant::class);
    }

    /**
     * Get the sessions where this user is a participant.
     */
    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(PadelSession::class, 'session_participants', 'user_id', 'session_id')
                    ->withPivot('status', 'confirmed_at')
                    ->withTimestamps();
    }

    /**
     * Get the session invitations for this user.
     */
    public function sessionInvitations(): HasMany
    {
        return $this->hasMany(SessionInvitation::class, 'user_id');
    }

    /**
     * Get the session invitations sent by this user.
     */
    public function sentSessionInvitations(): HasMany
    {
        return $this->hasMany(SessionInvitation::class, 'invited_by');
    }

    /**
     * Get the matches where this user is a player.
     */
    public function matchPlayers(): HasMany
    {
        return $this->hasMany(MatchPlayer::class);
    }

    /**
     * Get the matches where this user is a player.
     */
    public function matches(): BelongsToMany
    {
        return $this->belongsToMany(PadelMatch::class, 'match_players', 'user_id', 'match_id')
                    ->withPivot('team')
                    ->withTimestamps();
    }

    /**
     * Check if user is available for a specific time slot.
     */
    public function isAvailableFor(\Carbon\Carbon $startTime, \Carbon\Carbon $endTime): bool
    {
        return $this->availabilities()
                    ->where('start_time', '<=', $startTime)
                    ->where('end_time', '>=', $endTime)
                    ->where('is_available', true)
                    ->exists();
    }

    /**
     * Get user's recent session count (last 30 days).
     */
    public function getRecentSessionCount(): int
    {
        return $this->sessions()
                    ->where('padel_sessions.start_time', '>=', now()->subDays(30))
                    ->count();
    }

    /**
     * Get user's recent match count (last 30 days).
     */
    public function getRecentMatchCount(): int
    {
        return $this->matches()
                    ->whereHas('session', function ($query) {
                        $query->where('start_time', '>=', now()->subDays(30));
                    })
                    ->count();
    }
}
