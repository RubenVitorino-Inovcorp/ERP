<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVatRateRequest extends FormRequest
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
        $vatRateId = is_object($this->route('vat_rate')) ? $this->route('vat_rate')->id : $this->route('vat_rate');

        return [
            'name' => 'required|string|max:255|unique:vat_rates,name,'.$vatRateId,
            'value' => 'required|numeric|min:0|max:100',
        ];
    }
}
