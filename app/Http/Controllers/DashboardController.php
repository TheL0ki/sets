<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\PadelMatch;
use App\Models\PadelSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the user's dashboard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Check if user needs to complete onboarding
        if (!$user->onboarding_completed) {
            return redirect()->route('onboarding.welcome');
        }

        // Get upcoming sessions where user is a participant
        $upcomingSessions = $user->sessions()
            ->where('padel_sessions.start_time', '>', now())
            ->whereIn('padel_sessions.status', [PadelSession::STATUS_PENDING, PadelSession::STATUS_CONFIRMED])
            ->orderBy('padel_sessions.start_time')
            ->limit(5)
            ->get();

        // Get recent matches
        $recentMatches = $user->matches()
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        // Get pending session invitations
        $pendingInvitations = $user->sessionInvitations()
            ->where('status', 'pending')
            ->with('session')
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        // Get user stats
        $stats = [
            'total_sessions' => $user->sessions()->count(),
            'total_matches' => $user->matches()->count(),
            'recent_sessions' => $user->sessions()
                ->where('padel_sessions.start_time', '>=', now()->subDays(30))
                ->count(),
            'recent_matches' => $user->matches()
                ->whereHas('session', function ($query) {
                    $query->where('start_time', '>=', now()->subDays(30));
                })
                ->count(),
        ];

        return view('dashboard', compact(
            'upcomingSessions',
            'recentMatches',
            'pendingInvitations',
            'stats'
        ));
    }
}
