<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $padelSession->location }}
            </h2>
            <div class="flex space-x-2">
                @if(!$padelSession->participants()->where('user_id', auth()->id())->exists())
                    <form method="POST" action="{{ route('padel-sessions.join', $padelSession) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            Join Session
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('padel-sessions.leave', $padelSession) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700"
                                onclick="return confirm('Are you sure you want to leave this session?')">
                            Leave Session
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Session Details -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Session Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Date & Time</p>
                            <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ $padelSession->start_time->toEuropeanDateTime() }} - {{ $padelSession->end_time->toEuropeanTime() }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                            <x-statusBadge :status="$padelSession->status" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Players</p>
                            <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ $padelSession->participants()->count() }}/4
                            </p>
                        </div>
                    </div>
                    @if($padelSession->notes)
                        <div class="mt-6">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notes</p>
                            <p class="text-gray-900 dark:text-gray-100">{{ $padelSession->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Participants -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Participants</h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $padelSession->participants()->count() }}/4
                            </span>
                        </div>
                        
                        @if($padelSession->status === 'pending')
                            <div class="space-y-3">
                                @foreach($padelSession->invitations as $invitation)
                                <div class="flex justify-between items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $invitation->user->name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Status: <x-statusBadge :status="$invitation->status" />
                                        </p>
                                    </div>
                                    @if($invitation->responded_at)
                                        <span class="text-xs text-gray-500 dark:text-gray-400" title="{{ $invitation->responded_at->format('d.m.Y H:i:s') }}">
                                            Confirmed {{ $invitation->responded_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400" title="{{ $invitation->created_at->format('d.m.Y H:i:s') }}">
                                            Invited {{ $invitation->created_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($padelSession->participants as $participant)
                                    <div class="flex justify-between items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $participant->user->name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Status: <span class="capitalize">{{ $participant->status }}</span>
                                            </p>
                                        </div>
                                        @if($participant->confirmed_at)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                Confirmed {{ $participant->confirmed_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Matches -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Matches</h3>
                            @if($padelSession->participants()->count() >= 4)
                                <a href="{{ route('padel-sessions.padel-matches.create', $padelSession) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Add Match
                                </a>
                            @endif
                        </div>
                        @if($padelSession->matches->count() > 0)
                            <div class="space-y-3">
                                @foreach($padelSession->matches as $match)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                                Match #{{ $match->match_number }}
                                            </h4>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                @if($match->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @elseif($match->status === 'confirmed') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                {{ ucfirst($match->status) }}
                                            </span>
                                        </div>
                                        @if($match->isCompleted())
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                                Score: {{ $match->getScoreString() }}
                                            </p>
                                        @endif
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <p class="text-gray-500 dark:text-gray-400">Team A</p>
                                                <div class="space-y-1">
                                                    @foreach($match->matchPlayers->where('team', 'A') as $player)
                                                        <p class="text-gray-900 dark:text-gray-100">{{ $player->user->name }}</p>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-gray-500 dark:text-gray-400">Team B</p>
                                                <div class="space-y-1">
                                                    @foreach($match->matchPlayers->where('team', 'B') as $player)
                                                        <p class="text-gray-900 dark:text-gray-100">{{ $player->user->name }}</p>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex space-x-2">
                                            <a href="{{ route('padel-sessions.padel-matches.show', [$padelSession, $match]) }}" 
                                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm">
                                                View Details
                                            </a>
                                            @if($padelSession->created_by === auth()->id())
                                                <a href="{{ route('padel-sessions.padel-matches.edit', [$padelSession, $match]) }}" 
                                                   class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm">
                                                    Edit
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400">No matches scheduled yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 