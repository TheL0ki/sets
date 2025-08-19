<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MatchmakingPreferencesUpdateRequest extends FormRequest
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
            'preferred_frequency_per_week' => ['required', 'integer', 'min:1', 'max:7'],
            'preferred_frequency_per_month' => ['required', 'integer', 'min:1', 'max:31'],
            'min_session_length_hours' => ['required', 'integer', 'min:1', 'max:24'],
            'max_session_length_hours' => ['required', 'integer', 'min:1', 'max:24'],
        ];
    }
}
