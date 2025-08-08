<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add Player to Ignore List') }}
            </h2>
            <a href="{{ route('player-ignores.index') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Ignore List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            Ignore a Player
                        </h3>
                        <p class="text-gray-600">
                            Players you ignore will not be paired with you in matches. This helps ensure you only play with compatible players.
                        </p>
                    </div>

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($availableUsers->count() > 0)
                        <form action="{{ route('player-ignores.store') }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="ignored_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Select Player to Ignore
                                </label>
                                <select name="ignored_id" id="ignored_id" 
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                        required>
                                    <option value="">Choose a player...</option>
                                    @foreach($availableUsers as $user)
                                        <option value="{{ $user->id }}" {{ old('ignored_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                    Reason (Optional)
                                </label>
                                <textarea name="reason" id="reason" rows="3"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                          placeholder="Why are you ignoring this player? (This is private and only visible to you)">{{ old('reason') }}</textarea>
                                <p class="mt-1 text-sm text-gray-500">
                                    This reason is only visible to you and helps you remember why you ignored this player.
                                </p>
                            </div>

                            <div class="flex items-center justify-end space-x-4">
                                <a href="{{ route('player-ignores.index') }}" 
                                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                    Cancel
                                </a>
                                <button type="submit" 
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    Add to Ignore List
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-500 text-lg mb-4">
                                No players available to ignore.
                            </div>
                            <p class="text-gray-400">
                                You have either ignored all active players or there are no other active players in the system.
                            </p>
                            <div class="mt-4">
                                <a href="{{ route('player-ignores.index') }}" 
                                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Back to Ignore List
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
