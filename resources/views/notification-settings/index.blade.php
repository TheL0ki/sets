<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Notification Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('notification-settings.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="space-y-6">
                            <!-- Email Notifications Toggle -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        Email Notifications
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Enable or disable all email notifications
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="email_notifications_enabled" 
                                           class="sr-only peer" 
                                           {{ $user->email_notifications_enabled ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <hr class="border-gray-200 dark:border-gray-700">

                            <!-- Session Invitation Notifications -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">
                                        Session Invitations
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Receive emails when you're invited to padel sessions
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="session_invitation_notifications" 
                                           class="sr-only peer" 
                                           {{ $user->session_invitation_notifications ? 'checked' : '' }}
                                           {{ !$user->email_notifications_enabled ? 'disabled' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 peer-disabled:opacity-50"></div>
                                </label>
                            </div>

                            <!-- Session Confirmation Notifications -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">
                                        Session Confirmations
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Receive emails when sessions are confirmed
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="session_confirmation_notifications" 
                                           class="sr-only peer" 
                                           {{ $user->session_confirmation_notifications ? 'checked' : '' }}
                                           {{ !$user->email_notifications_enabled ? 'disabled' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 peer-disabled:opacity-50"></div>
                                </label>
                            </div>

                            <!-- Session Reminder Notifications -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">
                                        Session Reminders
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Receive reminder emails 24 hours before sessions
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="session_reminder_notifications" 
                                           class="sr-only peer" 
                                           {{ $user->session_reminder_notifications ? 'checked' : '' }}
                                           {{ !$user->email_notifications_enabled ? 'disabled' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 peer-disabled:opacity-50"></div>
                                </label>
                            </div>

                            <!-- Session Cancellation Notifications -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">
                                        Session Cancellations
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Receive emails when sessions are cancelled
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="session_cancellation_notifications" 
                                           class="sr-only peer" 
                                           {{ $user->session_cancellation_notifications ? 'checked' : '' }}
                                           {{ !$user->email_notifications_enabled ? 'disabled' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 peer-disabled:opacity-50"></div>
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end">
                            <x-primary-button>
                                {{ __('Save Settings') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle the main email notifications toggle
        document.querySelector('input[name="email_notifications_enabled"]').addEventListener('change', function() {
            const specificToggles = document.querySelectorAll('input[name^="session_"]');
            specificToggles.forEach(toggle => {
                toggle.disabled = !this.checked;
                if (!this.checked) {
                    toggle.checked = false;
                }
            });
        });
    </script>
</x-app-layout>
