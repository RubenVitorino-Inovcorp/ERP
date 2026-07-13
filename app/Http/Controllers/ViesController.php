<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use App\Models\Country;

class ViesController extends Controller
{
    /**
     * Look up VAT information in the VIES system.
     */
    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'nif' => 'required|string',
            'country_id' => 'nullable|exists:countries,id',
            'country_code' => 'nullable|string|size:2',
        ]);

        $nif = preg_replace('/[^A-Za-z0-9]/', '', $request->nif);
        $countryCode = 'PT';

        if ($request->country_id) {
            $country = Country::find($request->country_id);
            if ($country) {
                $countryCode = strtoupper($country->code);
            }
        } elseif ($request->country_code) {
            $countryCode = strtoupper($request->country_code);
        }

        try {
            // Remove country prefix from NIF if it starts with the country code (e.g., PT500000000 -> 500000000)
            if (str_starts_with(strtoupper($nif), $countryCode)) {
                $nif = substr($nif, strlen($countryCode));
            }

            $response = Http::timeout(5)->get("https://ec.europa.eu/taxation_customs/vies/rest-api/ms/{$countryCode}/vat/{$nif}");

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível ligar ao serviço VIES. Tente novamente mais tarde.',
                ], 502);
            }

            $data = $response->json();

            if (!($data['isValid'] ?? false)) {
                if ($countryCode === 'PT' && self::isValidPtNif($nif)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'NIF estruturalmente válido, mas não registado no VIES para transações intracomunitárias.',
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'NIF não encontrado ou inválido no sistema VIES.',
                ]);
            }

            $name = trim($data['name'] ?? '');
            $address = trim($data['address'] ?? '');
            $zipCode = '';
            $city = '';

            // Tentar extrair código postal (formato XXXX-XXX) e localidade para Portugal
            if ($countryCode === 'PT' && preg_match('/(\d{4}-\d{3})/', $address, $matches)) {
                $zipCode = $matches[1];
                $parts = explode($zipCode, $address);
                $address = trim($parts[0], " ,\n\r\t");
                $city = trim($parts[1] ?? '', " ,\n\r\t");
            }

            return response()->json([
                'success' => true,
                'name' => $name,
                'address' => $address,
                'zip_code' => $zipCode,
                'city' => $city,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar consulta VIES: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Valida se o NIF português é estruturalmente válido via Módulo 11.
     */
    private static function isValidPtNif(?string $nif): bool
    {
        if (!$nif || strlen($nif) !== 9 || !is_numeric($nif)) {
            return false;
        }

        $firstChar = $nif[0];
        if (!in_array($firstChar, ['1', '2', '3', '5', '6', '8', '9'])) {
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
