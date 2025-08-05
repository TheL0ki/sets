<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PadelSessionRequest extends FormRequest
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
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'location' => 'required|string|max:255',
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
            'notes' => 'nullable|string|max:1000',
            'max_players' => 'sometimes|integer|min:4|max:12',
            'invite_users' => 'sometimes|array',
            'invite_users.*' => 'exists:users,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_time.required' => 'Please select a start time.',
            'start_time.after' => 'Start time must be in the future.',
            'end_time.required' => 'Please select an end time.',
            'end_time.after' => 'End time must be after start time.',
            'location.required' => 'Please specify a location.',
            'location.max' => 'Location cannot exceed 255 characters.',
            'max_players.min' => 'Maximum players must be at least 4.',
            'max_players.max' => 'Maximum players cannot exceed 12.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'status' => $this->input('status', 'pending'),
            'max_players' => $this->input('max_players', 8),
        ]);
    }
}
