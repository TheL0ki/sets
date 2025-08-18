<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit Match #{{ $padel_match->match_number }}
            </h2>
            <a href="{{ route('padel-sessions.show', $padel_session) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Back to Session
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('padel-sessions.padel-matches.update', [$padel_session, $padel_match]) }}" id="match-form">
                        @csrf
                        @method('PUT')
                        
                        <!-- Team Assignment Section -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Team Assignment</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Team A -->
                                <div class="space-y-4">
                                    <h4 class="text-md font-medium text-gray-700 dark:text-gray-300">Team A</h4>
                                    
                                    <div>
                                        <label for="team_a_players_0" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Player 1
                                        </label>
                                        <select name="team_a_players[]" id="team_a_players_0" 
                                                class="team-a-select block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                required>
                                            <option value="">Select Player</option>
                                            @foreach($participants as $participant)
                                                <option value="{{ $participant->user->id }}" 
                                                        data-participant-id="{{ $participant->user->id }}"
                                                        @if($padel_match->matchPlayers->where('team', 'A')->first() && $padel_match->matchPlayers->where('team', 'A')->first()->user_id == $participant->user->id) selected @endif>
                                                    {{ $participant->user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="team_a_players_1" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Player 2
                                        </label>
                                        <select name="team_a_players[]" id="team_a_players_1" 
                                                class="team-a-select block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                required>
                                            <option value="">Select Player</option>
                                            @foreach($participants as $participant)
                                                <option value="{{ $participant->user->id }}" 
                                                        data-participant-id="{{ $participant->user->id }}"
                                                        @if($padel_match->matchPlayers->where('team', 'A')->skip(1)->first() && $padel_match->matchPlayers->where('team', 'A')->skip(1)->first()->user_id == $participant->user->id) selected @endif>
                                                    {{ $participant->user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Team B -->
                                <div class="space-y-4">
                                    <h4 class="text-md font-medium text-gray-700 dark:text-gray-300">Team B</h4>
                                    
                                    <div>
                                        <label for="team_b_players_0" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Player 1
                                        </label>
                                        <select name="team_b_players[]" id="team_b_players_0" 
                                                class="team-b-select block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                required>
                                            <option value="">Select Player</option>
                                            @foreach($participants as $participant)
                                                <option value="{{ $participant->user->id }}" 
                                                        data-participant-id="{{ $participant->user->id }}"
                                                        @if($padel_match->matchPlayers->where('team', 'B')->first() && $padel_match->matchPlayers->where('team', 'B')->first()->user_id == $participant->user->id) selected @endif>
                                                    {{ $participant->user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="team_b_players_1" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Player 2
                                        </label>
                                        <select name="team_b_players[]" id="team_b_players_1" 
                                                class="team-b-select block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                required>
                                            <option value="">Select Player</option>
                                            @foreach($participants as $participant)
                                                <option value="{{ $participant->user->id }}" 
                                                        data-participant-id="{{ $participant->user->id }}"
                                                        @if($padel_match->matchPlayers->where('team', 'B')->skip(1)->first() && $padel_match->matchPlayers->where('team', 'B')->skip(1)->first()->user_id == $participant->user->id) selected @endif>
                                                    {{ $participant->user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            @error('team_a_players')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Score Section -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Match Score</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Team A Score -->
                                <div>
                                    <label for="team_a_score" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Team A Score
                                    </label>
                                    <input type="number" name="team_a_score" id="team_a_score" 
                                           min="0" max="20" value="{{ $padel_match->team_a_score }}"
                                           class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                           required>
                                    @error('team_a_score')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Team B Score -->
                                <div>
                                    <label for="team_b_score" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Team B Score
                                    </label>
                                    <input type="number" name="team_b_score" id="team_b_score" 
                                           min="0" max="20" value="{{ $padel_match->team_b_score }}"
                                           class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                           required>
                                    @error('team_b_score')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('padel-sessions.show', $padel_session) }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Update Match
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const teamASelects = document.querySelectorAll('.team-a-select');
            const teamBSelects = document.querySelectorAll('.team-b-select');
            const allSelects = [...teamASelects, ...teamBSelects];

            function updateDropdownOptions() {
                const selectedPlayers = new Set();
                
                // Collect all selected players
                allSelects.forEach(select => {
                    if (select.value) {
                        selectedPlayers.add(select.value);
                    }
                });

                // Update all dropdowns
                allSelects.forEach(select => {
                    const currentValue = select.value;
                    
                    // Reset options
                    Array.from(select.options).forEach(option => {
                        if (option.value === '') {
                            // Keep the "Select Player" option
                            option.disabled = false;
                        } else if (selectedPlayers.has(option.value) && option.value !== currentValue) {
                            // Disable options that are selected in other dropdowns
                            option.disabled = true;
                        } else {
                            // Enable options that are not selected elsewhere
                            option.disabled = false;
                        }
                    });
                });
            }

            // Add event listeners to all selects
            allSelects.forEach(select => {
                select.addEventListener('change', updateDropdownOptions);
            });

            // Initial update
            updateDropdownOptions();
        });
    </script>
</x-app-layout>
