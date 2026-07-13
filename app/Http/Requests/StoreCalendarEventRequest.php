<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarEventRequest extends FormRequest
{
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
            'date' => ['required', 'date'],
            'time' => ['required', 'string'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'entity_id' => ['nullable', 'exists:entities,id'],
            'calendar_type_id' => ['nullable', 'exists:calendar_types,id'],
            'calendar_action_id' => ['nullable', 'exists:calendar_actions,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:agendado,realizado,cancelado'],
            'partilha' => ['nullable', 'array'],
            'partilha.*' => ['exists:users,id'],
            'conhecimento' => ['nullable', 'array'],
            'conhecimento.*' => ['exists:users,id'],
        ];
    }
}
