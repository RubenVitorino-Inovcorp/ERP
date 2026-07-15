<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\VatRate;
use App\Traits\Searchable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    use Searchable;

    /**
     * Display a listing of the articles.
     */
    public function index(Request $request): Response
    {
        $query = Article::with('vatRate');

        $articles = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['reference', 'name', 'description'],
            sortableFields: ['reference', 'name', 'price', 'status', 'created_at'],
            defaultSort: 'created_at',
            defaultDirection: 'desc'
        );

        return Inertia::render('Articles/Index', [
            'articles' => $articles,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    /**
     * Show the form for creating a new article.
     */
    public function create(): Response
    {
        $vatRates = VatRate::all(['id', 'name', 'value']);

        return Inertia::render('Articles/Create', [
            'vatRates' => $vatRates,
        ]);
    }

    /**
     * Store a newly created article in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'reference' => 'required|string|max:255|unique:articles,reference',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'vat_rate_id' => 'required|exists:vat_rates,id',
            'photo' => 'nullable|image|max:2048', // Max 2MB
            'notes' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('articles', 'public');
        }

        Article::create([
            'reference' => $request->reference,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'vat_rate_id' => $request->vat_rate_id,
            'photo_path' => $photoPath,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        return redirect()->route('articles.index')->with('success', 'Artigo criado com sucesso.');
    }

    /**
     * Display the specified article.
     */
    public function show(Article $article): Response
    {
        $article->load('vatRate');

        return Inertia::render('Articles/Show', [
            'article' => $article,
        ]);
    }

    /**
     * Show the form for editing the specified article.
     */
    public function edit(Article $article): Response
    {
        $vatRates = VatRate::all(['id', 'name', 'value']);

        return Inertia::render('Articles/Edit', [
            'article' => $article,
            'vatRates' => $vatRates,
        ]);
    }

    /**
     * Update the specified article in storage.
     */
    public function update(Request $request, Article $article): RedirectResponse
    {
        $request->validate([
            'reference' => 'required|string|max:255|unique:articles,reference,'.$article->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'vat_rate_id' => 'required|exists:vat_rates,id',
            'photo' => 'nullable|image|max:2048',
            'remove_photo' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $photoPath = $article->photo_path;

        // Processa a remoção da foto
        if ($request->boolean('remove_photo')) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = null;
        }

        // Processa o upload de uma nova foto
        if ($request->hasFile('photo')) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store('articles', 'public');
        }

        $article->update([
            'reference' => $request->reference,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'vat_rate_id' => $request->vat_rate_id,
            'photo_path' => $photoPath,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        return redirect()->route('articles.index')->with('success', 'Artigo atualizado com sucesso.');
    }

    /**
     * Remove the specified article from storage.
     */
    public function destroy(Article $article): RedirectResponse
    {
        try {
            $article->delete();
            
            if ($article->photo_path) {
                Storage::disk('public')->delete($article->photo_path);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return redirect()->route('articles.index')->with('error', 'Não é possível eliminar este artigo pois está associado a propostas ou encomendas.');
            }
            return redirect()->route('articles.index')->with('error', 'Ocorreu um erro ao eliminar o artigo.');
        }

        return redirect()->route('articles.index')->with('success', 'Artigo eliminado com sucesso.');
    }
}
