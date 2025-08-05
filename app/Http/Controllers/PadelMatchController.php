<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MatchPlayer;
use App\Models\PadelMatch;
use App\Models\PadelSession;
use App\Models\User;
use App\Models\SessionParticipant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PadelMatchController extends Controller
{
    /**
     * Display a listing of matches for a session.
     */
    public function index(Request $request, PadelSession $padel_session): View
    {
        $matches = $padel_session->matches()
            ->with(['matchPlayers.user'])
            ->orderBy('match_number')
            ->get();

        return view('padel-matches.index', compact('padel_session', 'matches'));
    }

    /**
     * Show the form for creating a new match.
     */
    public function create(Request $request, PadelSession $padel_session): View
    {
        // Ensure user is a confirmed participant in the session
        $participant = $padel_session->participants()
            ->where('user_id', $request->user()->id)
            ->where('status', SessionParticipant::STATUS_CONFIRMED)
            ->first();

        if (!$participant) {
            abort(403, 'You must be a confirmed participant to add matches.');
        }

        // Get confirmed participants for team assignment
        $participants = $padel_session->participants()
            ->with('user')
            ->where('status', SessionParticipant::STATUS_CONFIRMED)
            ->get();

        return view('padel-matches.create', compact('padel_session', 'participants'));
    }

    /**
     * Store a newly created match.
     */
    public function store(Request $request, PadelSession $padel_session): RedirectResponse
    {
        // Ensure user is a confirmed participant in the session
        $participant = $padel_session->participants()
            ->where('user_id', $request->user()->id)
            ->where('status', SessionParticipant::STATUS_CONFIRMED)
            ->first();

        if (!$participant) {
            abort(403, 'You must be a confirmed participant to add matches.');
        }

        $request->validate([
            'match_number' => 'required|integer|min:1',
            'team_a_players' => 'required|array|size:2',
            'team_b_players' => 'required|array|size:2',
            'team_a_players.*' => 'exists:users,id',
            'team_b_players.*' => 'exists:users,id',
        ]);

        // Check if match number already exists
        $existingMatch = $padel_session->matches()
            ->where('match_number', $request->input('match_number'))
            ->first();

        if ($existingMatch) {
            return back()
                ->withInput()
                ->withErrors(['match_number' => 'A match with this number already exists in this session.']);
        }

        // Create the match
        $match = $padel_session->matches()->create([
            'match_number' => $request->input('match_number'),
            'status' => PadelMatch::STATUS_PENDING,
        ]);

        // Add team A players
        foreach ($request->input('team_a_players') as $playerId) {
            MatchPlayer::create([
                'match_id' => $match->id,
                'user_id' => $playerId,
                'team' => MatchPlayer::TEAM_A,
                'confirmed_at' => now(),
            ]);
        }

        // Add team B players
        foreach ($request->input('team_b_players') as $playerId) {
            MatchPlayer::create([
                'match_id' => $match->id,
                'user_id' => $playerId,
                'team' => MatchPlayer::TEAM_B,
                'confirmed_at' => now(),
            ]);
        }

        return redirect()
            ->route('padel-sessions.show', $padel_session)
            ->with('success', 'Match created successfully.');
    }

    /**
     * Display the specified match.
     */
    public function show(PadelSession $padel_session, PadelMatch $padel_match): View
    {
        $padel_match->load(['matchPlayers.user', 'session']);

        return view('padel-matches.show', compact('padel_session', 'padel_match'));
    }

    /**
     * Show the form for editing the specified match.
     */
    public function edit(Request $request, PadelSession $padel_session, PadelMatch $padel_match): View
    {
        // Ensure user is a confirmed participant in the session
        $participant = $padel_session->participants()
            ->where('user_id', $request->user()->id)
            ->where('status', SessionParticipant::STATUS_CONFIRMED)
            ->first();

        if (!$participant) {
            abort(403, 'You must be a confirmed participant to edit matches.');
        }

        $padel_match->load(['matchPlayers.user']);
        
        $participants = $padel_session->participants()
            ->with('user')
            ->where('status', SessionParticipant::STATUS_CONFIRMED)
            ->get();

        return view('padel-matches.edit', compact('padel_session', 'padel_match', 'participants'));
    }

    /**
     * Update the specified match.
     */
    public function update(Request $request, PadelSession $padel_session, PadelMatch $padel_match): RedirectResponse
    {
        // Ensure user is a confirmed participant in the session
        $participant = $padel_session->participants()
            ->where('user_id', $request->user()->id)
            ->where('status', SessionParticipant::STATUS_CONFIRMED)
            ->first();

        if (!$participant) {
            abort(403, 'You must be a confirmed participant to update matches.');
        }

        $request->validate([
            'team_a_score' => 'nullable|integer|min:0',
            'team_b_score' => 'nullable|integer|min:0',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'notes' => 'nullable|string|max:1000',
        ]);

        $padel_match->update($request->only(['team_a_score', 'team_b_score', 'status', 'notes']));

        // Update timestamps based on status
        if ($request->input('status') === PadelMatch::STATUS_CONFIRMED && !$padel_match->started_at) {
            $padel_match->update(['started_at' => now()]);
        }

        if ($request->input('status') === PadelMatch::STATUS_COMPLETED && !$padel_match->completed_at) {
            $padel_match->update(['completed_at' => now()]);
        }

        return redirect()
            ->route('padel-sessions.show', $padel_session)
            ->with('success', 'Match updated successfully.');
    }

    /**
     * Remove the specified match.
     */
    public function destroy(Request $request, PadelSession $padel_session, PadelMatch $padel_match): RedirectResponse
    {
        // Ensure user is a confirmed participant in the session
        $participant = $padel_session->participants()
            ->where('user_id', $request->user()->id)
            ->where('status', SessionParticipant::STATUS_CONFIRMED)
            ->first();

        if (!$participant) {
            abort(403, 'You must be a confirmed participant to delete matches.');
        }

        $padel_match->delete();

        return redirect()
            ->route('padel-sessions.show', $padel_session)
            ->with('success', 'Match deleted successfully.');
    }

    /**
     * Start a match.
     */
    public function start(Request $request, PadelSession $padel_session, PadelMatch $padel_match): RedirectResponse
    {
        // Ensure user is a confirmed participant in the session
        $participant = $padel_session->participants()
            ->where('user_id', $request->user()->id)
            ->where('status', SessionParticipant::STATUS_CONFIRMED)
            ->first();

        if (!$participant) {
            abort(403, 'You must be a confirmed participant to start matches.');
        }

        $padel_match->update([
            'status' => PadelMatch::STATUS_CONFIRMED,
            'started_at' => now(),
        ]);

        return redirect()
            ->route('padel-sessions.show', $padel_session)
            ->with('success', 'Match started successfully.');
    }

    /**
     * Complete a match with scores.
     */
    public function complete(Request $request, PadelSession $padel_session, PadelMatch $padel_match): RedirectResponse
    {
        // Ensure user is a confirmed participant in the session
        $participant = $padel_session->participants()
            ->where('user_id', $request->user()->id)
            ->where('status', SessionParticipant::STATUS_CONFIRMED)
            ->first();

        if (!$participant) {
            abort(403, 'You must be a confirmed participant to complete matches.');
        }

        $request->validate([
            'team_a_score' => 'required|integer|min:0',
            'team_b_score' => 'required|integer|min:0',
        ]);

        $padel_match->update([
            'team_a_score' => $request->input('team_a_score'),
            'team_b_score' => $request->input('team_b_score'),
            'status' => PadelMatch::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('padel-sessions.show', $padel_session)
            ->with('success', 'Match completed successfully.');
    }
}
