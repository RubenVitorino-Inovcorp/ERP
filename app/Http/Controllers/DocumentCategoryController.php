<?php

namespace App\Http\Controllers;

use App\Models\DocumentCategory;
use App\Traits\Searchable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentCategoryController extends Controller
{
    use Searchable;

    public function index(Request $request): Response
    {
        $query = DocumentCategory::query();

        $documentCategories = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['name'],
            blindIndexFields: [],
            sortableFields: ['name', 'created_at'],
            defaultSort: 'name',
            defaultDirection: 'asc'
        );

        return Inertia::render('DocumentCategories/Index', [
            'documentCategories' => $documentCategories,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('DocumentCategories/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_categories,name',
            'status' => 'boolean',
        ]);

        DocumentCategory::create([
            'name' => $validated['name'],
            'status' => $validated['status'] ?? true,
        ]);

        return redirect()->route('document-categories.index')->with('success', 'Categoria de documento criada com sucesso.');
    }

    public function edit(DocumentCategory $documentCategory): Response
    {
        return Inertia::render('DocumentCategories/Edit', [
            'documentCategory' => $documentCategory,
        ]);
    }

    public function update(Request $request, DocumentCategory $documentCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_categories,name,'.$documentCategory->id,
            'status' => 'boolean',
        ]);

        $documentCategory->update([
            'name' => $validated['name'],
            'status' => $validated['status'] ?? true,
        ]);

        return redirect()->route('document-categories.index')->with('success', 'Categoria de documento atualizada com sucesso.');
    }

    public function destroy(DocumentCategory $documentCategory): RedirectResponse
    {
        if ($documentCategory->digitalFiles()->count() > 0) {
            return redirect()->route('document-categories.index')->with('error', 'Não é possível eliminar esta categoria porque já existem ficheiros associados.');
        }

        $documentCategory->delete();

        return redirect()->route('document-categories.index')->with('success', 'Categoria de documento eliminada.');
    }
}
