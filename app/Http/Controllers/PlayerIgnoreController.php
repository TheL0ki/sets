<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PlayerIgnore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class PlayerIgnoreController extends Controller
{
    /**
     * Display the list of ignored players for the current user.
     */
    public function index(): View
    {
        $user = Auth::user();
        $ignoredUsers = $user->ignoredUsers()->paginate(10);
        
        return view('player-ignores.index', compact('ignoredUsers'));
    }

    /**
     * Show the form to add a new ignored player.
     */
    public function create(): View
    {
        $user = Auth::user();
        $availableUsers = User::where('is_active', true)
                             ->where('id', '!=', $user->id)
                             ->whereNotExists(function ($query) use ($user) {
                                 $query->select(\DB::raw(1))
                                       ->from('player_ignores')
                                       ->where('ignorer_id', $user->id)
                                       ->whereRaw('ignored_id = users.id');
                             })
                             ->get();
        
        return view('player-ignores.create', compact('availableUsers'));
    }

    /**
     * Store a new ignored player relationship.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'ignored_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        
        // Prevent self-ignoring
        if ($user->id == $request->ignored_id) {
            return back()->withErrors(['ignored_id' => 'You cannot ignore yourself.']);
        }

        // Check if already ignoring
        if ($user->isIgnoring(User::find($request->ignored_id))) {
            return back()->withErrors(['ignored_id' => 'You are already ignoring this player.']);
        }

        PlayerIgnore::create([
            'ignorer_id' => $user->id,
            'ignored_id' => $request->ignored_id,
            'reason' => $request->reason,
        ]);

        return redirect()->route('player-ignores.index')
                        ->with('success', 'Player added to ignore list successfully.');
    }

    /**
     * Remove a player from the ignore list.
     */
    public function destroy(int $ignoredId): RedirectResponse
    {
        $user = Auth::user();
        
        $ignore = PlayerIgnore::where('ignorer_id', $user->id)
                             ->where('ignored_id', $ignoredId)
                             ->first();

        if (!$ignore) {
            return back()->withErrors(['error' => 'Player not found in ignore list.']);
        }

        $ignore->delete();

        return redirect()->route('player-ignores.index')
                        ->with('success', 'Player removed from ignore list successfully.');
    }

    /**
     * API endpoint to get ignored players.
     */
    public function apiIndex(): JsonResponse
    {
        $user = Auth::user();
        $ignoredUsers = $user->ignoredUsers()->get(['users.id', 'users.name', 'users.email']);
        
        return response()->json([
            'ignored_players' => $ignoredUsers,
        ]);
    }

    /**
     * API endpoint to add a player to ignore list.
     */
    public function apiStore(Request $request): JsonResponse
    {
        $request->validate([
            'ignored_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        
        if ($user->id == $request->ignored_id) {
            return response()->json(['error' => 'You cannot ignore yourself.'], 400);
        }

        if ($user->isIgnoring(User::find($request->ignored_id))) {
            return response()->json(['error' => 'You are already ignoring this player.'], 400);
        }

        PlayerIgnore::create([
            'ignorer_id' => $user->id,
            'ignored_id' => $request->ignored_id,
            'reason' => $request->reason,
        ]);

        return response()->json(['message' => 'Player added to ignore list successfully.']);
    }

    /**
     * API endpoint to remove a player from ignore list.
     */
    public function apiDestroy(int $ignoredId): JsonResponse
    {
        $user = Auth::user();
        
        $ignore = PlayerIgnore::where('ignorer_id', $user->id)
                             ->where('ignored_id', $ignoredId)
                             ->first();

        if (!$ignore) {
            return response()->json(['error' => 'Player not found in ignore list.'], 404);
        }

        $ignore->delete();

        return response()->json(['message' => 'Player removed from ignore list successfully.']);
    }
}
