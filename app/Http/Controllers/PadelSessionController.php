<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PadelSessionRequest;
use App\Models\Availability;
use App\Models\PadelSession;
use App\Models\SessionInvitation;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PadelSessionController extends Controller
{
    /**
     * Display a listing of confirmed padel sessions.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get confirmed sessions where user is a participant
        $mySessions = $user->sessions()
            ->where('padel_sessions.status', PadelSession::STATUS_CONFIRMED)
            ->orderBy('padel_sessions.start_time')
            ->paginate(10);

        // Get pending invitations for the user
        $pendingInvitations = $user->sessionInvitations()
            ->where('status', SessionInvitation::STATUS_PENDING)
            ->with(['session.creator'])
            ->orderBy('created_at')
            ->get();

        return view('padel-sessions.index', compact('mySessions', 'pendingInvitations'));
    }

    /**
     * Create a session via algorithm (for internal use).
     */
    public function createViaAlgorithm(array $data): PadelSession
    {
        $session = PadelSession::create([
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'location' => $data['location'],
            'status' => PadelSession::STATUS_PENDING,
            'created_by' => $data['created_by'] ?? 1, // System user or first user
            'max_players' => 4, // Fixed at 4 players
        ]);

        // Send invitations to all players
        foreach ($data['player_ids'] as $playerId) {
            SessionInvitation::create([
                'session_id' => $session->id,
                'user_id' => $playerId,
                'status' => SessionInvitation::STATUS_PENDING,
            ]);
        }

        return $session;
    }

    /**
     * Display the specified padel session.
     */
    public function show(PadelSession $padelSession): View
    {
        $padelSession->load(['participants.user', 'matches.matchPlayers.user', 'creator']);
        
        return view('padel-sessions.show', compact('padelSession'));
    }



    /**
     * Join a padel session.
     */
    public function join(Request $request, PadelSession $padelSession): RedirectResponse
    {
        $user = $request->user();

        // Check if user is already a participant
        $existingParticipant = $padelSession->participants()
            ->where('user_id', $user->id)
            ->first();

        if ($existingParticipant) {
            return redirect()
                ->route('padel-sessions.show', $padelSession)
                ->with('info', 'You are already a participant in this session.');
        }

        // Check if session is full
        if ($padelSession->getParticipantCount() >= $padelSession->max_players) {
            return redirect()
                ->route('padel-sessions.show', $padelSession)
                ->with('error', 'This session is full.');
        }

        // Add user as participant
        SessionParticipant::create([
            'session_id' => $padelSession->id,
            'user_id' => $user->id,
            'status' => SessionParticipant::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);

        return redirect()
            ->route('padel-sessions.show', $padelSession)
            ->with('success', 'You have joined the session successfully.');
    }

    /**
     * Leave a padel session.
     */
    public function leave(Request $request, PadelSession $padelSession): RedirectResponse
    {
        $user = $request->user();

        $participant = $padelSession->participants()
            ->where('user_id', $user->id)
            ->first();

        if (!$participant) {
            return redirect()
                ->route('padel-sessions.show', $padelSession)
                ->with('error', 'You are not a participant in this session.');
        }

        $participant->delete();

        $padelSession->status = PadelSession::STATUS_CANCELLED;
        $padelSession->save();

        $user->availabilities()->where('start_time', '>=', $padelSession->start_time)->where('end_time', '<=', $padelSession->end_time)->delete();

        return redirect()
            ->route('padel-sessions.index')
            ->with('success', 'You have left the session successfully.');
    }

    /**
     * Accept a session invitation.
     */
    public function acceptInvitation(Request $request, SessionInvitation $invitation): RedirectResponse
    {
        $user = $request->user();

        // Ensure user owns the invitation
        if ($invitation->user_id !== $user->id) {
            abort(403);
        }

        // Update invitation status
        $invitation->update([
            'status' => SessionInvitation::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);

        // Add user as participant
        SessionParticipant::create([
            'session_id' => $invitation->session_id,
            'user_id' => $user->id,
            'status' => SessionParticipant::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);

        // Check if session should be confirmed
        $session = $invitation->session;
        $session->checkAndUpdateConfirmationStatus();

        return redirect()
            ->route('padel-sessions.show', $invitation->session)
            ->with('success', 'Invitation accepted successfully.');
    }

    /**
     * Decline a session invitation.
     */
    public function declineInvitation(Request $request, SessionInvitation $invitation): RedirectResponse
    {
        $user = $request->user();

        // Ensure user owns the invitation
        if ($invitation->user_id !== $user->id) {
            abort(403);
        }

        // Update invitation status
        $invitation->update([
            'status' => SessionInvitation::STATUS_DECLINED,
            'responded_at' => now(),
        ]);

        $invitation->session->status = PadelSession::STATUS_CANCELLED;
        $invitation->session->save();

        $user->availabilities()->where('start_time', '>=', $invitation->session->start_time)->where('end_time', '<=', $invitation->session->end_time)->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Invitation declined successfully.');
    }
}
