<?php

use App\Models\PadelSession;
use App\Models\SessionInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('email action routes exist', function () {
    $session = PadelSession::factory()->create();
    $invitation = SessionInvitation::factory()->create([
        'session_id' => $session->id,
    ]);

    $this->assertTrue(route('session.invitation.accept', ['session' => $session->id, 'invitation' => $invitation->id]) !== null);
    $this->assertTrue(route('session.invitation.decline', ['session' => $session->id, 'invitation' => $invitation->id]) !== null);
    $this->assertTrue(route('session.invitation.view', ['session' => $session->id]) !== null);
});
