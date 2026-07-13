<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContactFunctionRequest extends FormRequest
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
        $functionId = is_object($this->route('contact_function')) ? $this->route('contact_function')->id : $this->route('contact_function');

        return [
            'name' => 'required|string|max:255|unique:contact_functions,name,' . $functionId,
        ];
    }
}
