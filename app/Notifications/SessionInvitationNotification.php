<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\PadelSession;
use App\Models\SessionInvitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class SessionInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected PadelSession $session,
        protected SessionInvitation $invitation,
        protected User $invitedBy
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $acceptUrl = URL::signedRoute('session.invitation.accept', [
            'session' => $this->session->id,
            'invitation' => $this->invitation->id,
        ]);

        $declineUrl = URL::signedRoute('session.invitation.decline', [
            'session' => $this->session->id,
            'invitation' => $this->invitation->id,
        ]);

        $viewUrl = URL::signedRoute('session.invitation.view', [
            'session' => $this->session->id,
        ]);

        $participantCount = $this->session->invitations()->count();

        return (new MailMessage)
            ->subject('Padel Session Invitation - ' . $this->session->start_time->format('d.m.Y H:i'))
            ->view('emails.session-invitation', [
                'notifiable' => $notifiable,
                'session' => $this->session,
                'invitation' => $this->invitation,
                'acceptUrl' => $acceptUrl,
                'declineUrl' => $declineUrl,
                'viewUrl' => $viewUrl,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'session_id' => $this->session->id,
            'invitation_id' => $this->invitation->id,
            'invited_by' => $this->invitedBy->id,
            'session_start_time' => $this->session->start_time,
            'session_location' => $this->session->location,
        ];
    }
}
