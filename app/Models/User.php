<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\PlayerIgnore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements MustVerifyEmail
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
        'preferred_frequency_per_week',
        'preferred_frequency_per_month',
        'min_session_length_hours',
        'max_session_length_hours',
        'phone',
        'phone_visible',
        'email_visible',
        'is_active',
        'email_notifications_enabled',
        'session_invitation_notifications',
        'session_confirmation_notifications',
        'session_reminder_notifications',
        'session_cancellation_notifications',
        'onboarding_completed',
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
            'email_notifications_enabled' => 'boolean',
            'session_invitation_notifications' => 'boolean',
            'session_confirmation_notifications' => 'boolean',
            'session_reminder_notifications' => 'boolean',
            'session_cancellation_notifications' => 'boolean',
            'phone_visible' => 'boolean',
            'email_visible' => 'boolean',
            'onboarding_completed' => 'boolean',
        ];
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmailNotification);
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

    /**
     * Get the users that this user is ignoring.
     */
    public function ignoredUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'player_ignores', 'ignorer_id', 'ignored_id')
                    ->withPivot('reason')
                    ->withTimestamps();
    }

    /**
     * Get the users who are ignoring this user.
     */
    public function ignoredByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'player_ignores', 'ignored_id', 'ignorer_id')
                    ->withPivot('reason')
                    ->withTimestamps();
    }

    /**
     * Check if this user is ignoring another user.
     */
    public function isIgnoring(User $user): bool
    {
        return PlayerIgnore::isIgnoring($this->id, $user->id);
    }

    /**
     * Check if this user is being ignored by another user.
     */
    public function isIgnoredBy(User $user): bool
    {
        return PlayerIgnore::isIgnoring($user->id, $this->id);
    }

    /**
     * Check if this user has any ignore relationship with another user (bidirectional).
     */
    public function hasIgnoreRelationshipWith(User $user): bool
    {
        return PlayerIgnore::hasIgnoreRelationship($this->id, $user->id);
    }

    /**
     * Get all users that this user can play with (not ignored).
     */
    public function getCompatiblePlayers(): Collection
    {
        return User::where('is_active', true)
                   ->where('id', '!=', $this->id)
                   ->whereNotExists(function ($query) {
                       $query->select(DB::raw(1))
                             ->from('player_ignores')
                             ->where(function ($subQuery) {
                                 $subQuery->where('ignorer_id', $this->id)
                                         ->whereRaw('ignored_id = users.id');
                             })->orWhere(function ($subQuery) {
                                 $subQuery->where('ignored_id', $this->id)
                                         ->whereRaw('ignorer_id = users.id');
                             });
                   })
                   ->get();
    }

    /**
     * Check if email notifications are enabled for this user.
     */
    public function hasEmailNotificationsEnabled(): bool
    {
        return $this->email_notifications_enabled;
    }

    /**
     * Check if session invitation notifications are enabled for this user.
     */
    public function hasSessionInvitationNotificationsEnabled(): bool
    {
        return $this->email_notifications_enabled && $this->session_invitation_notifications;
    }

    /**
     * Check if session confirmation notifications are enabled for this user.
     */
    public function hasSessionConfirmationNotificationsEnabled(): bool
    {
        return $this->email_notifications_enabled && $this->session_confirmation_notifications;
    }

    /**
     * Check if session reminder notifications are enabled for this user.
     */
    public function hasSessionReminderNotificationsEnabled(): bool
    {
        return $this->email_notifications_enabled && $this->session_reminder_notifications;
    }

    /**
     * Check if session cancellation notifications are enabled for this user.
     */
    public function hasSessionCancellationNotificationsEnabled(): bool
    {
        return $this->email_notifications_enabled && $this->session_cancellation_notifications;
    }

    /**
     * Override the sendNotification method to check user preferences.
     */
    public function sendNotification($notification)
    {
        // Check if the user has email notifications enabled
        if (!$this->hasEmailNotificationsEnabled()) {
            return;
        }

        // Check specific notification type preferences
        if ($notification instanceof \App\Notifications\SessionInvitationNotification && !$this->hasSessionInvitationNotificationsEnabled()) {
            return;
        }

        if ($notification instanceof \App\Notifications\SessionConfirmationNotification && !$this->hasSessionConfirmationNotificationsEnabled()) {
            return;
        }

        if ($notification instanceof \App\Notifications\SessionReminderNotification && !$this->hasSessionReminderNotificationsEnabled()) {
            return;
        }

        if ($notification instanceof \App\Notifications\SessionCancellationNotification && !$this->hasSessionCancellationNotificationsEnabled()) {
            return;
        }

        // If all checks pass, send the notification
        parent::sendNotification($notification);
    }

    public function isPhoneVisible(): bool
    {
        return $this->phone_visible;
    }

    public function isEmailVisible(): bool
    {
        return $this->email_visible;
    }
    
    public function getVisiblePhone(): string
    {
        return $this->phone;
    }

    public function getVisibleEmail(): string
    {
        return $this->email;
    }
}
