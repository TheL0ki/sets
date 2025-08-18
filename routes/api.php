<?php

use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\PlayerIgnoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// SETS API Routes
Route::middleware('auth:sanctum')->group(function () {
    // Availability API routes
    Route::apiResource('availabilities', AvailabilityController::class);
    Route::get('/availabilities/overlapping', [AvailabilityController::class, 'overlapping'])
        ->name('api.availabilities.overlapping');

    // Padel Session API routes
    Route::patch('/padel-sessions/{padelSession}/location', [\App\Http\Controllers\Api\PadelSessionController::class, 'updateLocation'])
        ->name('api.padel-sessions.update-location');
    Route::patch('/padel-sessions/{padelSession}/complete', [\App\Http\Controllers\Api\PadelSessionController::class, 'markAsCompleted'])
        ->name('api.padel-sessions.complete');

    // Player Ignore API routes
    Route::get('/player-ignores', [PlayerIgnoreController::class, 'apiIndex'])->name('api.player-ignores.index');
    Route::post('/player-ignores', [PlayerIgnoreController::class, 'apiStore'])->name('api.player-ignores.store');
    Route::delete('/player-ignores/{ignoredId}', [PlayerIgnoreController::class, 'apiDestroy'])->name('api.player-ignores.destroy');
}); 