<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\Country;
use App\Traits\Searchable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Spatie\LaravelCipherSweet\Rules\EncryptedUniqueRule;

class ClientController extends Controller
{
    use Searchable;

    /**
     * Display a listing of the clients.
     */
    public function index(Request $request): Response
    {
        $query = Entity::whereIn('type', ['cliente', 'ambos']);

        $clients = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['name', 'website'],
            blindIndexFields: [
                'nif' => 'nif_index',
                'email' => 'email_index',
                'phone' => 'phone_index',
                'mobile' => 'mobile_index',
            ],
            sortableFields: ['name', 'status', 'created_at'],
            defaultSort: 'created_at',
            defaultDirection: 'desc'
        );

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    /**
     * Show the form for creating a new client.
     */
    public function create(): Response
    {
        $nextNumber = (Entity::max('number') ?? 0) + 1;
        $countries = Country::all(['id', 'name', 'code']);

        return Inertia::render('Clients/Create', [
            'nextNumber' => $nextNumber,
            'countries' => $countries,
        ]);
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:cliente,fornecedor,ambos',
            'nif' => [
                'required',
                'string',
                new EncryptedUniqueRule(Entity::class, 'nif_index'),
                function ($attribute, $value, $fail) use ($request) {
                    $country = Country::find($request->country_id);
                    if ($country && $country->code === 'PT' && !self::isValidPtNif($value)) {
                        $fail('O NIF introduzido não é válido.');
                    }
                }
            ],
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'zip_code' => 'required|string|regex:/^\d{4}-\d{3}$/',
            'city' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'website' => 'nullable|string|url|max:255',
            'email' => 'required|email|max:255',
            'gdpr_consent' => 'required|boolean',
            'notes' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        Entity::create([
            'type' => $request->type,
            'number' => (Entity::max('number') ?? 0) + 1,
            'nif' => $request->nif,
            'name' => $request->name,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
            'city' => $request->city,
            'country_id' => $request->country_id,
            'phone' => $request->phone,
            'mobile' => $request->mobile,
            'website' => $request->website,
            'email' => $request->email,
            'gdpr_consent' => $request->gdpr_consent,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        return redirect()->route('clients.index')->with('success', 'Cliente criado com sucesso.');
    }

    /**
     * Display the specified client.
     */
    public function show(Entity $entity): Response
    {
        $entity->load('country');

        return Inertia::render('Clients/Show', [
            'entity' => $entity,
        ]);
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Entity $entity): Response
    {
        $countries = Country::all(['id', 'name', 'code']);

        return Inertia::render('Clients/Edit', [
            'entity' => $entity,
            'countries' => $countries,
        ]);
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, Entity $entity): RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:cliente,fornecedor,ambos',
            'nif' => [
                'required',
                'string',
                (new EncryptedUniqueRule(Entity::class, 'nif_index'))->ignore($entity->id),
                function ($attribute, $value, $fail) use ($request) {
                    $country = Country::find($request->country_id);
                    if ($country && $country->code === 'PT' && !self::isValidPtNif($value)) {
                        $fail('O NIF introduzido não é válido.');
                    }
                }
            ],
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'zip_code' => 'required|string|regex:/^\d{4}-\d{3}$/',
            'city' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'website' => 'nullable|string|url|max:255',
            'email' => 'required|email|max:255',
            'gdpr_consent' => 'required|boolean',
            'notes' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $entity->update([
            'type' => $request->type,
            'nif' => $request->nif,
            'name' => $request->name,
            'address' => $request->address,
            'zip_code' => $request->zip_code,
            'city' => $request->city,
            'country_id' => $request->country_id,
            'phone' => $request->phone,
            'mobile' => $request->mobile,
            'website' => $request->website,
            'email' => $request->email,
            'gdpr_consent' => $request->gdpr_consent,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        return redirect()->route('clients.index')->with('success', 'Cliente atualizado com sucesso.');
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Entity $entity): RedirectResponse
    {
        $entity->delete();

        return redirect()->route('clients.index')->with('success', 'Cliente eliminado com sucesso.');
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

