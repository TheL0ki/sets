<?php

use App\Models\PadelSession;
use App\Models\SessionInvitation;
use App\Models\User;
use App\Notifications\SessionCancellationNotification;
use App\Notifications\SessionConfirmationNotification;
use App\Notifications\SessionInvitationNotification;
use App\Notifications\SessionReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
});

test('session invitation notification is sent when invitation is created', function () {
    $creator = User::factory()->create();
    $user = User::factory()->create();
    $session = PadelSession::factory()->create([
        'status' => PadelSession::STATUS_PENDING,
    ]);

    $invitation = SessionInvitation::create([
        'session_id' => $session->id,
        'user_id' => $user->id,
        'status' => SessionInvitation::STATUS_PENDING,
    ]);

    Notification::assertSentTo($user, SessionInvitationNotification::class);
});

test('session confirmation notification is sent when all invitations are accepted', function () {
    $creator = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    $user4 = User::factory()->create();

    $session = PadelSession::factory()->create([
        'status' => PadelSession::STATUS_PENDING,
    ]);

    // Create 4 invitations
    $invitations = [
        SessionInvitation::create([
            'session_id' => $session->id,
            'user_id' => $user1->id,
            'status' => SessionInvitation::STATUS_PENDING,
        ]),
        SessionInvitation::create([
            'session_id' => $session->id,
            'user_id' => $user2->id,
            'status' => SessionInvitation::STATUS_PENDING,
        ]),
        SessionInvitation::create([
            'session_id' => $session->id,
            'user_id' => $user3->id,
            'status' => SessionInvitation::STATUS_PENDING,
        ]),
        SessionInvitation::create([
            'session_id' => $session->id,
            'user_id' => $user4->id,
            'status' => SessionInvitation::STATUS_PENDING,
        ]),
    ];

    // Accept all invitations
    foreach ($invitations as $invitation) {
        $invitation->update([
            'status' => SessionInvitation::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);
    }

    // Check that confirmation notifications were sent to all participants
    Notification::assertSentTo($user1, SessionConfirmationNotification::class);
    Notification::assertSentTo($user2, SessionConfirmationNotification::class);
    Notification::assertSentTo($user3, SessionConfirmationNotification::class);
    Notification::assertSentTo($user4, SessionConfirmationNotification::class);

    // Check that session status was updated to confirmed
    $this->assertEquals(PadelSession::STATUS_CONFIRMED, $session->fresh()->status);
});

test('session cancellation notification is sent when session is cancelled', function () {
    $creator = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $session = PadelSession::factory()->create([
        'status' => PadelSession::STATUS_PENDING,
    ]);

    // Create invitations
    SessionInvitation::create([
        'session_id' => $session->id,
        'user_id' => $user1->id,
        'status' => SessionInvitation::STATUS_ACCEPTED,
    ]);

    SessionInvitation::create([
        'session_id' => $session->id,
        'user_id' => $user2->id,
        'status' => SessionInvitation::STATUS_PENDING,
    ]);

    // Cancel the session
    $session->cancelSession('Test cancellation');

    // Check that cancellation notifications were sent
    Notification::assertSentTo($user1, SessionCancellationNotification::class);
    Notification::assertSentTo($user2, SessionCancellationNotification::class);

    // Check that session status was updated
    $this->assertEquals(PadelSession::STATUS_CANCELLED, $session->fresh()->status);
});

test('session is automatically cancelled when insufficient participants', function () {
    $creator = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $session = PadelSession::factory()->create([
        'status' => PadelSession::STATUS_PENDING,
    ]);

    // Create 3 invitations (not enough for 4-player session)
    $invitations = [
        SessionInvitation::create([
            'session_id' => $session->id,
            'user_id' => $user1->id,
            'status' => SessionInvitation::STATUS_PENDING,
        ]),
        SessionInvitation::create([
            'session_id' => $session->id,
            'user_id' => $user2->id,
            'status' => SessionInvitation::STATUS_PENDING,
        ]),
        SessionInvitation::create([
            'session_id' => $session->id,
            'user_id' => $user3->id,
            'status' => SessionInvitation::STATUS_PENDING,
        ]),
    ];

    // Decline one invitation (now only 2 participants remain)
    $invitations[0]->update(['status' => SessionInvitation::STATUS_DECLINED]);

    // Check that session was automatically cancelled
    $this->assertEquals(PadelSession::STATUS_CANCELLED, $session->fresh()->status);

    // Check that cancellation notifications were sent
    Notification::assertSentTo($user2, SessionCancellationNotification::class);
    Notification::assertSentTo($user3, SessionCancellationNotification::class);
});

test('user preferences are respected when sending notifications', function () {
    $creator = User::factory()->create();
    $user = User::factory()->create([
        'email_notifications_enabled' => false,
    ]);

    $session = PadelSession::factory()->create([
        'status' => PadelSession::STATUS_PENDING,
    ]);

    $invitation = SessionInvitation::create([
        'session_id' => $session->id,
        'user_id' => $user->id,
        'status' => SessionInvitation::STATUS_PENDING,
    ]);

    // No notification should be sent because user has disabled email notifications
    Notification::assertNotSentTo($user, SessionInvitationNotification::class);
});

test('specific notification preferences are respected', function () {
    $creator = User::factory()->create();
    $user1 = User::factory()->create([
        'email_notifications_enabled' => true,
        'session_invitation_notifications' => false,
        'session_confirmation_notifications' => true,
    ]);
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    $user4 = User::factory()->create();

    $session = PadelSession::factory()->create([
        'status' => PadelSession::STATUS_PENDING,
    ]);

    // Create 4 invitations
    $invitations = [
        SessionInvitation::create([
            'session_id' => $session->id,
            'user_id' => $user1->id,
            'status' => SessionInvitation::STATUS_PENDING,
        ]),
        SessionInvitation::create([
            'session_id' => $session->id,
            'user_id' => $user2->id,
            'status' => SessionInvitation::STATUS_PENDING,
        ]),
        SessionInvitation::create([
            'session_id' => $session->id,
            'user_id' => $user3->id,
            'status' => SessionInvitation::STATUS_PENDING,
        ]),
        SessionInvitation::create([
            'session_id' => $session->id,
            'user_id' => $user4->id,
            'status' => SessionInvitation::STATUS_PENDING,
        ]),
    ];

    // Invitation notification should not be sent to user1 (disabled)
    Notification::assertNotSentTo($user1, SessionInvitationNotification::class);

    // Accept all invitations
    foreach ($invitations as $invitation) {
        $invitation->update([
            'status' => SessionInvitation::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);
    }

    // Confirmation notification should be sent to user1 (enabled)
    Notification::assertSentTo($user1, SessionConfirmationNotification::class);
});

test('email action routes work correctly', function () {
    $user = User::factory()->create();
    $session = PadelSession::factory()->create();
    
    $invitation = SessionInvitation::factory()->create([
        'session_id' => $session->id,
        'user_id' => $user->id,
        'status' => SessionInvitation::STATUS_PENDING,
    ]);

    // Test accept invitation route
    $acceptUrl = URL::signedRoute('session.invitation.accept', [
        'session' => $session->id,
        'invitation' => $invitation->id,
    ]);

    $response = $this->get($acceptUrl);
    $response->assertRedirect(route('padel-sessions.show', $session));

    // Test decline invitation route
    $declineUrl = URL::signedRoute('session.invitation.decline', [
        'session' => $session->id,
        'invitation' => $invitation->id,
    ]);

    $response = $this->get($declineUrl);
    $response->assertRedirect(route('dashboard'));

    // Test view session route
    $viewUrl = URL::signedRoute('session.invitation.view', [
        'session' => $session->id,
    ]);

    $response = $this->get($viewUrl);
    $response->assertStatus(200);
    $response->assertViewIs('padel-sessions.show');
});

test('notification settings can be updated', function () {
    $user = User::factory()->create([
        'email_notifications_enabled' => true,
        'session_invitation_notifications' => true,
    ]);

    $this->actingAs($user);

    $response = $this->patch(route('notification-settings.update'), [
        'email_notifications_enabled' => false,
        'session_invitation_notifications' => false,
        'session_confirmation_notifications' => false,
        'session_reminder_notifications' => false,
        'session_cancellation_notifications' => false,
    ]);

    $response->assertRedirect(route('notification-settings.index'));
    $response->assertSessionHas('success');

    $user->refresh();
    $this->assertFalse($user->email_notifications_enabled);
    $this->assertFalse($user->session_invitation_notifications);
});

test('reminder command respects user preferences', function () {
    $user = User::factory()->create([
        'email_notifications_enabled' => true,
        'session_reminder_notifications' => false,
    ]);

    $session = PadelSession::factory()->create([
        'status' => PadelSession::STATUS_CONFIRMED,
        'start_time' => now()->addHours(24),
    ]);

    SessionInvitation::factory()->create([
        'session_id' => $session->id,
        'user_id' => $user->id,
        'status' => SessionInvitation::STATUS_ACCEPTED,
    ]);

    $this->artisan('sessions:send-reminders', ['--dry-run' => true])
        ->expectsOutput('DRY RUN MODE - No emails will be sent')
        ->expectsOutput('Found 1 session(s) starting in the next 24 hours.')
        ->expectsOutput("Processing session #{$session->id} at " . $session->start_time->format('d.m.Y H:i'))
        ->expectsOutput("  - Would skip reminder to: {$user->name} ({$user->email}) - notifications disabled")
        ->assertExitCode(0);
});
