<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('SETS Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sessions</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_sessions'] }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Matches</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_matches'] }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Sessions (30 days)</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['recent_sessions'] }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Matches (30 days)</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['recent_matches'] }}</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Upcoming Sessions -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Upcoming Sessions</h3>
                        @if($upcomingSessions->count() > 0)
                            <div class="space-y-4">
                                @foreach($upcomingSessions as $session)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $session->location }}</h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $session->start_time->toEuropeanDateTime() }} - {{ $session->end_time->toEuropeanTime() }}
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    Status: <span class="capitalize">{{ $session->status }}</span>
                                                </p>
                                            </div>
                                            <a href="{{ route('padel-sessions.show', $session) }}" 
                                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm">
                                                View
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400">No upcoming sessions.</p>
                        @endif

                    </div>
                </div>

                <!-- Recent Matches -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Matches</h3>
                        @if($recentMatches->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentMatches as $match)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-3">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                            <a href="{{ route('padel-sessions.show', $match->session) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                {{ $match->session->location }} - {{ $match->session->start_time->toEuropeanDate() }}
                                            </a>
                                        </h4>
                                    </div>
                                    <table class="w-full">
                                        <tr>
                                            <td class="w-1/3 align-top">
                                                <div class="text-xs">
                                                    <p class="text-gray-500 dark:text-gray-400 font-medium mb-2 text-left">Team A</p>
                                                </div>
                                            </td>
                                            <td class="w-1/3 align-top text-center">
                                                <div class="text-xs">
                                                    <p class="text-gray-500 dark:text-gray-400 font-medium mb-2 ">Score</p>
                                                </div>
                                            </td>
                                            <td class="w-1/3 align-top">
                                                <div class="text-xs">
                                                    <p class="text-gray-500 dark:text-gray-400 font-medium mb-2 text-right">Team B</p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="w-1/3 align-top">
                                                <div class="space-y-1">
                                                    @foreach($match->matchPlayers->where('team', 'A') as $player)
                                                        <p class="text-sm text-gray-900 dark:text-gray-100 text-left">{{ $player->user->name }}</p>
                                                    @endforeach
                                                </div> 
                                            </td>
                                            <td class="w-1/3 align-middle text-center">
                                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                    {{ $match->getScoreString() }}
                                                </p>
                                            </td>
                                            <td class="w-1/3 align-top">
                                                <div class="space-y-1">
                                                    @foreach($match->matchPlayers->where('team', 'B') as $player)
                                                        <p class="text-sm text-gray-900 dark:text-gray-100 text-right">{{ $player->user->name }}</p>
                                                    @endforeach
                                                </div> 
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400">No recent matches.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Pending Invitations -->
            @if($pendingInvitations->count() > 0)
                <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pending Invitations</h3>
                        <div class="space-y-4">
                            @foreach($pendingInvitations as $invitation)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $invitation->session->location }}
                                            </h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $invitation->session->start_time->toEuropeanDateTime() }} - {{ $invitation->session->end_time->toEuropeanTime() }}
                                            </p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <form method="POST" action="{{ route('session-invitations.accept', $invitation) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                                                    Accept
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('session-invitations.decline', $invitation) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                                    Decline
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
