<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                My Availability
            </h2>
            <div class="hidden lg:flex space-x-2">
                <a href="{{ route('availabilities.index', ['week' => $weekStart->copy()->subWeek()->format('Y-m-d')]) }}" 
                   class="inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Previous Week
                </a>
                <a href="{{ route('availabilities.index', ['week' => $weekStart->copy()->addWeek()->format('Y-m-d')]) }}" 
                   class="inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Next Week
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Week Navigation -->
                    <div class="mb-6 text-center hidden lg:block">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Week of {{ $weekStart->toEuropeanDate() }} - {{ $weekStart->copy()->addDays(6)->toEuropeanDate() }}
                        </h3>
                    </div>

                    <!-- Calendar Grid -->
                    <form method="POST" action="{{ route('availabilities.store') }}" id="availability-form">
                        @csrf
                        <input type="hidden" name="week_start" value="{{ $weekStart->format('Y-m-d') }}">
                        
                        <!-- Desktop View (lg and above) -->
                        <div class="hidden lg:block w-full">
                            <table class="w-full border border-gray-200 dark:border-gray-700">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700">
                                        <th class="border border-gray-200 dark:border-gray-600 px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-16">
                                            Time
                                        </th>
                                        @foreach($weekDays as $day)
                                            <th class="border border-gray-200 dark:border-gray-600 px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-32">
                                                <div class="font-semibold">{{ $day['dayName'] }}</div>
                                                <div class="text-sm {{ $day['isToday'] ? 'text-blue-600 dark:text-blue-400 font-bold' : '' }}">
                                                    {{ $day['dayNumber'] }}
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($timeSlots as $timeSlot)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="border border-gray-200 dark:border-gray-600 px-2 py-2 text-sm font-medium text-gray-900 dark:text-gray-100 text-center">
                                                {{ $timeSlot['label'] }}
                                            </td>
                                            @foreach($weekDays as $day)
                                                @php
                                                    $slotKey = $day['date']->format('Y-m-d') . '-' . $timeSlot['start']->format('H-i');
                                                    $isAvailable = $existingAvailabilities->has($slotKey);
                                                @endphp
                                                <td class="border border-gray-200 dark:border-gray-600 px-1 py-1">
                                                    <label class="block w-full h-full cursor-pointer">
                                                        <input type="checkbox" 
                                                               name="availabilities[]" 
                                                               value="{{ $slotKey }}"
                                                               {{ $isAvailable ? 'checked' : '' }}
                                                               class="sr-only availability-checkbox"
                                                               data-day="{{ $day['dayName'] }}"
                                                               data-time="{{ $timeSlot['label'] }}">
                                                        <div class="w-full h-8 rounded transition-colors duration-200 cursor-pointer
                                                                    {{ $isAvailable 
                                                                        ? 'bg-green-500 hover:bg-green-600' 
                                                                        : 'bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500' }}">
                                                        </div>
                                                    </label>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile View (below lg) -->
                        <div class="lg:hidden">
                            <!-- Day Navigation for Mobile -->
                            <div class="flex justify-between items-center mb-4">
                                <button type="button" id="prev-day" class="px-3 py-2 bg-gray-600 text-white rounded-md text-sm">
                                    ← Previous Day
                                </button>
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="current-day-display">
                                    Loading...
                                </h4>
                                <button type="button" id="next-day" class="px-3 py-2 bg-gray-600 text-white rounded-md text-sm">
                                    Next Day →
                                </button>
                            </div>

                            <!-- Mobile Day Views (one for each day, initially hidden except current day) -->
                            @foreach($weekDays as $dayIndex => $day)
                                <div class="space-y-2 mobile-day-view hidden" data-day-index="{{ $dayIndex }}">
                                    @foreach($timeSlots as $timeSlot)
                                        @php
                                            $slotKey = $day['date']->format('Y-m-d') . '-' . $timeSlot['start']->format('H-i');
                                            $isAvailable = $existingAvailabilities->has($slotKey);
                                        @endphp
                                        <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $timeSlot['label'] }}
                                            </span>
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" 
                                                       name="mobile_availabilities[]" 
                                                       value="{{ $slotKey }}"
                                                       {{ $isAvailable ? 'checked' : '' }}
                                                       class="sr-only mobile-availability-checkbox"
                                                       data-day="{{ $day['dayName'] }}"
                                                       data-time="{{ $timeSlot['label'] }}"
                                                       data-day-index="{{ $dayIndex }}">
                                                <div class="w-12 h-8 rounded transition-colors duration-200 cursor-pointer
                                                            {{ $isAvailable 
                                                                ? 'bg-green-500 hover:bg-green-600' 
                                                                : 'bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500' }}">
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        <!-- Legend and Actions -->
                        <div class="mt-6 flex flex-col sm:flex-row justify-around items-start sm:items-center space-y-4 sm:space-y-0">
                            <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex items-center space-x-2">
                                    <div class="w-4 h-4 bg-gray-100 dark:bg-gray-600 rounded"></div>
                                    <span>Unavailable</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="w-4 h-4 bg-green-500 rounded"></div>
                                    <span>Available</span>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 w-full justify-end">
                                <button type="button" 
                                        onclick="selectAll()"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Select All
                                </button>
                                <button type="button" 
                                        onclick="clearAll()"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                    Clear All
                                </button>
                                <button type="submit" 
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                    Save Availability
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript is loaded via Vite in app.js --}}
</x-app-layout> 