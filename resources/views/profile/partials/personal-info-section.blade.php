<div class="expandable-section bg-white dark:bg-gray-800 shadow sm:rounded-lg">
    <div class="section-header cursor-pointer p-4 sm:p-8 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Personal Information') }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Update your personal details and privacy settings.') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <svg class="expand-icon w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="section-content overflow-hidden transition-all duration-300 ease-in-out" style="max-height: 0px;">
        <div class="p-4 sm:p-8">
            <form method="POST" action="{{ route('profile.personal-info.update') }}" class="ajax-form space-y-6">
                @csrf
                @method('PATCH')

                <!-- Name Field -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <x-input-label for="name" :value="__('Name')" class="text-sm font-medium" />
                    </div>
                    <x-text-input 
                        id="name" 
                        name="name" 
                        type="text" 
                        class="mt-1 block w-full" 
                        :value="old('name', $user->name)" 
                        required 
                        autocomplete="name" 
                    />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <!-- Phone Field -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <x-input-label for="phone" :value="__('Phone Number')" class="text-sm font-medium" />
                        <x-privacy-toggle name="phone_visible" :value="$user->phone_visible" />
                    </div>
                    <x-text-input 
                        id="phone" 
                        name="phone" 
                        type="tel" 
                        class="mt-1 block w-full" 
                        :value="old('phone', $user->phone)" 
                        autocomplete="tel" 
                        placeholder="+1 (555) 123-4567"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Optional. Used for matchmaking coordination.') }}
                    </p>
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>

                <!-- Email Field -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <x-input-label for="email" :value="__('Email Address')" class="text-sm font-medium" />
                        <x-privacy-toggle name="email_visible" :value="$user->email_visible" />
                    </div>
                    <x-text-input 
                        id="email" 
                        name="email" 
                        type="email" 
                        class="mt-1 block w-full" 
                        :value="old('email', $user->email)" 
                        required 
                        autocomplete="username" 
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Used for account login and notifications.') }}
                    </p>
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="mt-2">
                            <p class="text-sm text-gray-800 dark:text-gray-200">
                                {{ __('Your email address is unverified.') }}
                                <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                    {{ __('Click here to re-send the verification email.') }}
                                </button>
                            </p>
                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Privacy Information -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                {{ __('Privacy Settings') }}
                            </h4>
                            <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                {{ __('Control which information is visible to other players.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <x-primary-button type="submit">
                        {{ __('Save Personal Information') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Email verification form (hidden) -->
<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>
