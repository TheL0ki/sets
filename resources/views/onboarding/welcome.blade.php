<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Welcome to SETS!') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                            Welcome to SETS!
                        </h2>
                        <p class="text-lg text-gray-600 dark:text-gray-400">
                            Smart Engine for Tennis Scheduling
                        </p>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Email Verified</h3>
                                <p class="text-gray-600 dark:text-gray-400">Your email has been successfully verified. You're all set to start using SETS!</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Next Steps</h3>
                                <p class="text-gray-600 dark:text-gray-400">Complete your profile and start scheduling padel games with other players.</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Smart Matchmaking</h3>
                                <p class="text-gray-600 dark:text-gray-400">Our system will automatically match you with players based on your availability and preferences.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                        <form method="POST" action="{{ route('onboarding.complete') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Complete Profile
                            </button>
                        </form>
                        <form method="POST" action="{{ route('onboarding.skip') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Skip for Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
