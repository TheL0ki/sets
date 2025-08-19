<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationPreferencesUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email_notifications_enabled' => ['required', 'boolean'],
            'session_invitation_notifications' => ['required', 'boolean'],
            'session_confirmation_notifications' => ['required', 'boolean'],
            'session_reminder_notifications' => ['required', 'boolean'],
            'session_cancellation_notifications' => ['required', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email_notifications_enabled' => $this->has('email_notifications_enabled'),
            'session_invitation_notifications' => $this->has('session_invitation_notifications'),
            'session_confirmation_notifications' => $this->has('session_confirmation_notifications'),
            'session_reminder_notifications' => $this->has('session_reminder_notifications'),
            'session_cancellation_notifications' => $this->has('session_cancellation_notifications'),
        ]);
    }
}
