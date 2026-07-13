<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactFunctionRequest;
use App\Http\Requests\UpdateContactFunctionRequest;
use App\Models\ContactFunction;
use App\Models\Contact;
use App\Traits\Searchable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ContactFunctionController extends Controller
{
    use Searchable;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = ContactFunction::query();

        $contactFunctions = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['name'],
            blindIndexFields: [],
            sortableFields: ['name', 'created_at'],
            defaultSort: 'created_at',
            defaultDirection: 'desc'
        );

        return Inertia::render('ContactFunctions/Index', [
            'contactFunctions' => $contactFunctions,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('ContactFunctions/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactFunctionRequest $request): RedirectResponse
    {
        ContactFunction::create($request->validated());

        return redirect()->route('contact-functions.index')->with('success', 'Função de contacto criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ContactFunction $contactFunction)
    {
        return redirect()->route('contact-functions.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContactFunction $contactFunction): Response
    {
        return Inertia::render('ContactFunctions/Edit', [
            'contactFunction' => $contactFunction,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContactFunctionRequest $request, ContactFunction $contactFunction): RedirectResponse
    {
        $contactFunction->update($request->validated());

        return redirect()->route('contact-functions.index')->with('success', 'Função de contacto atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactFunction $contactFunction): RedirectResponse
    {
        if (Contact::where('contact_function_id', $contactFunction->id)->exists()) {
            return redirect()->route('contact-functions.index')->with('error', 'Não é possível eliminar esta função de contacto porque está associada a um ou mais contactos.');
        }

        $contactFunction->delete();

        return redirect()->route('contact-functions.index')->with('success', 'Função de contacto eliminada com sucesso.');
    }
}
