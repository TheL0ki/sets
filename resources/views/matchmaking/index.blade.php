<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Matchmaking Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Admin Controls -->
            @if(auth()->user()->id === 1)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Admin Controls</h3>
                    <form action="{{ route('matchmaking.run') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            🏓 Run Matchmaking Algorithm
                        </button>
                    </form>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                        This will analyze all player availabilities and create sessions for the next 4 weeks.
                    </p>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('matchmaking.stats') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            📊 View Statistics
                        </a>
                        <a href="{{ route('availabilities.index') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            📅 Update Availability
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Recent Sessions</h3>
                    
                    @if($recentSessions->isEmpty())
                        <p class="text-gray-600 dark:text-gray-400">No recent sessions found.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($recentSessions as $session)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold">
                                                <a href="{{ route('padel-sessions.show', $session) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                                    Session on {{ $session->start_time->toEuropeanDate() }}
                                                </a>
                                            </h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $session->start_time->toEuropeanTime() }} - {{ $session->end_time->toEuropeanTime() }}
                                            </p>
                                        </div>
                                        <span class="px-2 py-1 text-xs font-medium rounded
                                            @if($session->status === 'confirmed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                            @elseif($session->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                            @endif">
                                            {{ ucfirst($session->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

