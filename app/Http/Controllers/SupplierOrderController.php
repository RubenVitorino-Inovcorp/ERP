<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierOrderRequest;
use App\Http\Requests\UpdateSupplierOrderRequest;
use App\Models\Article;
use App\Models\Company;
use App\Models\Entity;
use App\Models\Order;
use App\Models\VatRate;
use App\Traits\Searchable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelPdf\Facades\Pdf;

class SupplierOrderController extends Controller
{
    use Searchable;

    /**
     * Display a listing of the supplier orders.
     */
    public function index(Request $request): Response
    {
        $query = Order::with(['entity:id,name', 'lines.vatRate:id,value'])
            ->where('type', 'fornecedor');

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
            'type' => 'fornecedor',
        ]);
    }

    /**
     * Show the form for creating a new supplier order.
     */
    public function create(): Response
    {
        $nextNumber = (Order::where('type', 'fornecedor')->max('number') ?? 0) + 1;

        $suppliers = Entity::whereIn('type', ['fornecedor', 'ambos'])
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

        $articles = Article::where('status', true)
            ->get(['id', 'reference', 'name', 'price', 'vat_rate_id']);

        $vatRates = VatRate::all(['id', 'name', 'value']);

        return Inertia::render('Orders/Create', [
            'nextNumber' => $nextNumber,
            'clients' => $suppliers, // reuse clients prop as suppliers since it's the main entity select
            'suppliers' => $suppliers,
            'articles' => $articles,
            'vatRates' => $vatRates,
            'type' => 'fornecedor',
        ]);
    }

    /**
     * Store a newly created supplier order in storage.
     */
    public function store(StoreSupplierOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $nextNumber = (Order::where('type', 'fornecedor')->max('number') ?? 0) + 1;

            $order = Order::create([
                'type' => 'fornecedor',
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

        return redirect()->route('supplier-orders.index')->with('success', 'Encomenda de Fornecedor criada com sucesso.');
    }

    /**
     * Show the form for editing the specified supplier order.
     */
    public function edit(Order $encomendasFornecedore): Response
    {
        $order = $encomendasFornecedore;
        $order->load(['lines.article', 'lines.supplier', 'lines.vatRate']);

        $suppliers = Entity::whereIn('type', ['fornecedor', 'ambos'])
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

        $articles = Article::where('status', true)
            ->get(['id', 'reference', 'name', 'price', 'vat_rate_id']);

        $vatRates = VatRate::all(['id', 'name', 'value']);

        return Inertia::render('Orders/Edit', [
            'order' => $order,
            'clients' => $suppliers, // reuse clients prop
            'suppliers' => $suppliers,
            'articles' => $articles,
            'vatRates' => $vatRates,
            'type' => 'fornecedor',
        ]);
    }

    /**
     * Update the specified supplier order in storage.
     */
    public function update(UpdateSupplierOrderRequest $request, Order $encomendasFornecedore): RedirectResponse
    {
        $order = $encomendasFornecedore;
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

        return redirect()->route('supplier-orders.index')->with('success', 'Encomenda de Fornecedor atualizada com sucesso.');
    }

    /**
     * Remove the specified supplier order from storage.
     */
    public function destroy(Order $encomendasFornecedore): RedirectResponse
    {
        $order = $encomendasFornecedore;
        
        try {
            $order->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return redirect()->route('supplier-orders.index')->with('error', 'Não é possível eliminar esta encomenda pois tem registos associados (ex. faturas).');
            }
            return redirect()->route('supplier-orders.index')->with('error', 'Ocorreu um erro ao eliminar a encomenda.');
        }

        return redirect()->route('supplier-orders.index')->with('success', 'Encomenda de Fornecedor eliminada com sucesso.');
    }

    /**
     * Download supplier order as PDF.
     */
    public function downloadPdf(Order $encomendasFornecedore)
    {
        $order = $encomendasFornecedore;
        $order->load(['entity', 'lines.article', 'lines.supplier', 'lines.vatRate']);
        $company = Company::first();

        return Pdf::view('pdf.order', [
            'order' => $order,
            'company' => $company,
        ])
            ->name("Encomenda_Fornecedor_{$order->number}.pdf")
            ->download();
    }
}
