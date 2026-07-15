<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
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
            'entity_id' => 'required|exists:entities,id',
            'status' => 'required|in:rascunho,fechado',
            'lines' => 'required|array|min:1',
            'lines.*.article_id' => 'required|exists:articles,id',
            'lines.*.supplier_id' => 'nullable|exists:entities,id',
            'lines.*.cost_price' => 'nullable|numeric|min:0',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.vat_rate_id' => 'required|exists:vat_rates,id',
        ];
    }
}
