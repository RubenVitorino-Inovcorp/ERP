<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Company;
use App\Models\Entity;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Proposal;
use App\Models\ProposalLine;
use App\Models\VatRate;
use App\Traits\Searchable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelPdf\Facades\Pdf;

class ProposalController extends Controller
{
    use Searchable;

    /**
     * Display a listing of the proposals.
     */
    public function index(Request $request): Response
    {
        $query = Proposal::with(['entity:id,name', 'lines.vatRate:id,value']);

        $proposals = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['number'],
            sortableFields: ['number', 'proposal_date', 'validity_date', 'status', 'created_at'],
            defaultSort: 'created_at',
            defaultDirection: 'desc'
        );

        // Transform data to append calculated total_value
        $proposals->getCollection()->transform(function ($proposal) {
            $total = 0;
            foreach ($proposal->lines as $line) {
                $vatPercent = $line->vatRate ? (float) $line->vatRate->value : 0;
                $lineTotal = $line->quantity * (float) $line->unit_price * (1 + ($vatPercent / 100));
                $total += $lineTotal;
            }
            $proposal->total_value = round($total, 2);
            return $proposal;
        });

        return Inertia::render('Proposals/Index', [
            'proposals' => $proposals,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    /**
     * Show the form for creating a new proposal.
     */
    public function create(): Response
    {
        $nextNumber = (Proposal::max('number') ?? 0) + 1;
        
        $clients = Entity::whereIn('type', ['cliente', 'ambos'])
            ->where('status', true)
            ->get()
            ->map(fn (Entity $entity) => [
                'id' => $entity->id,
                'name' => $entity->name,
                'nif' => $entity->nif,
                'address' => $entity->address,
                'zip_code' => $entity->zip_code,
                'city' => $entity->city,
            ]);

        $suppliers = Entity::whereIn('type', ['fornecedor', 'ambos'])
            ->where('status', true)
            ->get(['id', 'name']);

        $articles = Article::where('status', true)
            ->get(['id', 'reference', 'name', 'price', 'vat_rate_id']);

        $vatRates = VatRate::all(['id', 'name', 'value']);

        return Inertia::render('Proposals/Create', [
            'nextNumber' => $nextNumber,
            'clients' => $clients,
            'suppliers' => $suppliers,
            'articles' => $articles,
            'vatRates' => $vatRates,
            'defaultValidityDate' => now()->addDays(30)->format('Y-m-d'),
        ]);
    }

    /**
     * Store a newly created proposal in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'validity_date' => 'required|date',
            'status' => 'required|in:rascunho,fechado',
            'lines' => 'required|array|min:1',
            'lines.*.article_id' => 'required|exists:articles,id',
            'lines.*.supplier_id' => 'nullable|exists:entities,id',
            'lines.*.cost_price' => 'nullable|numeric|min:0',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.vat_rate_id' => 'required|exists:vat_rates,id',
        ]);

        DB::transaction(function () use ($validated) {
            $nextNumber = (Proposal::max('number') ?? 0) + 1;
            $proposalDate = $validated['status'] === 'fechado' ? now()->format('Y-m-d') : now()->format('Y-m-d');

            $proposal = Proposal::create([
                'number' => $nextNumber,
                'proposal_date' => $proposalDate,
                'entity_id' => $validated['entity_id'],
                'validity_date' => $validated['validity_date'],
                'status' => $validated['status'],
            ]);

            foreach ($validated['lines'] as $lineData) {
                $proposal->lines()->create([
                    'article_id' => $lineData['article_id'],
                    'supplier_id' => $lineData['supplier_id'] ?? null,
                    'cost_price' => $lineData['cost_price'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate_id' => $lineData['vat_rate_id'],
                ]);
            }
        });

        return redirect()->route('proposals.index')->with('success', 'Proposta criada com sucesso.');
    }

    /**
     * Show the form for editing the specified proposal.
     */
    public function edit(Proposal $proposal): Response
    {
        $proposal->load(['lines.article', 'lines.supplier', 'lines.vatRate']);

        $clients = Entity::whereIn('type', ['cliente', 'ambos'])
            ->where('status', true)
            ->get()
            ->map(fn (Entity $entity) => [
                'id' => $entity->id,
                'name' => $entity->name,
                'nif' => $entity->nif,
                'address' => $entity->address,
                'zip_code' => $entity->zip_code,
                'city' => $entity->city,
            ]);

        $suppliers = Entity::whereIn('type', ['fornecedor', 'ambos'])
            ->where('status', true)
            ->get(['id', 'name']);

        $articles = Article::where('status', true)
            ->get(['id', 'reference', 'name', 'price', 'vat_rate_id']);

        $vatRates = VatRate::all(['id', 'name', 'value']);

        return Inertia::render('Proposals/Edit', [
            'proposal' => $proposal,
            'clients' => $clients,
            'suppliers' => $suppliers,
            'articles' => $articles,
            'vatRates' => $vatRates,
        ]);
    }

    /**
     * Update the specified proposal in storage.
     */
    public function update(Request $request, Proposal $proposal): RedirectResponse
    {
        $validated = $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'validity_date' => 'required|date',
            'status' => 'required|in:rascunho,fechado',
            'lines' => 'required|array|min:1',
            'lines.*.article_id' => 'required|exists:articles,id',
            'lines.*.supplier_id' => 'nullable|exists:entities,id',
            'lines.*.cost_price' => 'nullable|numeric|min:0',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.vat_rate_id' => 'required|exists:vat_rates,id',
        ]);

        DB::transaction(function () use ($proposal, $validated) {
            $proposalDate = $proposal->proposal_date;
            if ($validated['status'] === 'fechado' && $proposal->status !== 'fechado') {
                $proposalDate = now()->format('Y-m-d');
            }

            $proposal->update([
                'entity_id' => $validated['entity_id'],
                'proposal_date' => $proposalDate,
                'validity_date' => $validated['validity_date'],
                'status' => $validated['status'],
            ]);

            // Re-create lines
            $proposal->lines()->delete();

            foreach ($validated['lines'] as $lineData) {
                $proposal->lines()->create([
                    'article_id' => $lineData['article_id'],
                    'supplier_id' => $lineData['supplier_id'] ?? null,
                    'cost_price' => $lineData['cost_price'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate_id' => $lineData['vat_rate_id'],
                ]);
            }
        });

        return redirect()->route('proposals.index')->with('success', 'Proposta atualizada com sucesso.');
    }

    /**
     * Remove the specified proposal from storage.
     */
    public function destroy(Proposal $proposal): RedirectResponse
    {
        $proposal->delete();

        return redirect()->route('proposals.index')->with('success', 'Proposta eliminada com sucesso.');
    }

    /**
     * Download proposal as PDF using spatie/laravel-pdf.
     */
    public function downloadPdf(Proposal $proposal)
    {
        $proposal->load(['entity', 'lines.article', 'lines.supplier', 'lines.vatRate']);
        $company = Company::first();

        return Pdf::view('pdf.proposal', [
            'proposal' => $proposal,
            'company' => $company,
        ])
        ->name("Proposta_{$proposal->number}.pdf")
        ->download();
    }

    /**
     * Convert proposal into a draft customer order.
     */
    public function convertToOrder(Proposal $proposal): RedirectResponse
    {
        DB::transaction(function () use ($proposal) {
            $nextOrderNumber = (Order::where('type', 'cliente')->max('number') ?? 0) + 1;

            $order = Order::create([
                'type' => 'cliente',
                'number' => $nextOrderNumber,
                'order_date' => now()->format('Y-m-d'),
                'entity_id' => $proposal->entity_id,
                'proposal_id' => $proposal->id,
                'status' => 'rascunho',
            ]);

            foreach ($proposal->lines as $line) {
                OrderLine::create([
                    'order_id' => $order->id,
                    'article_id' => $line->article_id,
                    'supplier_id' => $line->supplier_id,
                    'cost_price' => $line->cost_price,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'vat_rate_id' => $line->vat_rate_id,
                ]);
            }
        });

        return redirect()->route('proposals.index')->with('success', 'Proposta convertida em Encomenda com sucesso.');
    }
}
