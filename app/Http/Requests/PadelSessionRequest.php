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
        ];
    }
}
