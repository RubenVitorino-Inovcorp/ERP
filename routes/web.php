<?php
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('clientes', App\Http\Controllers\ClientController::class)->names('clients')->parameters(['clientes' => 'entity']);
    Route::resource('fornecedores', App\Http\Controllers\SupplierController::class)->names('suppliers')->parameters(['fornecedores' => 'entity']);
    Route::resource('contactos', App\Http\Controllers\ContactController::class)->names('contacts')->parameters(['contactos' => 'contact']);
    Route::resource('artigos', App\Http\Controllers\ArticleController::class)->names('articles')->parameters(['artigos' => 'article']);
    Route::get('propostas/{proposal}/pdf', [App\Http\Controllers\ProposalController::class, 'downloadPdf'])->name('proposals.pdf');
    Route::post('propostas/{proposal}/convert', [App\Http\Controllers\ProposalController::class, 'convertToOrder'])->name('proposals.convert');
    Route::resource('propostas', App\Http\Controllers\ProposalController::class)->names('proposals')->parameters(['propostas' => 'proposal']);
    Route::resource('calendario/eventos', App\Http\Controllers\CalendarEventController::class)->names('calendar-events')->parameters(['eventos' => 'calendar_event']);
    Route::resource('configuracoes/financeiro/iva', App\Http\Controllers\VatRateController::class)->names('vat-rates')->parameters(['iva' => 'vat_rate']);
    Route::resource('configuracoes/contactos/funcoes', App\Http\Controllers\ContactFunctionController::class)->names('contact-functions')->parameters(['funcoes' => 'contact_function']);
    Route::resource('configuracoes/calendarios/tipos-de-eventos', App\Http\Controllers\CalendarTypeController::class)->names('calendar-types')->parameters(['tipos-de-eventos' => 'calendar_type']);
    Route::resource('configuracoes/calendarios/acoes-de-evento', App\Http\Controllers\CalendarActionController::class)->names('calendar-actions')->parameters(['acoes-de-evento' => 'calendar_action']);
    Route::resource('configuracoes/paises', App\Http\Controllers\CountryController::class)->names('countries')->parameters(['paises' => 'country']);
    Route::resource('configuracoes/categorias-documentos', App\Http\Controllers\DocumentCategoryController::class)->names('document-categories')->parameters(['categorias-documentos' => 'document_category']);

    Route::resource('gestao-acessos/utilizadores', App\Http\Controllers\UserController::class)->names('users')->parameters(['utilizadores' => 'user']);
    Route::resource('gestao-acessos/permissoes', App\Http\Controllers\RoleController::class)->names('roles')->parameters(['permissoes' => 'role']);
    Route::get('api/vies/lookup', [App\Http\Controllers\ViesController::class, 'lookup'])->name('vies.lookup');

    // Arquivo Digital
    Route::resource('arquivo-digital', App\Http\Controllers\DigitalFileController::class)->names('digital-files')->parameters(['arquivo-digital' => 'digital_file']);
    Route::get('arquivo-digital/{digital_file}/download', [App\Http\Controllers\DigitalFileController::class, 'download'])->name('digital-files.download');

    // Configurações da Empresa
    Route::get('configuracoes/empresa', [App\Http\Controllers\CompanyController::class, 'edit'])->name('company.edit');
    Route::post('configuracoes/empresa', [App\Http\Controllers\CompanyController::class, 'update'])->name('company.update');
});

require __DIR__.'/settings.php';

