<div class="expandable-section bg-white dark:bg-gray-800 shadow sm:rounded-lg">
    <div class="section-header cursor-pointer p-4 sm:p-8 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Matchmaking Preferences') }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Set your preferred playing frequency and session preferences.') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <svg class="expand-icon w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="section-content overflow-hidden transition-all duration-300 ease-in-out" style="max-height: 0px;">
        <div class="p-4 sm:p-8">
            <form method="POST" action="{{ route('profile.matchmaking.update') }}" class="ajax-form space-y-6">
                @csrf
                @method('PATCH')

                <!-- Weekly Frequency -->
                <div>
                    <x-input-label for="preferred_frequency_per_week" :value="__('Preferred Sessions per Week')" class="text-sm font-medium" />
                    <div class="mt-2">
                        <x-frequency-selector 
                            name="preferred_frequency_per_week" 
                            :value="$user->preferred_frequency_per_week" 
                            :min="1" 
                            :max="7" 
                            :step="1"
                        />
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('How many padel sessions would you like to play per week?') }}
                    </p>
                    <x-input-error class="mt-2" :messages="$errors->get('preferred_frequency_per_week')" />
                </div>

                <!-- Monthly Frequency -->
                <div>
                    <x-input-label for="preferred_frequency_per_month" :value="__('Preferred Sessions per Month')" class="text-sm font-medium" />
                    <div class="mt-2">
                        <x-frequency-selector 
                            name="preferred_frequency_per_month" 
                            :value="$user->preferred_frequency_per_month" 
                            :min="1" 
                            :max="31" 
                            :step="1"
                        />
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('How many padel sessions would you like to play per month?') }}
                    </p>
                    <x-input-error class="mt-2" :messages="$errors->get('preferred_frequency_per_month')" />
                </div>

                <!-- Session Length Preferences -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="min_session_length_hours" :value="__('Minimum Session Length (hours)')" class="text-sm font-medium" />
                        <div class="mt-2">
                                                    <x-frequency-selector 
                            name="min_session_length_hours" 
                            :value="$user->min_session_length_hours" 
                            :min="1" 
                            :max="6" 
                            :step="1"
                        />
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Shortest session you\'re willing to play.') }}
                        </p>
                        <x-input-error class="mt-2" :messages="$errors->get('min_session_length_hours')" />
                    </div>

                    <div>
                        <x-input-label for="max_session_length_hours" :value="__('Maximum Session Length (hours)')" class="text-sm font-medium" />
                        <div class="mt-2">
                                                    <x-frequency-selector 
                            name="max_session_length_hours" 
                            :value="$user->max_session_length_hours" 
                            :min="1" 
                            :max="8" 
                            :step="1"
                        />
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Longest session you\'re willing to play.') }}
                        </p>
                        <x-input-error class="mt-2" :messages="$errors->get('max_session_length_hours')" />
                    </div>
                </div>

                <!-- Matchmaking Information -->
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-green-800 dark:text-green-200">
                                {{ __('Matchmaking Algorithm') }}
                            </h4>
                            <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                                {{ __('These preferences help our system match you with players who have similar availability and preferences. We prioritize players who haven\'t played recently.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <x-primary-button type="submit">
                        {{ __('Save Matchmaking Preferences') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
