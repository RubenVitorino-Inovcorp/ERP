<?php

namespace App\Http\Controllers;

use App\Mail\SupplierPaymentProofMail;
use App\Models\Entity;
use App\Models\Order;
use App\Models\SupplierInvoice;
use App\Traits\Searchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\DigitalFile;
use App\Models\DocumentCategory;

class SupplierInvoiceController extends Controller
{
    use Searchable;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SupplierInvoice::with(['entity:id,name', 'order:id,number']);

        $invoices = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['number'],
            sortableFields: ['invoice_date', 'due_date', 'number', 'total_value', 'status'],
            defaultSort: 'invoice_date',
            defaultDirection: 'desc'
        );

        return Inertia::render('SupplierInvoices/Index', [
            'invoices' => $invoices,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Entity::whereIn('type', ['fornecedor', 'ambos'])
            ->where('status', true)
            ->orderBy('name')
            ->get();

        // Map to force decryption
        $suppliers = $suppliers->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'nif' => $s->nif,
            ];
        });

        $orders = Order::with('lines.vatRate')
            ->where('type', 'fornecedor')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'number', 'entity_id', 'order_date']);

        $orders->transform(function ($order) {
            $total = 0;
            foreach ($order->lines as $line) {
                $vatPercent = $line->vatRate ? (float) $line->vatRate->value : 0;
                $lineTotal = $line->quantity * (float) $line->unit_price * (1 + ($vatPercent / 100));
                $total += $lineTotal;
            }
            $order->total_value = round($total, 2);

            return [
                'id' => $order->id,
                'number' => $order->number,
                'entity_id' => $order->entity_id,
                'order_date' => $order->order_date,
                'total_value' => $order->total_value,
            ];
        });

        return Inertia::render('SupplierInvoices/Create', [
            'suppliers' => $suppliers,
            'orders' => $orders,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => ['required', 'string', 'max:255'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'entity_id' => ['required', 'exists:entities,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'total_value' => ['required', 'numeric', 'min:0'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $path = $file->store('supplier_invoices', 'local');
            $validated['document_path'] = $path;
        }

        $invoice = SupplierInvoice::create($validated);

        if ($request->hasFile('document')) {
            $category = DocumentCategory::firstOrCreate(['name' => 'Faturas']);
            $invoice->digitalFiles()->create([
                'name' => $invoice->number,
                'document_category_id' => $category->id,
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
        }

        return redirect()->route('supplier-invoices.index')->with('success', 'Fatura de fornecedor registada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SupplierInvoice $faturas_fornecedore)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SupplierInvoice $faturas_fornecedore)
    {
        $suppliers = Entity::whereIn('type', ['fornecedor', 'ambos'])
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $suppliers = $suppliers->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'nif' => $s->nif,
            ];
        });

        $orders = Order::with('lines.vatRate')
            ->where('type', 'fornecedor')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'number', 'entity_id', 'order_date']);

        $orders->transform(function ($order) {
            $total = 0;
            foreach ($order->lines as $line) {
                $vatPercent = $line->vatRate ? (float) $line->vatRate->value : 0;
                $lineTotal = $line->quantity * (float) $line->unit_price * (1 + ($vatPercent / 100));
                $total += $lineTotal;
            }
            $order->total_value = round($total, 2);

            return [
                'id' => $order->id,
                'number' => $order->number,
                'entity_id' => $order->entity_id,
                'order_date' => $order->order_date,
                'total_value' => $order->total_value,
            ];
        });

        return Inertia::render('SupplierInvoices/Edit', [
            'invoice' => $faturas_fornecedore,
            'suppliers' => $suppliers,
            'orders' => $orders,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SupplierInvoice $faturas_fornecedore)
    {
        $validated = $request->validate([
            'number' => ['required', 'string', 'max:255'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'entity_id' => ['required', 'exists:entities,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'total_value' => ['required', 'numeric', 'min:0'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('document')) {
            if ($faturas_fornecedore->document_path) {
                Storage::disk('local')->delete($faturas_fornecedore->document_path);
            }
            $file = $request->file('document');
            $path = $file->store('supplier_invoices', 'local');
            $validated['document_path'] = $path;
            
            $category = DocumentCategory::firstOrCreate(['name' => 'Faturas']);
            $faturas_fornecedore->digitalFiles()->updateOrCreate(
                ['document_category_id' => $category->id], // Assuming one invoice document per invoice
                [
                    'name' => $request->number,
                    'file_path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                ]
            );
        } else {
            // Update the name of the digital file if the invoice number changed
            $faturas_fornecedore->digitalFiles()->whereHas('category', function($q) {
                $q->where('name', 'Faturas');
            })->update(['name' => $request->number]);
        }

        $faturas_fornecedore->update($validated);

        return redirect()->route('supplier-invoices.index')->with('success', 'Fatura atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SupplierInvoice $faturas_fornecedore)
    {
        try {
            // Since digitalFiles has cascadeOnDelete on fileable_id? No, it's polymorphic.
            // We should manually delete physical files
            if ($faturas_fornecedore->document_path) {
                Storage::disk('local')->delete($faturas_fornecedore->document_path);
            }

            if ($faturas_fornecedore->proof_of_payment_path) {
                Storage::disk('local')->delete($faturas_fornecedore->proof_of_payment_path);
            }
            
            // Delete DigitalFiles records (the files are the same, so deleting the paths above is enough)
            $faturas_fornecedore->digitalFiles()->delete();

            $faturas_fornecedore->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return redirect()->route('supplier-invoices.index')->with('error', 'Não é possível eliminar esta fatura pois tem registos associados.');
            }
            return redirect()->route('supplier-invoices.index')->with('error', 'Ocorreu um erro ao eliminar a fatura.');
        }

        return redirect()->route('supplier-invoices.index')->with('success', 'Fatura eliminada com sucesso.');
    }

    /**
     * Mark the invoice as paid and optionally send proof of payment.
     */
    public function pay(Request $request, SupplierInvoice $faturas_fornecedore)
    {
        \Illuminate\Support\Facades\Log::info('Pay request data', [
            'has_file' => $request->hasFile('proof_of_payment'),
            'file_name' => $request->file('proof_of_payment') ? $request->file('proof_of_payment')->getClientOriginalName() : 'none',
            'all' => $request->all(),
        ]);

        $request->validate([
            'proof_of_payment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('proof_of_payment')) {
            if ($faturas_fornecedore->proof_of_payment_path) {
                Storage::disk('local')->delete($faturas_fornecedore->proof_of_payment_path);
            }
            $file = $request->file('proof_of_payment');
            $path = $file->store('supplier_invoices/proofs', 'local');
            $faturas_fornecedore->proof_of_payment_path = $path;
            
            $category = DocumentCategory::firstOrCreate(['name' => 'Comprovativos de Pagamento']);
            $faturas_fornecedore->digitalFiles()->updateOrCreate(
                ['document_category_id' => $category->id], // Assuming one proof per invoice
                [
                    'name' => 'Comprovativo - ' . $faturas_fornecedore->number,
                    'file_path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                ]
            );
        }

        $faturas_fornecedore->status = 'paga';
        $faturas_fornecedore->save();

        $faturas_fornecedore->load('entity');

        if ($faturas_fornecedore->entity && $faturas_fornecedore->entity->email) {
            $emailAddress = $faturas_fornecedore->entity->email;

            if ($emailAddress) {
                Mail::to($emailAddress)->send(
                    new SupplierPaymentProofMail(
                        $faturas_fornecedore->number,
                        $faturas_fornecedore->proof_of_payment_path,
                        $faturas_fornecedore->document_path
                    )
                );
            }
        }

        return redirect()->back()->with('success', 'Fatura marcada como paga e fornecedor notificado.');
    }
}
