<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding welcome page.
     */
    public function show(): View
    {
        return view('onboarding.welcome');
    }

    /**
     * Complete the onboarding process.
     */
    public function complete(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $user->update(['onboarding_completed' => true]);
        
        return redirect()->route('profile.edit');
    }

    public function skip(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $user->update(['onboarding_completed' => true]);

        return redirect()->route('dashboard');
    }
}
