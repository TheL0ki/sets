<div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
    <div class="max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Profile Overview') }}
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Manage your personal information, matchmaking preferences, and notification settings.') }}
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    {{ __('Active') }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Personal Info Summary -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Personal Information') }}
                    </h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $user->name }}
                </p>
                @if($user->email && $user->email_visible)
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $user->email }}
                    </p>
                @endif
                @if($user->phone && $user->phone_visible)
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $user->phone }}
                    </p>
                @endif
            </div>

            <!-- Matchmaking Summary -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Matchmaking Preferences') }}
                    </h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Weekly:') }} {{ $user->preferred_frequency_per_week }}x
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Monthly:') }} {{ $user->preferred_frequency_per_month }}x
                </p>
            </div>

            <!-- Notifications Summary -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Notifications') }}
                    </h4>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $user->email_notifications_enabled ? __('Enabled') : __('Disabled') }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $user->email_notifications_enabled ? __('Email notifications active') : __('Email notifications disabled') }}
                </p>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">
                {{ __('Recent Activity') }}
            </h4>
            <div class="space-y-2">
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400" title="{{ $user->updated_at->toEuropeanDateTime() }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ __('Last login:') }} {{ $user->updated_at->diffForHumans() }}
                </div>
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400" title="{{ $user->created_at->toEuropeanDateTime() }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ __('Account created:') }} {{ $user->created_at->format('M j, Y') }}
                </div>
            </div>
        </div>
    </div>
</div>
