<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Events\Created;
use App\Models\PadelSession;

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

    /**
     * Boot the model and register event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // No invitation notification needed for participants - they are already part of the session

        // Send confirmation/cancellation notifications when status changes
        static::updated(function ($participant) {
            if ($participant->wasChanged('status')) {
                $participant->handleStatusChangeNotification();
            }
        });
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

        // Check if all participants have confirmed
        if ($this->status === self::STATUS_CONFIRMED) {
            $allConfirmed = $session->participants()
                ->where('status', '!=', self::STATUS_DECLINED)
                ->count() === $session->participants()->count();

            if ($allConfirmed) {
                // Update session status to confirmed
                $session->update(['status' => PadelSession::STATUS_CONFIRMED]);

                // Send confirmation notifications to all participants
                $this->sendConfirmationNotifications($session);
            }
        }

        // Check if session should be cancelled due to insufficient participants
        if ($this->status === self::STATUS_DECLINED) {
            $confirmedCount = $session->participants()->confirmed()->count();
            $invitedCount = $session->participants()->invited()->count();

            if ($confirmedCount + $invitedCount < 4) {
                // Not enough participants, cancel the session
                $session->update(['status' => PadelSession::STATUS_CANCELLED]);
                
                // Send cancellation notifications
                $this->sendCancellationNotifications($session, 'Insufficient participants');
            }
        }
    }

    /**
     * Send confirmation notifications to all participants.
     */
    private function sendConfirmationNotifications(PadelSession $session): void
    {
        $participants = $session->participants()
            ->with('user')
            ->confirmed()
            ->get();

        foreach ($participants as $participant) {
            // Check if user has confirmation notifications enabled
            if ($participant->user->hasSessionConfirmationNotificationsEnabled()) {
                $participant->user->notify(new \App\Notifications\SessionConfirmationNotification($session));
            }
        }
    }

    /**
     * Send cancellation notifications to all participants.
     */
    private function sendCancellationNotifications(PadelSession $session, ?string $reason = null): void
    {
        $participants = $session->participants()
            ->with('user')
            ->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_INVITED])
            ->get();

        foreach ($participants as $participant) {
            // Check if user has cancellation notifications enabled
            if ($participant->user->hasSessionCancellationNotificationsEnabled()) {
                $participant->user->notify(new \App\Notifications\SessionCancellationNotification($session, $reason));
            }
        }
    }
} 