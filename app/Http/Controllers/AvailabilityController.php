<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AvailabilityRequest;
use App\Models\Availability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    /**
     * Display a calendar view of the user's availabilities.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get the week to display (default to current week)
        $weekStart = $request->get('week', now()->startOfWeek());
        if (is_string($weekStart)) {
            $weekStart = \Carbon\Carbon::parse($weekStart);
        }
        
        // Generate time slots from 07:00 to 22:00 in 30-minute intervals
        $timeSlots = [];
        $currentTime = \Carbon\Carbon::parse('07:00');
        $endTime = \Carbon\Carbon::parse('22:00');
        
        while ($currentTime < $endTime) {
            $timeSlots[] = [
                'start' => $currentTime->copy(),
                'end' => $currentTime->copy()->addMinutes(30),
                'label' => $currentTime->format('H:i')
            ];
            $currentTime->addMinutes(30);
        }
        
        // Generate week days
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $weekDays[] = [
                'date' => $day,
                'dayName' => $day->format('l'),
                'dayNumber' => $day->format('j'),
                'isToday' => $day->isToday()
            ];
        }
        
        // Get existing availabilities for the week
        $weekEnd = $weekStart->copy()->endOfWeek();
        $existingAvailabilities = $user->availabilities()
            ->whereBetween('start_time', [$weekStart, $weekEnd])
            ->get()
            ->keyBy(function ($availability) {
                return $availability->start_time->format('Y-m-d-H-i');
            });

        return view('availabilities.index', compact('timeSlots', 'weekDays', 'existingAvailabilities', 'weekStart'));
    }



    /**
     * Store multiple availabilities for the week.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        $request->validate([
            'week_start' => 'required|date',
            'availabilities' => 'array',
            'availabilities.*' => 'string|regex:/^\d{4}-\d{2}-\d{2}-\d{2}-\d{2}$/'
        ]);
        
        $weekStart = \Carbon\Carbon::parse($request->input('week_start'));
        $weekEnd = $weekStart->copy()->endOfWeek();
        
        // Delete existing availabilities for this week
        $user->availabilities()
            ->whereBetween('start_time', [$weekStart, $weekEnd])
            ->delete();
        
        // Create new availabilities
        $availabilities = $request->input('availabilities', []);
        $createdCount = 0;
        
        foreach ($availabilities as $timeSlot) {
            // Parse time slot format: YYYY-MM-DD-HH-MM
            $parts = explode('-', $timeSlot);
            if (count($parts) === 5) {
                $startTime = \Carbon\Carbon::create(
                    $parts[0], $parts[1], $parts[2], $parts[3], $parts[4]
                );
                $endTime = $startTime->copy()->addMinutes(30);
                
                $user->availabilities()->create([
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_available' => true
                ]);
                $createdCount++;
            }
        }
        
        return redirect()
            ->route('availabilities.index', ['week' => $weekStart->format('Y-m-d')])
            ->with('success', "{$createdCount} availability slots saved successfully.");
    }



    /**
     * Remove the specified availability.
     */
    public function destroy(Request $request, Availability $availability): RedirectResponse
    {
        // Ensure user can only delete their own availability
        if ($availability->user_id !== $request->user()->id) {
            abort(403);
        }

        $availability->delete();

        return redirect()
            ->route('availabilities.index')
            ->with('success', 'Availability deleted successfully.');
    }

    /**
     * Get overlapping availabilities for matchmaking.
     */
    public function overlapping(Request $request): \Illuminate\Http\JsonResponse
    {
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');

        $overlappingAvailabilities = Availability::with('user')
            ->overlapping($startTime, $endTime)
            ->available()
            ->get()
            ->groupBy('user_id');

        return response()->json([
            'overlapping_availabilities' => $overlappingAvailabilities,
            'available_users' => $overlappingAvailabilities->keys()->count(),
        ]);
    }
}
