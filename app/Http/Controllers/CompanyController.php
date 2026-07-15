<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Show the form for editing the company settings.
     */
    public function edit(): Response
    {
        // Só há uma empresa no sistema
        $company = Company::first();

        if (! $company) {
            $company = new Company;
        }

        return Inertia::render('Company/Edit', [
            'company' => $company,
        ]);
    }

    /**
     * Update the company settings.
     */
    public function update(UpdateCompanyRequest $request): RedirectResponse
    {
        $company = Company::first();

        if (! $company) {
            $company = new Company;
        }

        $company->name = $request->name;
        $company->address = $request->address;
        $company->zip_code = $request->zip_code;
        $company->city = $request->city;
        $company->nif = $request->nif;

        // Processar carregamento do novo logo, se enviado
        if ($request->hasFile('logo')) {
            // Eliminar logo antigo do disco público se existir
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }

            $path = $request->file('logo')->store('logos', 'public');
            $company->logo_path = $path;
        }

        $company->save();

        return redirect()->route('company.edit')->with('success', 'Definições da empresa atualizadas com sucesso.');
    }
}
