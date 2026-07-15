<?php

namespace App\Http\Controllers;

use App\Models\CurrentAccountEntry;
use App\Models\Entity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CurrentAccountController extends Controller
{
    /**
     * Display a listing of clients with their current account balances.
     */
    public function index(Request $request): Response
    {
        $query = Entity::query()->whereIn('type', ['cliente', 'ambos']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereBlind('nif', 'nif_index', $search)
                    ->orWhereBlind('email', 'email_index', $search);
            });
        }

        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');

        // Allowed sort columns
        if (in_array($sort, ['name', 'nif', 'email', 'phone'])) {
            $query->orderBy($sort, $direction);
        }

        $clients = $query->paginate($request->input('per_page', 15))
            ->withQueryString();

        // Calculate balances for the paginated clients
        $clientIds = collect($clients->items())->pluck('id');

        // Get sums of debits and credits
        $balances = CurrentAccountEntry::whereIn('entity_id', $clientIds)
            ->selectRaw("
                entity_id, 
                SUM(CASE WHEN movement_type = 'debit' THEN amount ELSE 0 END) as total_debit,
                SUM(CASE WHEN movement_type = 'credit' THEN amount ELSE 0 END) as total_credit
            ")
            ->groupBy('entity_id')
            ->get()
            ->keyBy('entity_id');

        // Attach balance to each client item
        foreach ($clients->items() as $client) {
            $balanceData = $balances->get($client->id);
            $totalDebit = $balanceData ? (float) $balanceData->total_debit : 0;
            $totalCredit = $balanceData ? (float) $balanceData->total_credit : 0;
            $client->current_balance = $totalDebit - $totalCredit;

            // Touch attributes to force decryption
            $client->nif = $client->nif;
            $client->email = $client->email;
        }

        $allClients = Entity::whereIn('type', ['cliente', 'ambos'])->orderBy('name')->get()->map(function ($client) {
            return [
                'id' => $client->id,
                'name' => $client->name,
                'nif' => $client->nif, // Accessing attribute triggers decryption
            ];
        });

        return Inertia::render('CurrentAccounts/Index', [
            'clients' => $clients,
            'allClients' => $allClients,
            'filters' => $request->only(['search', 'sort', 'direction', 'per_page']),
        ]);
    }

    /**
     * Display the current account entries for a specific client.
     */
    public function show(Entity $client, Request $request): Response
    {
        if (! in_array($client->type, ['cliente', 'ambos'])) {
            abort(404);
        }

        $entries = $client->currentAccountEntries()
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Calculate running balances
        // To do this correctly, we need to sort ascending first
        $entriesAsc = $entries->sortBy('date')->sortBy('id')->values();
        $runningBalance = 0;

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($entriesAsc as $entry) {
            if ($entry->movement_type === 'debit') {
                $runningBalance += $entry->amount;
                $totalDebit += $entry->amount;
            } else {
                $runningBalance -= $entry->amount;
                $totalCredit += $entry->amount;
            }
            $entry->running_balance = $runningBalance;
        }

        // We reverse back to descending for display
        $entries = $entriesAsc->reverse()->values();

        $client->total_debit = $totalDebit;
        $client->total_credit = $totalCredit;
        $client->current_balance = $runningBalance;

        // Touch attributes to force decryption
        $client->nif = $client->nif;

        return Inertia::render('CurrentAccounts/Show', [
            'client' => $client,
            'entries' => $entries,
        ]);
    }

    /**
     * Store a newly created current account entry.
     */
    public function store(Request $request, Entity $client)
    {
        if (! in_array($client->type, ['cliente', 'ambos'])) {
            abort(404);
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'document_type' => ['required', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'movement_type' => ['required', 'in:debit,credit'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // 5MB
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('current_accounts', 'public');
            $validated['attachment_path'] = $path;
        }

        $client->currentAccountEntries()->create($validated);

        return redirect()->back()->with('success', 'Movimento registado com sucesso.');
    }

    /**
     * Remove the specified entry.
     */
    public function destroy(CurrentAccountEntry $entry)
    {
        $entry->delete();

        return redirect()->back()->with('success', 'Movimento eliminado com sucesso.');
    }
}
