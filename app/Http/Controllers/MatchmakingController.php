<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MatchmakingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchmakingController extends Controller
{
    public function __construct(
        private MatchmakingService $matchmakingService
    ) {}

    /**
     * Show the matchmaking dashboard
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get recent matchmaking activity
        $recentSessions = $user->sessions()
            ->where('padel_sessions.start_time', '>=', now()->subWeeks(2))
            ->orderBy('padel_sessions.start_time', 'desc')
            ->limit(10)
            ->get();

        return view('matchmaking.index', compact('recentSessions'));
    }

    /**
     * Run the matchmaking algorithm
     */
    public function run(Request $request): RedirectResponse
    {
        // Check if user has admin privileges (for now, just check if they're the first user)
        // In a real app, you'd have proper admin roles
        if ($request->user()->id !== 1) {
            abort(403, 'Only administrators can run the matchmaking algorithm.');
        }

        try {
            $results = $this->matchmakingService->runMatchmaking();
            
            if (!empty($results['errors'])) {
                return back()->withErrors($results['errors']);
            }

            $message = "Matchmaking completed successfully! Created {$results['sessions_created']} sessions and sent {$results['invitations_sent']} invitations.";
            
            return redirect()->route('matchmaking.index')->with('success', $message);
            
        } catch (\Exception $e) {
            return back()->withErrors(['Matchmaking failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Show matchmaking statistics
     */
    public function stats(Request $request): View
    {
        $user = $request->user();
        
        // Get user's matchmaking statistics
        $stats = [
            'total_sessions' => $user->sessions()->count(),
            'confirmed_sessions' => $user->sessions()->where('padel_sessions.status', 'confirmed')->count(),
            'pending_invitations' => $user->sessionInvitations()->where('status', 'pending')->count(),
            'recent_matches' => $user->getRecentMatchCount(),
            'weekly_goal' => $user->preferred_frequency_per_week,
            'monthly_goal' => $user->preferred_frequency_per_month,
        ];

        return view('matchmaking.stats', compact('stats'));
    }
}
