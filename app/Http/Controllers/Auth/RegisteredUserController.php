<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegistrationRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            // Set default values for new users
            'preferred_frequency_per_week' => 2,
            'preferred_frequency_per_month' => 8,
            'min_session_length_hours' => 1,
            'max_session_length_hours' => 2,
            'phone_visible' => false,
            'email_visible' => false,
            'email_notifications_enabled' => true,
            'session_invitation_notifications' => true,
            'session_confirmation_notifications' => true,
            'session_reminder_notifications' => true,
            'session_cancellation_notifications' => true,
            'onboarding_completed' => false,
        ]);

        event(new Registered($user));

        // Log the user in so they can access the verification notice
        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
