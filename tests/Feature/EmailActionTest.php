<?php

use App\Models\PadelSession;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('email action routes exist', function () {
    $session = PadelSession::factory()->create();
    $participant = SessionParticipant::factory()->create([
        'session_id' => $session->id,
    ]);

    $this->assertTrue(route('session.invitation.accept', ['session' => $session->id, 'participant' => $participant->id]) !== null);
    $this->assertTrue(route('session.invitation.decline', ['session' => $session->id, 'participant' => $participant->id]) !== null);
    $this->assertTrue(route('session.invitation.view', ['session' => $session->id]) !== null);
});
