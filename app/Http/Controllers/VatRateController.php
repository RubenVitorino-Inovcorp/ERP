<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVatRateRequest;
use App\Http\Requests\UpdateVatRateRequest;
use App\Models\VatRate;
use App\Models\Article;
use App\Traits\Searchable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class VatRateController extends Controller
{
    use Searchable;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = VatRate::query();

        $vatRates = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['name'],
            blindIndexFields: [],
            sortableFields: ['name', 'value', 'created_at'],
            defaultSort: 'created_at',
            defaultDirection: 'desc'
        );

        return Inertia::render('VatRates/Index', [
            'vatRates' => $vatRates,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('VatRates/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVatRateRequest $request): RedirectResponse
    {
        VatRate::create($request->validated());

        return redirect()->route('vat-rates.index')->with('success', 'Taxa de IVA criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VatRate $vatRate)
    {
        return redirect()->route('vat-rates.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VatRate $vatRate): Response
    {
        return Inertia::render('VatRates/Edit', [
            'vatRate' => $vatRate,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVatRateRequest $request, VatRate $vatRate): RedirectResponse
    {
        $vatRate->update($request->validated());

        return redirect()->route('vat-rates.index')->with('success', 'Taxa de IVA atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VatRate $vatRate): RedirectResponse
    {
        if (Article::where('vat_rate_id', $vatRate->id)->exists()) {
            return redirect()->route('vat-rates.index')->with('error', 'Não é possível eliminar esta taxa de IVA porque está associada a um ou mais artigos.');
        }

        $vatRate->delete();

        return redirect()->route('vat-rates.index')->with('success', 'Taxa de IVA eliminada com sucesso.');
    }
}
