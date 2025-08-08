<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Availability;
use App\Models\PadelSession;
use App\Models\PlayerIgnore;
use App\Models\SessionInvitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MatchmakingService
{
    /**
     * Run the matchmaking algorithm to create sessions
     */
    public function runMatchmaking(): array
    {
        $results = [
            'sessions_created' => 0,
            'invitations_sent' => 0,
            'errors' => [],
        ];

        try {
            // Get all active users
            $activeUsers = User::where('is_active', true)->get();
            
            if ($activeUsers->count() < 4) {
                $this->report('Insufficient active players', [
                    'active_players' => $activeUsers->count(),
                    'minimum_required' => 4,
                ]);
                $results['errors'][] = 'Not enough active players (minimum 4 required)';
                return $results;
            }

            // Get overlapping availabilities for the next 4 weeks
            $overlappingSlots = $this->findOverlappingAvailabilities($activeUsers);
            
            if ($overlappingSlots->isEmpty()) {
                $this->report('No overlapping availability found in next 4 weeks');
                $results['errors'][] = 'No overlapping availability found for the next 4 weeks';
                return $results;
            }

            // Group players by their frequency preferences and recent activity
            $playerGroups = $this->groupPlayersByPriority($activeUsers);
            
            // Create sessions for each overlapping slot
            foreach ($overlappingSlots as $slot) {
                $this->report('Evaluating overlapping slot', [
                    'start_time' => $slot['start_time']->toDateTimeString(),
                    'end_time' => $slot['end_time']->toDateTimeString(),
                    'session_length_hours' => $slot['session_length_hours'],
                    'available_user_ids' => $slot['available_users']->pluck('id')->all(),
                ]);
                $sessionCreated = $this->createSessionForSlot($slot, $playerGroups);
                if ($sessionCreated) {
                    $this->report('Session created successfully', [
                        'start_time' => $slot['start_time']->toDateTimeString(),
                        'end_time' => $slot['end_time']->toDateTimeString(),
                    ]);
                    $results['sessions_created']++;
                    $results['invitations_sent'] += 4; // 4 invitations per session
                }
            }

        } catch (\Exception $e) {
            $this->report('Unhandled matchmaking exception', [
                'error' => $e->getMessage(),
            ]);
            $results['errors'][] = 'Matchmaking error: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Find overlapping availabilities for the next 4 weeks
     */
    private function findOverlappingAvailabilities(Collection $users): Collection
    {
        $startDate = now()->startOfWeek();
        $endDate = now()->addWeeks(4)->endOfWeek();

        // Get all availabilities for the date range
        $availabilities = Availability::where('is_available', true)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->with('user')
            ->get()
            ->groupBy(function ($availability) {
                return $availability->start_time->format('Y-m-d H:i');
            });

        $overlappingSlots = collect();

        foreach ($availabilities as $timeSlot => $slotAvailabilities) {
            if ($slotAvailabilities->count() >= 4) {
                $users = $slotAvailabilities->pluck('user');
                $sessionStart = Carbon::parse($timeSlot);
                
                // Try different session lengths, starting from optimal and working down
                $sessionLengths = $this->getPossibleSessionLengths($users);
                
                foreach ($sessionLengths as $sessionLength) {
                    $sessionEnd = $sessionStart->copy()->addHours($sessionLength);
                    
                    $allPlayersAvailable = $this->verifyPlayersAvailableForDuration(
                        $users, 
                        $sessionStart, 
                        $sessionEnd
                    );
                    
                    if ($allPlayersAvailable) {
                        $overlappingSlots->push([
                            'start_time' => $sessionStart,
                            'end_time' => $sessionEnd,
                            'available_users' => $users,
                            'user_count' => $slotAvailabilities->count(),
                            'session_length_hours' => $sessionLength,
                        ]);
                        
                        // Found a working session length, no need to try shorter ones
                        break;
                    }
                }
            }
        }

        return $overlappingSlots->sortBy('start_time');
    }

    /**
     * Calculate the optimal session length for a group of users
     * Returns the longest session length that all users can accommodate
     */
    private function calculateOptimalSessionLength(Collection $users): int
    {
        if ($users->isEmpty()) {
            return 0;
        }

        // Get the minimum and maximum session lengths for all users
        $minLengths = $users->pluck('min_session_length_hours');
        $maxLengths = $users->pluck('max_session_length_hours');

        // Find the highest minimum and lowest maximum
        $highestMin = $minLengths->max();
        $lowestMax = $maxLengths->min();

        // If the highest minimum is greater than the lowest maximum, no compatible session length exists
        if ($highestMin > $lowestMax) {
            return 0;
        }

        // Return the optimal length (prefer longer sessions when possible)
        return $lowestMax;
    }

    /**
     * Get all possible session lengths for a group of users, sorted in descending order
     * Returns an array of session lengths from optimal down to minimum
     */
    private function getPossibleSessionLengths(Collection $users): array
    {
        if ($users->isEmpty()) {
            return [];
        }

        // Get the minimum and maximum session lengths for all users
        $minLengths = $users->pluck('min_session_length_hours');
        $maxLengths = $users->pluck('max_session_length_hours');

        // Find the highest minimum and lowest maximum
        $highestMin = $minLengths->max();
        $lowestMax = $maxLengths->min();

        // If the highest minimum is greater than the lowest maximum, no compatible session length exists
        if ($highestMin > $lowestMax) {
            return [];
        }

        // Generate all possible session lengths from optimal down to minimum
        $sessionLengths = [];
        for ($length = $lowestMax; $length >= $highestMin; $length--) {
            $sessionLengths[] = $length;
        }

        return $sessionLengths;
    }

    /**
     * Verify that all players are available for the full session duration
     */
    private function verifyPlayersAvailableForDuration(Collection $users, Carbon $startTime, Carbon $endTime): bool
    {
        foreach ($users as $user) {
            // Check if user has consecutive 30-minute slots for the entire session duration
            $requiredSlots = [];
            $currentTime = $startTime->copy();
            
            // Generate all required 30-minute slots for the session
            while ($currentTime < $endTime) {
                $requiredSlots[] = $currentTime->copy();
                $currentTime->addMinutes(30);
            }
            
            // Check if user has availability for all required slots
            $userAvailabilities = Availability::where('user_id', $user->id)
                ->where('is_available', true)
                ->whereIn('start_time', $requiredSlots)
                ->count();
            
            // Check if all required slots are available
            $requiredSlotCount = count($requiredSlots);
            if ($userAvailabilities < $requiredSlotCount) {
                return false;
            }
            

        }

        return true;
    }

    /**
     * Verify availability with per-user diagnostics.
     * Returns an associative array with an 'ok' flag and a collection of unavailable users.
     *
     * @return array{ok: bool, unavailable_users: Collection}
     */
    private function verifyPlayersAvailableForDurationWithDetails(
        Collection $users,
        Carbon $startTime,
        Carbon $endTime
    ): array {
        $unavailableUsers = collect();

        foreach ($users as $user) {
            $requiredSlots = [];
            $currentTime = $startTime->copy();
            while ($currentTime < $endTime) {
                $requiredSlots[] = $currentTime->copy();
                $currentTime->addMinutes(30);
            }

            $availableCount = Availability::where('user_id', $user->id)
                ->where('is_available', true)
                ->whereIn('start_time', $requiredSlots)
                ->count();

            if ($availableCount < count($requiredSlots)) {
                $unavailableUsers->push($user);
            }
        }

        return [
            'ok' => $unavailableUsers->isEmpty(),
            'unavailable_users' => $unavailableUsers,
        ];
    }

    /**
     * Group players by priority based on frequency preferences and recent activity
     */
    private function groupPlayersByPriority(Collection $users): array
    {
        $playerGroups = [
            'high_priority' => collect(),
            'medium_priority' => collect(),
            'low_priority' => collect(),
        ];

        foreach ($users as $user) {
            $recentSessions = $user->getRecentSessionCount();
            $recentMatches = $user->getRecentMatchCount();
            
            // Calculate priority score
            $priorityScore = $this->calculatePriorityScore($user, $recentSessions, $recentMatches);
            
            if ($priorityScore >= 8) {
                $playerGroups['high_priority']->push($user);
            } elseif ($priorityScore >= 5) {
                $playerGroups['medium_priority']->push($user);
            } else {
                $playerGroups['low_priority']->push($user);
            }
        }

        return $playerGroups;
    }

    /**
     * Calculate priority score for a player
     */
    private function calculatePriorityScore(User $user, int $recentSessions, int $recentMatches): int
    {
        $score = 0;
        
        // Base score from frequency preferences
        $weeklyRatio = $recentSessions / max($user->preferred_frequency_per_week, 1);
        $monthlyRatio = $recentSessions / max($user->preferred_frequency_per_month, 1);
        
        // If player is under their preferred frequency, increase priority
        if ($weeklyRatio < 1) {
            $score += 3;
        }
        if ($monthlyRatio < 1) {
            $score += 2;
        }
        
        // If player hasn't played recently, increase priority
        if ($recentSessions === 0) {
            $score += 4;
        } elseif ($recentSessions <= 1) {
            $score += 2;
        }
        
        // If player has played too much recently, decrease priority
        if ($recentSessions > $user->preferred_frequency_per_week * 2) {
            $score -= 2;
        }
        
        return max(0, min(10, $score));
    }

    /**
     * Create a session for a specific time slot
     */
    private function createSessionForSlot(array $slot, array $playerGroups): bool
    {
        // Check if a session already exists for this time slot
        $existingSession = PadelSession::where('start_time', $slot['start_time'])
            ->where('end_time', $slot['end_time'])
            ->whereNot('status', PadelSession::STATUS_CANCELLED)
            ->first();

        if ($existingSession) {
            $this->report('Skipped: existing session already scheduled for slot', [
                'start_time' => $slot['start_time']->toDateTimeString(),
                'end_time' => $slot['end_time']->toDateTimeString(),
                'existing_session_id' => $existingSession->id,
            ]);
            return false; // Session already exists for this slot
        }

        // Check for overlapping sessions (any session that overlaps with the proposed time and is not cancelled)
        $overlappingSession = PadelSession::where(function ($query) use ($slot) {
            $query->where(function ($q) use ($slot) {
                // Session starts before our session ends AND ends after our session starts
                $q->where('start_time', '<', $slot['end_time'])
                  ->where('end_time', '>', $slot['start_time']);
            });
        })
        ->where('status', '!=', PadelSession::STATUS_CANCELLED)
        ->first();

        if ($overlappingSession) {
            $this->report('Skipped: overlapping session conflict', [
                'start_time' => $slot['start_time']->toDateTimeString(),
                'end_time' => $slot['end_time']->toDateTimeString(),
                'conflicting_session_id' => $overlappingSession->id,
                'conflicting_start' => $overlappingSession->start_time->toDateTimeString(),
                'conflicting_end' => $overlappingSession->end_time->toDateTimeString(),
            ]);
            return false; // Overlapping session exists
        }

        // Verify that all selected players are available for the full session duration
        $sessionEndTime = $slot['start_time']->copy()->addHours($slot['session_length_hours']);
        $availabilityCheck = $this->verifyPlayersAvailableForDurationWithDetails(
            $slot['available_users'],
            $slot['start_time'],
            $sessionEndTime
        );
        
        if ($availabilityCheck['ok'] === false) {
            $this->report('Skipped: availability verification failed for some players', [
                'start_time' => $slot['start_time']->toDateTimeString(),
                'end_time' => $sessionEndTime->toDateTimeString(),
                'unavailable_user_ids' => $availabilityCheck['unavailable_users']->pluck('id')->all(),
            ]);
            return false; // Not all players are available for the full session duration
        }

        // Select 4 players for this session (least played first, backfill around ignores)
        $selectedPlayers = $this->selectPlayersForSession($slot['available_users'], $playerGroups, $slot['start_time']);
        
        if ($selectedPlayers->count() !== 4) {
            $this->report('Skipped: could not assemble compatible group of 4 players', [
                'available_user_ids' => $slot['available_users']->pluck('id')->all(),
                'selected_ids' => $selectedPlayers->pluck('id')->all(),
                'selected_count' => $selectedPlayers->count(),
            ]);
            return false; // Couldn't select exactly 4 players
        }

        try {
            DB::transaction(function () use ($slot, $selectedPlayers) {
                // Create the session
                $session = PadelSession::create([
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['start_time']->copy()->addHours($slot['session_length_hours']),
                    'location' => 'TBD', // Default location
                    'status' => PadelSession::STATUS_PENDING,
                ]);

                // Send invitations to all selected players
                foreach ($selectedPlayers as $player) {
                    SessionInvitation::create([
                        'session_id' => $session->id,
                        'user_id' => $player->id,
                        'invited_by' => $selectedPlayers->first()->id,
                        'status' => SessionInvitation::STATUS_PENDING,
                    ]);
                }
            });

            return true;
        } catch (\Exception $e) {
            $this->report('Failed to create session (transaction error)', [
                'error' => $e->getMessage(),
                'start_time' => $slot['start_time']->toDateTimeString(),
                'end_time' => $slot['end_time']->toDateTimeString(),
            ]);
            return false;
        }
    }

    /**
     * Select 4 players for a session based on priority and availability
     */
    private function selectPlayersForSession(Collection $availableUsers, array $playerGroups, Carbon $slotStart): Collection
    {
        // Build a fairness-ordered candidate pool across priority groups
        $candidates = collect();

        // Helper to sort by fairness and append
        $appendSorted = function (Collection $group) use (&$candidates, $slotStart) {
            $sorted = $group
                ->unique('id')
                ->sortBy([ // least to most
                    fn (User $u) => $u->getRecentSessionCount(),
                    fn (User $u) => $u->id,
                ])
                ->values();
            $candidates = $candidates->merge($sorted);
        };

        $appendSorted($availableUsers->intersect($playerGroups['high_priority']));
        $appendSorted(
            $availableUsers->intersect($playerGroups['medium_priority'])
                ->diff($candidates)
        );
        $appendSorted(
            $availableUsers->intersect($playerGroups['low_priority'])
                ->diff($candidates)
        );

        // Optionally enforce frequency caps: exclude users who shouldn't be scheduled this week/month
        $candidates = $candidates->filter(function (User $user) use ($slotStart) {
            return $this->canUserBeScheduled($user, $slotStart);
        })->values();

        if ($candidates->count() < 4) {
            $this->report('Not enough candidates after fairness sorting and frequency checks', [
                'candidate_ids' => $candidates->pluck('id')->all(),
                'candidate_count' => $candidates->count(),
                'available_count' => $availableUsers->count(),
            ]);
            return collect();
        }

        // Take a pool (e.g., top 8) to allow backfilling around ignore conflicts
        $poolSize = max(8, 4);
        $pool = $candidates->take($poolSize);

        // Find a compatible group of 4 from the fairness-ordered pool
        $compatibleGroups = $this->findCompatibleGroups($pool->all(), 4);
        if (!empty($compatibleGroups)) {
            return collect($compatibleGroups[0]);
        }

        // Diagnostics for conflicts
        $conflicts = [];
        for ($i = 0; $i < $pool->count(); $i++) {
            for ($j = $i + 1; $j < $pool->count(); $j++) {
                $u1 = $pool[$i];
                $u2 = $pool[$j];
                if (PlayerIgnore::hasIgnoreRelationship($u1->id, $u2->id)) {
                    $conflicts[] = [$u1->id, $u2->id];
                }
            }
        }
        $this->report('Unable to form compatible group of 4 from fairness-ordered pool', [
            'pool_ids' => $pool->pluck('id')->all(),
            'conflicting_pairs' => $conflicts,
        ]);

        return collect();
    }

    /**
     * Filter out players who have ignore relationships with each other
     */
    private function filterCompatiblePlayers(Collection $players): Collection
    {
        if ($players->count() < 4) {
            return $players;
        }

        // Try to find a compatible group of 4 players (keep models, not arrays)
        $playerArray = $players->all();
        $compatibleGroups = $this->findCompatibleGroups($playerArray, 4);
        
        if (!empty($compatibleGroups)) {
            // Return the first compatible group
            return collect($compatibleGroups[0]);
        }
        
        // If still no compatible group, return empty (no valid matches possible)
        return collect();
    }

    /**
     * Find all compatible groups of given size from a list of players
     */
    private function findCompatibleGroups(array $players, int $groupSize): array
    {
        $compatibleGroups = [];
        $this->generateCombinations($players, $groupSize, 0, [], $compatibleGroups);
        return $compatibleGroups;
    }

    /**
     * Generate all combinations of players and check for compatibility
     */
    private function generateCombinations(array $players, int $groupSize, int $start, array $current, array &$result): void
    {
        if (count($current) === $groupSize) {
            if ($this->isCompatibleGroup($current)) {
                $result[] = $current;
            }
            return;
        }

        for ($i = $start; $i < count($players); $i++) {
            $current[] = $players[$i];
            $this->generateCombinations($players, $groupSize, $i + 1, $current, $result);
            array_pop($current);
        }
    }

    /**
     * Check if a group of players is compatible (no ignore relationships)
     */
    private function isCompatibleGroup(array $players): bool
    {
        for ($i = 0; $i < count($players); $i++) {
            for ($j = $i + 1; $j < count($players); $j++) {
                if (PlayerIgnore::hasIgnoreRelationship($players[$i]->id, $players[$j]->id)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Log to Laravel and echo to console if running via CLI for observability
     */
    private function report(string $message, array $context = []): void
    {
        Log::info('[Matchmaking] ' . $message, $context);

        if (app()->runningInConsole()) {
            $pairs = [];
            foreach ($context as $key => $value) {
                if ($value instanceof \Carbon\CarbonInterface) {
                    $pairs[] = $key . '=' . $value->toDateTimeString();
                } elseif ($value instanceof Collection) {
                    $pairs[] = $key . '=' . json_encode($value->toArray());
                } else {
                    $pairs[] = $key . '=' . (is_scalar($value) ? (string) $value : json_encode($value));
                }
            }
            $line = '[Matchmaking] ' . $message . (empty($pairs) ? '' : ' {' . implode(', ', $pairs) . '}');
            // phpcs:ignore
            fwrite(STDOUT, $line . PHP_EOL);
        }
    }

    /**
     * Get suggested sessions for a specific user
     */
    public function getSuggestedSessionsForUser(User $user): Collection
    {
        // Get overlapping availabilities that include this user
        $userAvailabilities = $user->availabilities()
            ->where('is_available', true)
            ->where('start_time', '>=', now())
            ->where('start_time', '<=', now()->addWeeks(4))
            ->get();

        $suggestedSessions = collect();

        foreach ($userAvailabilities as $availability) {
            $overlappingUsers = $this->findUsersAvailableAt($availability->start_time, $availability->end_time);
            
            if ($overlappingUsers->count() >= 4) {
                $suggestedSessions->push([
                    'start_time' => $availability->start_time,
                    'end_time' => $availability->end_time,
                    'available_players' => $overlappingUsers,
                    'player_count' => $overlappingUsers->count(),
                ]);
            }
        }

        return $suggestedSessions->sortBy('start_time');
    }

    /**
     * Find users available at a specific time
     */
    private function findUsersAvailableAt(Carbon $startTime, Carbon $endTime): Collection
    {
        return Availability::where('is_available', true)
            ->where('start_time', $startTime)
            ->where('end_time', $endTime)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter(function ($user) {
                return $user->is_active;
            });
    }

    /**
     * Check if a user can be scheduled based on their frequency preferences
     */
    public function canUserBeScheduled(User $user, Carbon $date): bool
    {
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        // Count sessions in current week
        $weeklySessions = $user->sessions()
            ->whereBetween('padel_sessions.start_time', [$weekStart, $weekEnd])
            ->whereIn('padel_sessions.status', [PadelSession::STATUS_CONFIRMED, PadelSession::STATUS_PENDING])
            ->count();

        // Count sessions in current month
        $monthlySessions = $user->sessions()
            ->whereBetween('padel_sessions.start_time', [$monthStart, $monthEnd])
            ->whereIn('padel_sessions.status', [PadelSession::STATUS_CONFIRMED, PadelSession::STATUS_PENDING])
            ->count();

        // Check if user is within their frequency limits
        $withinWeeklyLimit = $weeklySessions < $user->preferred_frequency_per_week;
        $withinMonthlyLimit = $monthlySessions < $user->preferred_frequency_per_month;

        return $withinWeeklyLimit && $withinMonthlyLimit;
    }
}

