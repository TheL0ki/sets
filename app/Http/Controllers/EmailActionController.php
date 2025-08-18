<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PadelSession;
use App\Models\SessionInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EmailActionController extends Controller
{
    /**
     * Accept a session invitation via email link.
     */
    public function acceptInvitation(Request $request, PadelSession $session, SessionInvitation $invitation): RedirectResponse
    {
        // Verify the signed URL
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid signature');
        }

        // Check if the invitation belongs to the session
        if ($invitation->session_id !== $session->id) {
            abort(404, 'Invitation not found in this session');
        }

        // Check if the invitation is still pending
        if (!$invitation->isPending()) {
            return redirect()->route('dashboard')
                ->with('error', 'This invitation has already been responded to.');
        }

        try {
            // Update invitation status to accepted
            $invitation->update([
                'status' => SessionInvitation::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);

            // Check if session should be confirmed
            $session->checkAndUpdateConfirmationStatus();

            return redirect()->route('padel-sessions.show', $session)
                ->with('success', 'Invitation accepted successfully!');

        } catch (\Exception $e) {
            Log::error('Error accepting session invitation', [
                'session_id' => $session->id,
                'invitation_id' => $invitation->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'An error occurred while accepting the invitation. Please try again.');
        }
    }

    /**
     * Decline a session invitation via email link.
     */
    public function declineInvitation(Request $request, PadelSession $session, SessionInvitation $invitation): RedirectResponse
    {
        // Verify the signed URL
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid signature');
        }

        // Check if the invitation belongs to the session
        if ($invitation->session_id !== $session->id) {
            abort(404, 'Invitation not found in this session');
        }

        // Check if the invitation is still pending
        if (!$invitation->isPending()) {
            return redirect()->route('dashboard')
                ->with('error', 'This invitation has already been responded to.');
        }

        try {
            // Update invitation status to declined
            $invitation->update([
                'status' => SessionInvitation::STATUS_DECLINED,
                'responded_at' => now(),
            ]);

            // Check if we need to cancel the session due to insufficient participants
            $acceptedCount = $session->invitations()->accepted()->count();
            $pendingCount = $session->invitations()->pending()->count();

            if ($acceptedCount + $pendingCount < 4) {
                // Not enough participants, cancel the session
                $session->update(['status' => PadelSession::STATUS_CANCELLED]);
                
                // Send cancellation notifications
                $this->sendCancellationNotifications($session, 'Insufficient participants');
            }

            return redirect()->route('dashboard')
                ->with('success', 'Invitation declined. Thank you for your response.');

        } catch (\Exception $e) {
            Log::error('Error declining session invitation', [
                'session_id' => $session->id,
                'invitation_id' => $invitation->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'An error occurred while declining the invitation. Please try again.');
        }
    }

    /**
     * View session details via email link.
     */
    public function viewSession(Request $request, PadelSession $session): View
    {
        // Verify the signed URL
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid signature');
        }

        // Load session with invitations and matches
        $session->load([
            'invitations.user',
            'matches.players.user',
            'creator'
        ]);

        return view('padel-sessions.show', compact('session'))->with('padelSession', $session);
    }

    /**
     * View confirmed session details via email link.
     */
    public function viewConfirmedSession(Request $request, PadelSession $session): View
    {
        // Verify the signed URL
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid signature');
        }

        // Load session with invitations and matches
        $session->load([
            'invitations.user',
            'matches.players.user',
            'creator'
        ]);

        return view('padel-sessions.show', compact('session'))->with('padelSession', $session);
    }

    /**
     * View session reminder details via email link.
     */
    public function viewReminderSession(Request $request, PadelSession $session): View
    {
        // Verify the signed URL
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid signature');
        }

        // Load session with invitations and matches
        $session->load([
            'invitations.user',
            'matches.players.user',
            'creator'
        ]);

        return view('padel-sessions.show', compact('session'))->with('padelSession', $session);
    }



    /**
     * Send cancellation notifications to all participants.
     */
    private function sendCancellationNotifications(PadelSession $session, ?string $reason = null): void
    {
        $invitations = $session->invitations()
            ->with('user')
            ->whereIn('status', [SessionInvitation::STATUS_ACCEPTED, SessionInvitation::STATUS_PENDING])
            ->get();

        foreach ($invitations as $invitation) {
            $invitation->user->notify(new \App\Notifications\SessionCancellationNotification($session, $reason));
        }
    }
}
