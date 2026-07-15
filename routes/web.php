<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\CalendarActionController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\CalendarTypeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactFunctionController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrentAccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DigitalFileController;
use App\Http\Controllers\DocumentCategoryController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierInvoiceController;
use App\Http\Controllers\SupplierOrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VatRateController;
use App\Http\Controllers\ViesController;
use App\Http\Controllers\WorkOrderController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('clientes', ClientController::class)->names('clients')->parameters(['clientes' => 'entity']);
    Route::resource('fornecedores', SupplierController::class)->names('suppliers')->parameters(['fornecedores' => 'entity']);
    Route::resource('contactos', ContactController::class)->names('contacts')->parameters(['contactos' => 'contact']);
    Route::resource('artigos', ArticleController::class)->names('articles')->parameters(['artigos' => 'article']);
    Route::get('propostas/{proposal}/pdf', [ProposalController::class, 'downloadPdf'])->name('proposals.pdf');
    Route::post('propostas/{proposal}/convert-to-order', [ProposalController::class, 'convertToOrder'])->name('proposals.convert');
    Route::resource('propostas', ProposalController::class)->names('proposals');

    Route::get('encomendas/{encomenda}/pdf', [OrderController::class, 'downloadPdf'])->name('encomendas.pdf');
    Route::post('encomendas/{encomenda}/convert-to-suppliers', [OrderController::class, 'convertToSupplierOrders'])->name('encomendas.convert');
    Route::resource('encomendas', OrderController::class);

    Route::get('encomendas-fornecedores/{encomendas_fornecedore}/pdf', [SupplierOrderController::class, 'downloadPdf'])->name('supplier-orders.pdf');
    Route::resource('encomendas-fornecedores', SupplierOrderController::class)->names('supplier-orders')->parameters(['encomendas-fornecedores' => 'encomendas_fornecedore']);

    Route::resource('ordens-trabalho', WorkOrderController::class)->names('work-orders')->parameters(['ordens-trabalho' => 'work_order']);
    Route::get('logs', [LogController::class, 'index'])->name('logs.index');

    Route::post('financeiro/faturas-fornecedores/{faturas_fornecedore}/pay', [SupplierInvoiceController::class, 'pay'])->name('supplier-invoices.pay');
    Route::resource('financeiro/faturas-fornecedores', SupplierInvoiceController::class)->names('supplier-invoices')->parameters(['faturas-fornecedores' => 'faturas_fornecedore']);

    Route::resource('calendario/eventos', CalendarEventController::class)->names('calendar-events')->parameters(['eventos' => 'calendar_event']);
    Route::resource('configuracoes/financeiro/iva', VatRateController::class)->names('vat-rates')->parameters(['iva' => 'vat_rate']);
    Route::resource('configuracoes/contactos/funcoes', ContactFunctionController::class)->names('contact-functions')->parameters(['funcoes' => 'contact_function']);
    Route::resource('configuracoes/calendarios/tipos-de-eventos', CalendarTypeController::class)->names('calendar-types')->parameters(['tipos-de-eventos' => 'calendar_type']);
    Route::resource('configuracoes/calendarios/acoes-de-evento', CalendarActionController::class)->names('calendar-actions')->parameters(['acoes-de-evento' => 'calendar_action']);
    Route::resource('configuracoes/paises', CountryController::class)->names('countries')->parameters(['paises' => 'country']);
    Route::resource('configuracoes/categorias-documentos', DocumentCategoryController::class)->names('document-categories')->parameters(['categorias-documentos' => 'document_category']);
    Route::resource('financeiro/contas-bancarias', BankAccountController::class)->names('contas-bancarias')->parameters(['contas-bancarias' => 'contas_bancaria']);

    // Conta Corrente Clientes
    Route::get('financeiro/conta-corrente', [CurrentAccountController::class, 'index'])->name('conta-corrente.index');
    Route::get('financeiro/conta-corrente/{client}', [CurrentAccountController::class, 'show'])->name('conta-corrente.show');
    Route::post('financeiro/conta-corrente/{client}', [CurrentAccountController::class, 'store'])->name('conta-corrente.store');
    Route::delete('financeiro/conta-corrente/movimento/{entry}', [CurrentAccountController::class, 'destroy'])->name('conta-corrente.destroy');
    Route::resource('gestao-acessos/utilizadores', UserController::class)->names('users')->parameters(['utilizadores' => 'user']);
    Route::resource('gestao-acessos/permissoes', RoleController::class)->names('roles')->parameters(['permissoes' => 'role']);
    Route::get('api/vies/lookup', [ViesController::class, 'lookup'])->name('vies.lookup');

    // Arquivo Digital
    Route::resource('arquivo-digital', DigitalFileController::class)->names('digital-files')->parameters(['arquivo-digital' => 'digital_file']);
    Route::get('arquivo-digital/{digital_file}/download', [DigitalFileController::class, 'download'])->name('digital-files.download');

    // Configurações da Empresa
    Route::get('configuracoes/empresa', [CompanyController::class, 'edit'])->name('company.edit');
    Route::post('configuracoes/empresa', [CompanyController::class, 'update'])->name('company.update');
});

require __DIR__.'/settings.php';
