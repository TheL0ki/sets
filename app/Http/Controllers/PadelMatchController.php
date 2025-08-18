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
            ->orderBy('created_at', 'asc')
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
            'team_a_players' => 'required|array|size:2',
            'team_b_players' => 'required|array|size:2',
            'team_a_players.*' => 'exists:users,id',
            'team_b_players.*' => 'exists:users,id',
            'team_a_score' => 'required|integer|min:0|max:20',
            'team_b_score' => 'required|integer|min:0|max:20',
        ]);

        // Check for duplicate players
        $allPlayers = array_merge($request->input('team_a_players'), $request->input('team_b_players'));
        if (count(array_unique($allPlayers)) !== count($allPlayers)) {
            return back()
                ->withInput()
                ->withErrors(['team_a_players' => 'Each player can only be assigned to one team.']);
        }

        // Get next match number using helper method
        $nextMatchNumber = PadelMatch::getNextMatchNumber($padel_session->id);

        // Create the match
        $match = $padel_session->matches()->create([
            'match_number' => $nextMatchNumber,
            'team_a_score' => $request->input('team_a_score'),
            'team_b_score' => $request->input('team_b_score'),
        ]);

        // Add team A players
        foreach ($request->input('team_a_players') as $playerId) {
            MatchPlayer::create([
                'match_id' => $match->id,
                'user_id' => $playerId,
                'team' => MatchPlayer::TEAM_A,
            ]);
        }

        // Add team B players
        foreach ($request->input('team_b_players') as $playerId) {
            MatchPlayer::create([
                'match_id' => $match->id,
                'user_id' => $playerId,
                'team' => MatchPlayer::TEAM_B,
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
            'team_a_players' => 'required|array|size:2',
            'team_b_players' => 'required|array|size:2',
            'team_a_players.*' => 'exists:users,id',
            'team_b_players.*' => 'exists:users,id',
            'team_a_score' => 'required|integer|min:0|max:20',
            'team_b_score' => 'required|integer|min:0|max:20',
        ]);

        // Check for duplicate players
        $allPlayers = array_merge($request->input('team_a_players'), $request->input('team_b_players'));
        if (count(array_unique($allPlayers)) !== count($allPlayers)) {
            return back()
                ->withInput()
                ->withErrors(['team_a_players' => 'Each player can only be assigned to one team.']);
        }

        // Update match scores
        $padel_match->update([
            'team_a_score' => $request->input('team_a_score'),
            'team_b_score' => $request->input('team_b_score'),
        ]);

        // Update team assignments
        $padel_match->matchPlayers()->delete();
        
        // Add team A players
        foreach ($request->input('team_a_players') as $playerId) {
            MatchPlayer::create([
                'match_id' => $padel_match->id,
                'user_id' => $playerId,
                'team' => MatchPlayer::TEAM_A,
            ]);
        }

        // Add team B players
        foreach ($request->input('team_b_players') as $playerId) {
            MatchPlayer::create([
                'match_id' => $padel_match->id,
                'user_id' => $playerId,
                'team' => MatchPlayer::TEAM_B,
            ]);
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
}
