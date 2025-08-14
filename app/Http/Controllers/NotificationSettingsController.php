<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationSettingsController extends Controller
{
    /**
     * Show the notification settings page.
     */
    public function index(): View
    {
        $user = auth()->user();
        
        return view('notification-settings.index', compact('user'));
    }

    /**
     * Update notification settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'email_notifications_enabled' => 'boolean',
            'session_invitation_notifications' => 'boolean',
            'session_confirmation_notifications' => 'boolean',
            'session_reminder_notifications' => 'boolean',
            'session_cancellation_notifications' => 'boolean',
        ]);

        // If email notifications are disabled, disable all specific notification types
        if (!$validated['email_notifications_enabled']) {
            $validated['session_invitation_notifications'] = false;
            $validated['session_confirmation_notifications'] = false;
            $validated['session_reminder_notifications'] = false;
            $validated['session_cancellation_notifications'] = false;
        }

        $user->update($validated);

        return redirect()->route('notification-settings.index')
            ->with('success', 'Notification settings updated successfully.');
    }
}
