<div class="expandable-section bg-white dark:bg-gray-800 shadow sm:rounded-lg">
    <div class="section-header cursor-pointer p-4 sm:p-8 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.19 4.19A2 2 0 006.03 3h11.94c.7 0 1.35.37 1.7.97L20.5 8H4.5L4.19 4.19zM4 10h16v8a2 2 0 01-2 2H6a2 2 0 01-2-2v-8z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Notification Preferences') }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Manage your email notification settings.') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->email_notifications_enabled ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                    {{ $user->email_notifications_enabled ? __('Enabled') : __('Disabled') }}
                </span>
                <svg class="expand-icon w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="section-content overflow-hidden transition-all duration-300 ease-in-out" style="max-height: 0px;">
        <div class="p-4 sm:p-8">
            <form method="POST" action="{{ route('profile.notifications.update') }}" class="ajax-form space-y-6">
                @csrf
                @method('PATCH')

                <!-- Master Email Notifications Toggle -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Email Notifications') }}
                        </h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Enable or disable all email notifications') }}
                        </p>
                    </div>
                    <x-notification-toggle 
                        name="email_notifications_enabled" 
                        :value="$user->email_notifications_enabled" 
                    />
                </div>

                <hr class="border-gray-200 dark:border-gray-700">

                <!-- Session Invitation Notifications -->
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Session Invitations') }}
                        </h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Receive emails when you\'re invited to padel sessions') }}
                        </p>
                    </div>
                    <x-notification-toggle 
                        name="session_invitation_notifications" 
                        :value="$user->session_invitation_notifications" 
                        :disabled="!$user->email_notifications_enabled"
                    />
                </div>

                <!-- Session Confirmation Notifications -->
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Session Confirmations') }}
                        </h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Receive emails when sessions are confirmed') }}
                        </p>
                    </div>
                    <x-notification-toggle 
                        name="session_confirmation_notifications" 
                        :value="$user->session_confirmation_notifications" 
                        :disabled="!$user->email_notifications_enabled"
                    />
                </div>

                <!-- Session Reminder Notifications -->
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Session Reminders') }}
                        </h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Receive reminder emails 24 hours before sessions') }}
                        </p>
                    </div>
                    <x-notification-toggle 
                        name="session_reminder_notifications" 
                        :value="$user->session_reminder_notifications" 
                        :disabled="!$user->email_notifications_enabled"
                    />
                </div>

                <!-- Session Cancellation Notifications -->
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Session Cancellations') }}
                        </h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Receive emails when sessions are cancelled') }}
                        </p>
                    </div>
                    <x-notification-toggle 
                        name="session_cancellation_notifications" 
                        :value="$user->session_cancellation_notifications" 
                        :disabled="!$user->email_notifications_enabled"
                    />
                </div>

                <!-- Notification Information -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                {{ __('Notification Settings') }}
                            </h4>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                {{ __('When email notifications are disabled, all specific notification types will be automatically disabled. You can still view all updates in the app.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <x-primary-button type="submit">
                        {{ __('Save Notification Settings') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Handle the main email notifications toggle
    document.addEventListener('DOMContentLoaded', function() {
        const masterToggle = document.querySelector('input[name="email_notifications_enabled"]');
        if (masterToggle) {
            masterToggle.addEventListener('change', function() {
                const specificToggles = document.querySelectorAll('input[name^="session_"]');
                specificToggles.forEach(toggle => {
                    toggle.disabled = !this.checked;
                    if (!this.checked) {
                        toggle.checked = false;
                    }
                });
            });
        }
    });
</script>
