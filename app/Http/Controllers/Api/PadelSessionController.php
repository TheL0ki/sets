<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PadelSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PadelSessionController extends Controller
{
    /**
     * Update the location of a padel session.
     */
    public function updateLocation(Request $request, PadelSession $padelSession): JsonResponse
    {
        $user = $request->user();

        // Check if user is a participant
        if (!$padelSession->isParticipant($user)) {
            return response()->json([
                'message' => 'Only participants can change the session location.'
            ], 403);
        }

        try {
            // Validate the request
            $validated = $request->validate([
                'location' => 'required|string|max:255',
            ]);

            // Update the session location
            $padelSession->update([
                'location' => $validated['location'],
            ]);

            return response()->json([
                'message' => 'Session location updated successfully.',
                'session' => [
                    'id' => $padelSession->id,
                    'location' => $padelSession->location,
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Mark a padel session as completed.
     */
    public function markAsCompleted(Request $request, PadelSession $padelSession): JsonResponse
    {
        $user = $request->user();

        // Check if user is a participant
        if (!$padelSession->isParticipant($user)) {
            return response()->json([
                'message' => 'Only participants can mark the session as completed.'
            ], 403);
        }

        // Check if session is in confirmed status
        if ($padelSession->status !== PadelSession::STATUS_CONFIRMED) {
            return response()->json([
                'message' => 'Only confirmed sessions can be marked as completed.'
            ], 400);
        }

        // Update the session status to completed
        $padelSession->update([
            'status' => PadelSession::STATUS_COMPLETED,
        ]);

        return response()->json([
            'message' => 'Session marked as completed successfully.',
            'session' => [
                'id' => $padelSession->id,
                'status' => $padelSession->status,
            ]
        ], 200);
    }
}
