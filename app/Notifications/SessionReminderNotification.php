<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\PadelSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class SessionReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected PadelSession $session
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
        $viewUrl = URL::signedRoute('session.reminder.view', [
            'session' => $this->session->id,
        ]);

        $participants = $this->session->participants()
            ->with('user')
            ->confirmed()
            ->get()
            ->pluck('user.name')
            ->join(', ');

        return (new MailMessage)
            ->subject('Padel Session Reminder - Tomorrow ' . $this->session->start_time->format('d.m.Y H:i'))
            ->view('emails.session-reminder', [
                'notifiable' => $notifiable,
                'session' => $this->session,
                'participants' => $participants,
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
            'session_start_time' => $this->session->start_time,
            'session_location' => $this->session->location,
            'participant_count' => $this->session->participants()->confirmed()->count(),
        ];
    }
}
