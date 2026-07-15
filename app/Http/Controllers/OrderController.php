<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Article;
use App\Models\Company;
use App\Models\Entity;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\VatRate;
use App\Traits\Searchable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelPdf\Facades\Pdf;

class OrderController extends Controller
{
    use Searchable;

    /**
     * Display a listing of the client orders.
     */
    public function index(Request $request): Response
    {
        $query = Order::with(['entity:id,name', 'lines.vatRate:id,value'])
            ->where('type', 'cliente');

        $orders = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['number'],
            sortableFields: ['number', 'order_date', 'status', 'created_at'],
            defaultSort: 'created_at',
            defaultDirection: 'desc'
        );

        $orders->getCollection()->transform(function ($order) {
            $total = 0;
            foreach ($order->lines as $line) {
                $vatPercent = $line->vatRate ? (float) $line->vatRate->value : 0;
                $lineTotal = $line->quantity * (float) $line->unit_price * (1 + ($vatPercent / 100));
                $total += $lineTotal;
            }
            $order->total_value = round($total, 2);

            return $order;
        });

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    /**
     * Show the form for creating a new client order.
     */
    public function create(): Response
    {
        $nextNumber = (Order::where('type', 'cliente')->max('number') ?? 0) + 1;

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

        return Inertia::render('Orders/Create', [
            'nextNumber' => $nextNumber,
            'clients' => $clients,
            'suppliers' => $suppliers,
            'articles' => $articles,
            'vatRates' => $vatRates,
        ]);
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $nextNumber = (Order::where('type', 'cliente')->max('number') ?? 0) + 1;

            $order = Order::create([
                'type' => 'cliente',
                'number' => $nextNumber,
                'order_date' => now()->format('Y-m-d'),
                'entity_id' => $validated['entity_id'],
                'status' => $validated['status'],
            ]);

            foreach ($validated['lines'] as $lineData) {
                $order->lines()->create([
                    'article_id' => $lineData['article_id'],
                    'supplier_id' => $lineData['supplier_id'] ?? null,
                    'cost_price' => $lineData['cost_price'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate_id' => $lineData['vat_rate_id'],
                ]);
            }
        });

        return redirect()->route('encomendas.index')->with('success', 'Encomenda criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $encomenda)
    {
        $order = $encomenda;
        //
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $encomenda): Response
    {
        $order = $encomenda;
        $order->load(['lines.article', 'lines.supplier', 'lines.vatRate']);

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

        return Inertia::render('Orders/Edit', [
            'order' => $order,
            'clients' => $clients,
            'suppliers' => $suppliers,
            'articles' => $articles,
            'vatRates' => $vatRates,
        ]);
    }

    /**
     * Update the specified order in storage.
     */
    public function update(UpdateOrderRequest $request, Order $encomenda): RedirectResponse
    {
        $order = $encomenda;
        $validated = $request->validated();

        DB::transaction(function () use ($order, $validated) {
            $order->update([
                'entity_id' => $validated['entity_id'],
                'status' => $validated['status'],
            ]);

            $order->lines()->delete();

            foreach ($validated['lines'] as $lineData) {
                $order->lines()->create([
                    'article_id' => $lineData['article_id'],
                    'supplier_id' => $lineData['supplier_id'] ?? null,
                    'cost_price' => $lineData['cost_price'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate_id' => $lineData['vat_rate_id'],
                ]);
            }
        });

        return redirect()->route('encomendas.index')->with('success', 'Encomenda atualizada com sucesso.');
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $encomenda): RedirectResponse
    {
        $order = $encomenda;
        
        try {
            $order->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return redirect()->route('encomendas.index')->with('error', 'Não é possível eliminar esta encomenda pois tem registos associados.');
            }
            return redirect()->route('encomendas.index')->with('error', 'Ocorreu um erro ao eliminar a encomenda.');
        }

        return redirect()->route('encomendas.index')->with('success', 'Encomenda eliminada com sucesso.');
    }

    /**
     * Download order as PDF.
     */
    public function downloadPdf(Order $encomenda)
    {
        $order = $encomenda;
        $order->load(['entity', 'lines.article', 'lines.supplier', 'lines.vatRate']);
        $company = Company::first();

        return Pdf::view('pdf.order', [
            'order' => $order,
            'company' => $company,
        ])
            ->name("Encomenda_{$order->number}.pdf")
            ->download();
    }

    /**
     * Convert client order to supplier orders.
     */
    public function convertToSupplierOrders(Order $encomenda): RedirectResponse
    {
        $order = $encomenda;
        if ($order->status !== 'fechado') {
            return redirect()->back()->with('error', 'Apenas encomendas fechadas podem ser convertidas.');
        }

        if ($order->type !== 'cliente') {
            return redirect()->back()->with('error', 'Apenas encomendas de clientes podem ser convertidas em encomendas de fornecedor.');
        }

        DB::transaction(function () use ($order) {
            $linesBySupplier = $order->lines->groupBy('supplier_id');

            foreach ($linesBySupplier as $supplierId => $lines) {
                if (! $supplierId) {
                    continue; // Saltar linhas sem fornecedor
                }

                $nextOrderNumber = (Order::where('type', 'fornecedor')->max('number') ?? 0) + 1;

                $supplierOrder = Order::create([
                    'type' => 'fornecedor',
                    'number' => $nextOrderNumber,
                    'order_date' => now()->format('Y-m-d'),
                    'entity_id' => $supplierId,
                    'status' => 'rascunho',
                ]);

                foreach ($lines as $line) {
                    OrderLine::create([
                        'order_id' => $supplierOrder->id,
                        'article_id' => $line->article_id,
                        'supplier_id' => $line->supplier_id,
                        'cost_price' => $line->cost_price,
                        'quantity' => $line->quantity,
                        'unit_price' => $line->cost_price ?? $line->unit_price,
                        'vat_rate_id' => $line->vat_rate_id,
                    ]);
                }
            }
        });

        return redirect()->route('encomendas.index')->with('success', 'Encomendas de Fornecedor criadas com sucesso.');
    }
}
