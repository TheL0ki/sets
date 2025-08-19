<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\MatchmakingPreferencesUpdateRequest;
use App\Http\Requests\NotificationPreferencesUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Personal information updated successfully']);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

  

    public function updateMatchmakingPreferences(MatchmakingPreferencesUpdateRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->user()->update($request->validated());

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Matchmaking preferences updated successfully']);
        }

        return Redirect::route('profile.edit')->with('status', 'matchmaking-preferences-updated');
    }

    public function updateNotificationPreferences(NotificationPreferencesUpdateRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        // If email notifications are disabled, disable all specific notification types
        if (!$data['email_notifications_enabled']) {
            $data['session_invitation_notifications'] = false;
            $data['session_confirmation_notifications'] = false;
            $data['session_reminder_notifications'] = false;
            $data['session_cancellation_notifications'] = false;
        }

        $request->user()->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Notification preferences updated successfully']);
        }

        return Redirect::route('profile.edit')->with('status', 'notification-preferences-updated');
    }
}
