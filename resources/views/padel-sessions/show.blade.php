<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $padelSession->start_time->toEuropeanDateTime() }} - {{ $padelSession->end_time->toEuropeanTime() }}
            </h2>
            <div class="flex space-x-2">
                @if($padelSession->isParticipant(auth()->user()) && $padelSession->start_time->isFuture())
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
                @if($padelSession->status === 'confirmed' && $padelSession->isParticipant(auth()->user()) && $padelSession->end_time->isPast())
                    <form method="POST" action="{{ route('padel-sessions.complete', $padelSession) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
                                onclick="return confirm('Are you sure you want to mark this session as completed?')">
                            Mark Completed
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
                            <p class="text-sm text-gray-500 dark:text-gray-400">Location</p>
                            <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ $padelSession->location }} 
                                @if($padelSession->isParticipant(auth()->user()))
                                    <span
                                            onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'change-location-modal' }))"
                                            class="cursor-pointer text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors duration-200">
                                            ✏️
                                    </span>
                            @endif
                            </p>
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
                                            <span class="text-xs text-gray-500 dark:text-gray-400" title="{{ $participant->confirmed_at->format('d.m.Y H:i:s') }}">
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
                            @if($padelSession->participants()->count() >= 4 && $padelSession->participants()->where('user_id', auth()->id())->exists())
                                <a href="{{ route('padel-sessions.padel-matches.create', $padelSession) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Add Match
                                </a>
                            @endif
                        </div>
                        @if($padelSession->matches->count() > 0)
                            <div class="space-y-4">
                                @foreach($padelSession->matches as $match)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-3">
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                                Match #{{ $match->match_number }}
                                            </h4>
                                            <a href="{{ route('padel-sessions.padel-matches.edit', [$padelSession, $match]) }}" 
                                               class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors duration-200"
                                               title="Edit Match">
                                                ✏️
                                            </a>
                                        </div>
                                        <table class="w-full">
                                            <tr>
                                                <td class="w-1/3 align-top">
                                                    <div class="text-sm">
                                                        <p class="text-gray-500 dark:text-gray-400 font-medium mb-2 text-left">Team A</p>
                                                    </div>
                                                </td>
                                                <td class="w-1/3 align-top text-center">
                                                    <div class="text-sm">
                                                        <p class="text-gray-500 dark:text-gray-400 font-medium mb-2 ">Score</p>
                                                    </div>
                                                </td>
                                                <td class="w-1/3 align-top">
                                                    <div class="text-sm">
                                                        <p class="text-gray-500 dark:text-gray-400 font-medium mb-2 text-right">Team B</p>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="w-1/3 align-top">
                                                    <div class="space-y-1">
                                                        @foreach($match->matchPlayers->where('team', 'A') as $player)
                                                            <p class="text-gray-900 dark:text-gray-100 text-left">{{ $player->user->name }}</p>
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
                                                            <p class="text-gray-900 dark:text-gray-100 text-right">{{ $player->user->name }}</p>
                                                        @endforeach
                                                    </div> 
                                                </td>
                                            </tr>
                                        </table>
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

    <!-- Change Location Modal -->
    <x-modal name="change-location-modal" :show="false" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Change Session Location
            </h2>
            
            <form method="POST" action="{{ route('padel-sessions.update-location', $padelSession) }}">
                @csrf
                @method('PATCH')
                
                <div class="mb-4">
                    <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        New Location
                    </label>
                    <input type="text" 
                           id="location" 
                           name="location" 
                           value="{{ $padelSession->location }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100"
                           required>
                    @error('location')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'change-location-modal' }))"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Location
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout> 