<?php

use App\Models\PadelSession;
use App\Models\SessionInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('session is not confirmed when only some invitations are accepted', function () {
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

    // Accept only 3 invitations
    $invitations[0]->update([
        'status' => SessionInvitation::STATUS_ACCEPTED,
        'responded_at' => now(),
    ]);
    $invitations[1]->update([
        'status' => SessionInvitation::STATUS_ACCEPTED,
        'responded_at' => now(),
    ]);
    $invitations[2]->update([
        'status' => SessionInvitation::STATUS_ACCEPTED,
        'responded_at' => now(),
    ]);

    // Session should still be pending
    $this->assertEquals(PadelSession::STATUS_PENDING, $session->fresh()->status);
    $this->assertEquals(3, $session->getAcceptedInvitationsCount());
    $this->assertEquals(1, $session->getPendingInvitationsCount());
});

test('session is confirmed when all 4 invitations are accepted', function () {
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

    // Accept all 4 invitations
    foreach ($invitations as $invitation) {
        $invitation->update([
            'status' => SessionInvitation::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);
    }

    // Session should be confirmed
    $this->assertEquals(PadelSession::STATUS_CONFIRMED, $session->fresh()->status);
    $this->assertEquals(4, $session->getAcceptedInvitationsCount());
    $this->assertEquals(0, $session->getPendingInvitationsCount());
});

test('session is cancelled when some invitations are declined and not enough participants remain', function () {
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

    // Accept 3 invitations and decline 1
    $invitations[0]->update([
        'status' => SessionInvitation::STATUS_ACCEPTED,
        'responded_at' => now(),
    ]);
    $invitations[1]->update([
        'status' => SessionInvitation::STATUS_ACCEPTED,
        'responded_at' => now(),
    ]);
    $invitations[2]->update([
        'status' => SessionInvitation::STATUS_ACCEPTED,
        'responded_at' => now(),
    ]);
    $invitations[3]->update([
        'status' => SessionInvitation::STATUS_DECLINED,
        'responded_at' => now(),
    ]);

    // Session should be cancelled (not enough participants for doubles)
    $this->assertEquals(PadelSession::STATUS_CANCELLED, $session->fresh()->status);
    $this->assertEquals(3, $session->getAcceptedInvitationsCount());
    $this->assertEquals(0, $session->getPendingInvitationsCount());
});

test('centralized checkAndUpdateConfirmationStatus method works correctly', function () {
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

    // Accept only 3 invitations
    $invitations[0]->update(['status' => SessionInvitation::STATUS_ACCEPTED]);
    $invitations[1]->update(['status' => SessionInvitation::STATUS_ACCEPTED]);
    $invitations[2]->update(['status' => SessionInvitation::STATUS_ACCEPTED]);

    // Call the centralized method - should not confirm
    $session->checkAndUpdateConfirmationStatus();
    $this->assertEquals(PadelSession::STATUS_PENDING, $session->fresh()->status);

    // Accept the 4th invitation
    $invitations[3]->update(['status' => SessionInvitation::STATUS_ACCEPTED]);

    // Call the centralized method - should confirm
    $session->checkAndUpdateConfirmationStatus();
    $this->assertEquals(PadelSession::STATUS_CONFIRMED, $session->fresh()->status);
});
