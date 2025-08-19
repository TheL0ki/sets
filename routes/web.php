<?php

use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailActionController;
use App\Http\Controllers\NotificationSettingsController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PadelMatchController;
use App\Http\Controllers\PadelSessionController;
use App\Http\Controllers\PlayerIgnoreController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Onboarding routes
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.welcome');
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
    Route::post('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Profile section routes
    Route::patch('/profile/personal-info', [ProfileController::class, 'update'])->name('profile.personal-info.update');
    Route::patch('/profile/matchmaking', [ProfileController::class, 'updateMatchmakingPreferences'])->name('profile.matchmaking.update');
    Route::patch('/profile/notifications', [ProfileController::class, 'updateNotificationPreferences'])->name('profile.notifications.update');

    // Availability routes
    Route::get('/availabilities', [AvailabilityController::class, 'index'])->name('availabilities.index');
    Route::post('/availabilities', [AvailabilityController::class, 'store'])->name('availabilities.store');
    Route::get('/availabilities/overlapping', [AvailabilityController::class, 'overlapping'])
        ->name('availabilities.overlapping');

    // Padel Session routes (algorithm-controlled)
    Route::get('/padel-sessions', [PadelSessionController::class, 'index'])->name('padel-sessions.index');
    Route::get('/padel-sessions/{padelSession}', [PadelSessionController::class, 'show'])->name('padel-sessions.show');
    Route::post('/padel-sessions/{padelSession}/join', [PadelSessionController::class, 'join'])
        ->name('padel-sessions.join');
    Route::delete('/padel-sessions/{padelSession}/leave', [PadelSessionController::class, 'leave'])
        ->name('padel-sessions.leave');
    Route::patch('/padel-sessions/{padelSession}/location', [PadelSessionController::class, 'updateLocation'])
        ->name('padel-sessions.update-location');
    Route::patch('/padel-sessions/{padelSession}/complete', [PadelSessionController::class, 'markAsCompleted'])
        ->name('padel-sessions.complete');

    // Session Invitation routes
    Route::post('/session-invitations/{invitation}/accept', [PadelSessionController::class, 'acceptInvitation'])
        ->name('session-invitations.accept');
    Route::post('/session-invitations/{invitation}/decline', [PadelSessionController::class, 'declineInvitation'])
        ->name('session-invitations.decline');

    // Padel Match routes (nested under sessions)
    Route::resource('padel-sessions.padel-matches', PadelMatchController::class);

    // Player Ignore routes
    Route::get('/player-ignores', [PlayerIgnoreController::class, 'index'])->name('player-ignores.index');
    Route::get('/player-ignores/create', [PlayerIgnoreController::class, 'create'])->name('player-ignores.create');
    Route::post('/player-ignores', [PlayerIgnoreController::class, 'store'])->name('player-ignores.store');
    Route::delete('/player-ignores/{ignoredId}', [PlayerIgnoreController::class, 'destroy'])->name('player-ignores.destroy');

    // Notification Settings routes
    Route::get('/notification-settings', [NotificationSettingsController::class, 'index'])->name('notification-settings.index');
    Route::patch('/notification-settings', [NotificationSettingsController::class, 'update'])->name('notification-settings.update');
});

// Email Action routes (signed URLs for email notifications)
Route::get('/session-invitation/accept/{session}/{invitation}', [EmailActionController::class, 'acceptInvitation'])
    ->name('session.invitation.accept')
    ->middleware('signed');

Route::get('/session-invitation/decline/{session}/{invitation}', [EmailActionController::class, 'declineInvitation'])
    ->name('session.invitation.decline')
    ->middleware('signed');

Route::get('/session-invitation/view/{session}', [EmailActionController::class, 'viewSession'])
    ->name('session.invitation.view')
    ->middleware('signed');

Route::get('/session-confirmed/view/{session}', [EmailActionController::class, 'viewConfirmedSession'])
    ->name('session.confirmed.view')
    ->middleware('signed');

Route::get('/session-reminder/view/{session}', [EmailActionController::class, 'viewReminderSession'])
    ->name('session.reminder.view')
    ->middleware('signed');

require __DIR__.'/auth.php';
