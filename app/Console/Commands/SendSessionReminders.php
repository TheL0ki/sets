<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PadelSession;
use App\Notifications\SessionReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendSessionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:send-reminders {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder notifications for sessions starting in 24 hours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('DRY RUN MODE - No emails will be sent');
        }

        // Find sessions starting in approximately 24 hours (23-25 hours to account for timing)
        $startTime = now()->addHours(23);
        $endTime = now()->addHours(25);

        $sessions = PadelSession::query()
            ->where('status', PadelSession::STATUS_CONFIRMED)
            ->whereBetween('start_time', [$startTime, $endTime])
            ->with(['invitations.user'])
            ->get();

        if ($sessions->isEmpty()) {
            $this->info('No sessions found starting in the next 24 hours.');
            return self::SUCCESS;
        }

        $this->info("Found {$sessions->count()} session(s) starting in the next 24 hours.");

        $totalNotifications = 0;

        foreach ($sessions as $session) {
            $acceptedInvitations = $session->invitations()
                ->with('user')
                ->accepted()
                ->get();

            if ($acceptedInvitations->isEmpty()) {
                $this->warn("Session #{$session->id} has no accepted invitations, skipping.");
                continue;
            }

            $this->info("Processing session #{$session->id} at {$session->start_time->format('d.m.Y H:i')}");

            foreach ($acceptedInvitations as $invitation) {
                // Check if user has reminder notifications enabled
                if (!$invitation->user->hasSessionReminderNotificationsEnabled()) {
                    if ($isDryRun) {
                        $this->line("  - Would skip reminder to: {$invitation->user->name} ({$invitation->user->email}) - notifications disabled");
                    }
                    continue;
                }

                if ($isDryRun) {
                    $this->line("  - Would send reminder to: {$invitation->user->name} ({$invitation->user->email})");
                } else {
                    try {
                        $invitation->user->notify(new SessionReminderNotification($session));
                        $this->line("  - Sent reminder to: {$invitation->user->name} ({$invitation->user->email})");
                        $totalNotifications++;
                    } catch (\Exception $e) {
                        $this->error("  - Failed to send reminder to {$invitation->user->email}: {$e->getMessage()}");
                        Log::error('Failed to send session reminder', [
                            'session_id' => $session->id,
                            'user_id' => $invitation->user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        if ($isDryRun) {
            $this->info("DRY RUN COMPLETE - Would have sent {$totalNotifications} reminder(s)");
        } else {
            $this->info("Successfully sent {$totalNotifications} reminder notification(s)");
        }

        return self::SUCCESS;
    }
}
