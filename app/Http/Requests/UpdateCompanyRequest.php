<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'regex:/^\d{4}-\d{3}$/'],
            'city' => ['required', 'string', 'max:255'],
            'nif' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (! self::isValidPtNif($value)) {
                        $fail('O NIF introduzido não é válido.');
                    }
                },
            ],
            'logo' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (request()->hasFile('logo')) {
                        $file = request()->file('logo');
                        if (! $file->isValid() || ! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'])) {
                            $fail('O logotipo deve ser uma imagem válida (jpeg, png, gif, svg, webp).');
                        }
                        if ($file->getSize() > 10240 * 1024) {
                            $fail('O logotipo não pode exceder 10MB.');
                        }
                    } elseif ($value !== null && ! is_string($value)) {
                        $fail('O logotipo fornecido tem um formato inválido.');
                    }
                },
            ],
        ];
    }

    private static function isValidPtNif(?string $nif): bool
    {
        if (! $nif || strlen($nif) !== 9 || ! is_numeric($nif)) {
            return false;
        }

        $firstChar = $nif[0];
        if (! in_array($firstChar, ['1', '2', '3', '5', '6', '8', '9'])) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += intval($nif[$i]) * (9 - $i);
        }

        $remainder = $sum % 11;
        $checkDigit = ($remainder === 0 || $remainder === 1) ? 0 : (11 - $remainder);

        return $checkDigit === intval($nif[8]);
    }
}
