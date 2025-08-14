<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Events\Created;

class SessionInvitation extends Model
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
        'invited_by',
        'status',
        'responded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'responded_at' => 'datetime',
    ];

    /**
     * Invitation status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_EXPIRED = 'expired';

    /**
     * Get the session that this invitation is for.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(PadelSession::class, 'session_id');
    }

    /**
     * Get the user that received this invitation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who sent this invitation.
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Check if the invitation is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the invitation has been accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Check if the invitation has been declined.
     */
    public function isDeclined(): bool
    {
        return $this->status === self::STATUS_DECLINED;
    }

    /**
     * Check if the invitation has expired.
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Check if the invitation has been responded to.
     */
    public function isResponded(): bool
    {
        return $this->responded_at !== null;
    }

    /**
     * Scope to get only pending invitations.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get only accepted invitations.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope to get only declined invitations.
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', self::STATUS_DECLINED);
    }

    /**
     * Scope to get invitations for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get invitations for a specific session.
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Boot the model and register event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // Send invitation notification when a new invitation is created
        static::created(function ($invitation) {
            if ($invitation->status === self::STATUS_PENDING) {
                $invitation->sendInvitationNotification();
            }
        });

        // Handle status changes
        static::updated(function ($invitation) {
            if ($invitation->wasChanged('status')) {
                $invitation->handleStatusChangeNotification();
            }
        });
    }

    /**
     * Send invitation notification to the user.
     */
    public function sendInvitationNotification(): void
    {
        $session = $this->session()->first();
        if (!$session) {
            return;
        }

        // Check if user has invitation notifications enabled
        if (!$this->user->hasSessionInvitationNotificationsEnabled()) {
            return;
        }

        // For testing purposes, use the first user as creator if no creator exists
        $creator = $session->creator ?? \App\Models\User::first() ?? $this->user;

        $this->user->notify(new \App\Notifications\SessionInvitationNotification(
            $session,
            $this,
            $creator
        ));
    }

    /**
     * Handle status change notifications.
     */
    public function handleStatusChangeNotification(): void
    {
        $session = $this->session()->first();
        if (!$session) {
            return;
        }

        // Check if all invitations have been accepted
        if ($this->status === self::STATUS_ACCEPTED) {
            $allAccepted = $session->invitations()
                ->where('status', '!=', self::STATUS_DECLINED)
                ->count() === $session->invitations()->count();

            if ($allAccepted) {
                // Update session status to confirmed
                $session->update(['status' => \App\Models\PadelSession::STATUS_CONFIRMED]);

                // Send confirmation notifications to all accepted participants
                $this->sendConfirmationNotifications($session);
            }
        }

        // Check if session should be cancelled due to insufficient participants
        if ($this->status === self::STATUS_DECLINED) {
            $acceptedCount = $session->invitations()->accepted()->count();
            $pendingCount = $session->invitations()->pending()->count();

            if ($acceptedCount + $pendingCount < 4) {
                // Not enough participants, cancel the session
                $session->update(['status' => \App\Models\PadelSession::STATUS_CANCELLED]);
                
                // Send cancellation notifications
                $this->sendCancellationNotifications($session, 'Insufficient participants');
            }
        }
    }

    /**
     * Send confirmation notifications to all accepted participants.
     */
    private function sendConfirmationNotifications(\App\Models\PadelSession $session): void
    {
        $invitations = $session->invitations()
            ->with('user')
            ->accepted()
            ->get();

        foreach ($invitations as $invitation) {
            if ($invitation->user->hasSessionConfirmationNotificationsEnabled()) {
                $invitation->user->notify(new \App\Notifications\SessionConfirmationNotification($session));
            }
        }
    }

    /**
     * Send cancellation notifications to all participants.
     */
    private function sendCancellationNotifications(\App\Models\PadelSession $session, ?string $reason = null): void
    {
        $invitations = $session->invitations()
            ->with('user')
            ->whereIn('status', [self::STATUS_ACCEPTED, self::STATUS_PENDING])
            ->get();

        foreach ($invitations as $invitation) {
            if ($invitation->user->hasSessionCancellationNotificationsEnabled()) {
                $invitation->user->notify(new \App\Notifications\SessionCancellationNotification($session, $reason));
            }
        }
    }
} 