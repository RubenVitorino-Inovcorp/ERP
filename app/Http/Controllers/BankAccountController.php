<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBankAccountRequest;
use App\Http\Requests\UpdateBankAccountRequest;
use App\Models\BankAccount;
use App\Traits\Searchable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountController extends Controller
{
    use Searchable;

    public function index(Request $request): Response
    {
        $query = BankAccount::query();

        $bankAccounts = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['name', 'bank_name', 'iban', 'swift'],
            sortableFields: ['name', 'bank_name', 'status', 'created_at'],
            defaultSort: 'created_at',
            defaultDirection: 'desc'
        );

        return Inertia::render('BankAccounts/Index', [
            'bankAccounts' => $bankAccounts,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('BankAccounts/Create');
    }

    public function store(StoreBankAccountRequest $request): RedirectResponse
    {
        BankAccount::create($request->validated());

        return redirect()->route('contas-bancarias.index')->with('success', 'Conta bancária criada com sucesso.');
    }

    public function edit(BankAccount $contas_bancaria): Response
    {
        return Inertia::render('BankAccounts/Edit', [
            'bankAccount' => $contas_bancaria,
        ]);
    }

    public function update(UpdateBankAccountRequest $request, BankAccount $contas_bancaria): RedirectResponse
    {
        $contas_bancaria->update($request->validated());

        return redirect()->route('contas-bancarias.index')->with('success', 'Conta bancária atualizada com sucesso.');
    }

    public function destroy(BankAccount $contas_bancaria): RedirectResponse
    {
        try {
            $contas_bancaria->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return redirect()->route('contas-bancarias.index')->with('error', 'Não é possível eliminar esta conta bancária pois tem registos associados.');
            }
            return redirect()->route('contas-bancarias.index')->with('error', 'Ocorreu um erro ao eliminar a conta bancária.');
        }

        return redirect()->route('contas-bancarias.index')->with('success', 'Conta bancária eliminada com sucesso.');
    }
}
