<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'time' => ['required', 'string'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'entity_id' => ['nullable', 'exists:entities,id'],
            'calendar_type_id' => ['required', 'exists:calendar_types,id'],
            'calendar_action_id' => ['required', 'exists:calendar_actions,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:agendado,realizado,cancelado'],
            'partilha' => ['nullable', 'array'],
            'partilha.*' => ['exists:users,id'],
            'conhecimento' => ['nullable', 'array'],
            'conhecimento.*' => ['exists:users,id'],
        ];
    }
}
