<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Entity;
use App\Models\ContactFunction;
use App\Traits\Searchable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    use Searchable;

    /**
     * Display a listing of the contacts.
     */
    public function index(Request $request): Response
    {
        // Start query with eager loading for datatable relationships
        $query = Contact::with(['entity:id,name,type', 'contactFunction:id,name']);

        $contacts = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['name', 'last_name'],
            blindIndexFields: [
                'email' => 'email_index',
                'phone' => 'phone_index',
                'mobile' => 'mobile_index',
            ],
            sortableFields: ['name', 'last_name', 'status', 'created_at'],
            defaultSort: 'created_at',
            defaultDirection: 'desc'
        );

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    /**
     * Show the form for creating a new contact.
     */
    public function create(): Response
    {
        $nextNumber = (Contact::max('number') ?? 0) + 1;
        $entities = Entity::all()->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'type' => $e->type, 'nif' => $e->nif]);
        $functions = ContactFunction::all(['id', 'name']);

        return Inertia::render('Contacts/Create', [
            'nextNumber' => $nextNumber,
            'entities' => $entities,
            'functions' => $functions,
        ]);
    }

    /**
     * Store a newly created contact in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_function_id' => 'required|exists:contact_functions,id',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'gdpr_consent' => 'required|boolean',
            'notes' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        Contact::create([
            'number' => (Contact::max('number') ?? 0) + 1,
            'entity_id' => $request->entity_id,
            'name' => $request->name,
            'last_name' => $request->last_name,
            'contact_function_id' => $request->contact_function_id,
            'phone' => $request->phone,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'gdpr_consent' => $request->gdpr_consent,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        return redirect()->route('contacts.index')->with('success', 'Contacto criado com sucesso.');
    }

    /**
     * Display the specified contact.
     */
    public function show(Contact $contact): Response
    {
        $contact->load(['entity', 'contactFunction']);

        return Inertia::render('Contacts/Show', [
            'contact' => $contact,
        ]);
    }

    /**
     * Show the form for editing the specified contact.
     */
    public function edit(Contact $contact): Response
    {
        $entities = Entity::all()->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'type' => $e->type, 'nif' => $e->nif]);
        $functions = ContactFunction::all(['id', 'name']);

        return Inertia::render('Contacts/Edit', [
            'contact' => $contact,
            'entities' => $entities,
            'functions' => $functions,
        ]);
    }

    /**
     * Update the specified contact in storage.
     */
    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_function_id' => 'required|exists:contact_functions,id',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'gdpr_consent' => 'required|boolean',
            'notes' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $contact->update([
            'entity_id' => $request->entity_id,
            'name' => $request->name,
            'last_name' => $request->last_name,
            'contact_function_id' => $request->contact_function_id,
            'phone' => $request->phone,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'gdpr_consent' => $request->gdpr_consent,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        return redirect()->route('contacts.index')->with('success', 'Contacto atualizado com sucesso.');
    }

    /**
     * Remove the specified contact from storage.
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'Contacto eliminado com sucesso.');
    }
}
