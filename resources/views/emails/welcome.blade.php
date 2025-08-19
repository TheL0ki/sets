@component('mail::message')
# Welcome to SETS!

Hi {{ $user->name }},

Welcome to SETS (Smart Engine for Tennis Scheduling)! We're excited to have you join our padel community.

Before you can start scheduling games, please verify your email address by clicking the button below:

@component('mail::button', ['url' => $verificationUrl])
Verify Email Address
@endcomponent

If you didn't create an account, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
