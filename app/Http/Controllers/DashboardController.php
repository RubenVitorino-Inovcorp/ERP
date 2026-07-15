<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\Order;
use App\Models\Proposal;
use App\Models\SupplierInvoice;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $totalClients = Entity::whereIn('type', ['cliente', 'ambos'])->count();
        $totalSuppliers = Entity::whereIn('type', ['fornecedor', 'ambos'])->count();

        $proposalsDraft = Proposal::where('status', 'rascunho')->count();
        $proposalsClosed = Proposal::where('status', 'fechado')->count();

        $ordersDraft = Order::where('type', 'cliente')->where('status', 'rascunho')->count();
        $ordersClosed = Order::where('type', 'cliente')->where('status', 'fechado')->count();

        $invoicesPending = SupplierInvoice::where('status', 'pendente')->count();
        $invoicesPaid = SupplierInvoice::where('status', 'paga')->count();
        $invoicesPendingTotal = SupplierInvoice::where('status', 'pendente')->sum('total_value');

        $workOrdersPending = WorkOrder::where('status', 'pendente')->count();
        $workOrdersInProgress = WorkOrder::where('status', 'em_curso')->count();
        $workOrdersCompleted = WorkOrder::where('status', 'concluida')->count();

        // Últimas propostas
        $recentProposals = Proposal::with('entity')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Proposal $p) => [
                'id' => $p->id,
                'number' => $p->number,
                'entity_name' => $p->entity?->name ?? '-',
                'status' => $p->status,
                'date' => $p->proposal_date,
                'total' => $p->lines->sum(fn ($l) => $l->quantity * $l->unit_price),
            ]);

        // Últimas encomendas
        $recentOrders = Order::with('entity')
            ->where('type', 'cliente')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'number' => $o->number,
                'entity_name' => $o->entity?->name ?? '-',
                'status' => $o->status,
                'date' => $o->order_date,
                'total' => $o->lines->sum(fn ($l) => $l->quantity * $l->unit_price),
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'clients' => $totalClients,
                'suppliers' => $totalSuppliers,
                'proposals' => ['draft' => $proposalsDraft, 'closed' => $proposalsClosed],
                'orders' => ['draft' => $ordersDraft, 'closed' => $ordersClosed],
                'invoices' => [
                    'pending' => $invoicesPending,
                    'paid' => $invoicesPaid,
                    'pending_total' => $invoicesPendingTotal,
                ],
                'workOrders' => [
                    'pending' => $workOrdersPending,
                    'in_progress' => $workOrdersInProgress,
                    'completed' => $workOrdersCompleted,
                ],
            ],
            'recentProposals' => $recentProposals,
            'recentOrders' => $recentOrders,
        ]);
    }
}
