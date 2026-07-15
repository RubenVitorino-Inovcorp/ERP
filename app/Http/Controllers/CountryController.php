<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCountryRequest;
use App\Http\Requests\UpdateCountryRequest;
use App\Models\Country;
use App\Traits\Searchable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CountryController extends Controller
{
    use Searchable;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Country::query();

        $countries = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['name', 'code'],
            blindIndexFields: [],
            sortableFields: ['name', 'code', 'created_at'],
            defaultSort: 'name',
            defaultDirection: 'asc'
        );

        return Inertia::render('Countries/Index', [
            'countries' => $countries,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Countries/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCountryRequest $request): RedirectResponse
    {
        if (Country::where('code', $request->code)->exists()) {
            return redirect()->route('countries.index')->with('error', 'Código do país já existe.');
        }

        if (Country::where('name', $request->name)->exists()) {
            return redirect()->route('countries.index')->with('error', 'Nome do país já existe.');
        }

        Country::create($request->validated());

        return redirect()->route('countries.index')->with('success', 'País criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Country $country)
    {
        return redirect()->route('countries.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Country $country): Response
    {
        return Inertia::render('Countries/Edit', [
            'country' => $country,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCountryRequest $request, Country $country): RedirectResponse
    {
        if ($country->code != $request->code && Country::where('code', $request->code)->exists()) {
            return redirect()->refresh()->with('error', 'Código do país já existe.');
        }

        if ($country->name != $request->name && Country::where('name', $request->name)->exists()) {
            return redirect()->refresh()->with('error', 'Nome do país já existe.');
        }

        $country->update($request->validated());

        return redirect()->route('countries.index')->with('success', 'País atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Country $country): RedirectResponse
    {
        if (! $country->exists()) {
            return redirect()->route('countries.index')->with('error', 'País não encontrado.');
        }

        try {
            $country->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return redirect()->route('countries.index')->with('error', 'Não é possível eliminar este país pois está associado a outras entidades.');
            }
            return redirect()->route('countries.index')->with('error', 'Ocorreu um erro ao eliminar o país.');
        }

        return redirect()->route('countries.index')->with('success', 'País eliminado com sucesso.');
    }
}
