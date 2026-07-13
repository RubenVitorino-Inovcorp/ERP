<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDigitalFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'document_category_id' => ['required', 'exists:document_categories,id'],
            'file' => ['required', 'file', 'max:102400'], // max 100MB
        ];
    }
}
