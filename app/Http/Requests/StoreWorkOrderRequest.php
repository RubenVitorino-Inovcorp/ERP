<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entity_id' => ['required', 'exists:entities,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'string', 'in:baixa,normal,alta,urgente'],
            'status' => ['required', 'string', 'in:pendente,em_curso,concluida,cancelada'],
            'expected_duration' => ['nullable', 'integer', 'min:0'],
            'actual_duration' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'technicians' => ['nullable', 'array'],
            'technicians.*' => ['exists:users,id'],
            'create_calendar_event' => ['nullable', 'boolean'],
            'date' => ['required_if:create_calendar_event,true,1', 'nullable', 'date'],
            'time' => ['required_if:create_calendar_event,true,1', 'nullable', 'string'],
            'calendar_type_id' => ['required_if:create_calendar_event,true,1', 'nullable', 'exists:calendar_types,id'],
            'calendar_action_id' => ['required_if:create_calendar_event,true,1', 'nullable', 'exists:calendar_actions,id'],
        ];
    }
}
