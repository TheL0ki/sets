<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AvailabilityRequest;
use App\Models\Availability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * Display a listing of the user's availabilities.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $availabilities = $user->availabilities()
            ->orderBy('start_time')
            ->paginate(10);

        return response()->json([
            'data' => $availabilities->items(),
            'meta' => [
                'current_page' => $availabilities->currentPage(),
                'last_page' => $availabilities->lastPage(),
                'per_page' => $availabilities->perPage(),
                'total' => $availabilities->total(),
            ],
        ]);
    }

    /**
     * Store a newly created availability.
     */
    public function store(AvailabilityRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $availability = $user->availabilities()->create($request->validated());

        return response()->json([
            'message' => 'Availability created successfully.',
            'data' => $availability->load('user'),
        ], 201);
    }

    /**
     * Display the specified availability.
     */
    public function show(Request $request, Availability $availability): JsonResponse
    {
        // Ensure user can only view their own availability
        if ($availability->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $availability->load('user'),
        ]);
    }

    /**
     * Update the specified availability.
     */
    public function update(AvailabilityRequest $request, Availability $availability): JsonResponse
    {
        // Ensure user can only update their own availability
        if ($availability->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $availability->update($request->validated());

        return response()->json([
            'message' => 'Availability updated successfully.',
            'data' => $availability->load('user'),
        ]);
    }

    /**
     * Remove the specified availability.
     */
    public function destroy(Request $request, Availability $availability): JsonResponse
    {
        // Ensure user can only delete their own availability
        if ($availability->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $availability->delete();

        return response()->json([
            'message' => 'Availability deleted successfully.',
        ]);
    }

    /**
     * Get overlapping availabilities for matchmaking.
     */
    public function overlapping(Request $request): JsonResponse
    {
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        if (!$startTime || !$endTime) {
            return response()->json([
                'message' => 'Start time and end time are required.',
            ], 400);
        }

        $overlappingAvailabilities = Availability::with('user')
            ->overlapping($startTime, $endTime)
            ->available()
            ->get()
            ->groupBy('user_id');

        return response()->json([
            'data' => [
                'overlapping_availabilities' => $overlappingAvailabilities,
                'available_users' => $overlappingAvailabilities->keys()->count(),
            ],
        ]);
    }
}
