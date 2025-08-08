<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Matchmaking Statistics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- User Preferences -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Your Preferences</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-700 dark:text-gray-300">Weekly Goal</h4>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['weekly_goal'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">sessions per week</p>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-700 dark:text-gray-300">Monthly Goal</h4>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['monthly_goal'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">sessions per month</p>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-700 dark:text-gray-300">Session Length</h4>
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {{ auth()->user()->min_session_length_hours }}-{{ auth()->user()->max_session_length_hours }}h
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">preferred range</p>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-700 dark:text-gray-300">Recent Matches</h4>
                            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['recent_matches'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">last 30 days</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Statistics -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Session Statistics</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_sessions'] }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Sessions</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['confirmed_sessions'] }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Confirmed Sessions</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending_invitations'] }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Pending Invitations</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Length Explanation -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">How Session Length Works</h3>
                    
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="mb-4">
                            The matchmaking algorithm optimizes session length based on all participants' preferences:
                        </p>
                        
                        <ul class="list-disc pl-6 space-y-2">
                            <li><strong>Minimum Length:</strong> The algorithm finds the highest minimum preference among all players</li>
                            <li><strong>Maximum Length:</strong> The algorithm uses the lowest maximum preference to ensure everyone can participate</li>
                            <li><strong>Optimal Length:</strong> Sessions are scheduled at the longest possible duration that satisfies all players</li>
                        </ul>
                        
                        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Example:</h4>
                            <p class="text-blue-700 dark:text-blue-300">
                                If Player A prefers 1-3 hours, Player B prefers 1-4 hours, and Player C prefers 2-4 hours, 
                                the algorithm will schedule a <strong>3-hour session</strong> (the lowest maximum that satisfies everyone's minimum).
                                If it can't find enough players for a 3-hour session, it will try to schedule a <strong>2-hour session</strong>, as this is the next longest session that satisfies everyone's minimum.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
