<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkOrderRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
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
        ];
    }
}
