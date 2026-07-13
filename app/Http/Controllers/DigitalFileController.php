<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDigitalFileRequest;
use App\Models\DigitalFile;
use App\Models\DocumentCategory;
use App\Traits\Searchable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DigitalFileController extends Controller
{
    use Searchable;

    public function index(Request $request): Response
    {
        $query = DigitalFile::with(['category', 'uploader']);

        $digitalFiles = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['name'],
            blindIndexFields: [],
            sortableFields: ['name', 'created_at'],
            defaultSort: 'created_at',
            defaultDirection: 'desc'
        );

        $categories = DocumentCategory::where('status', true)->orderBy('name')->get();

        return Inertia::render('DigitalFiles/Index', [
            'digitalFiles' => $digitalFiles,
            'categories' => $categories,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    public function store(StoreDigitalFileRequest $request): RedirectResponse
    {
        $file = $request->file('file');

        $path = $file->store('digital-files', 'local');

        DigitalFile::create([
            'name' => $request->name,
            'document_category_id' => $request->document_category_id,
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('digital-files.index')->with('success', 'Documento carregado com sucesso.');
    }

    public function destroy(DigitalFile $digitalFile): RedirectResponse
    {
        Storage::disk('local')->delete($digitalFile->file_path);
        $digitalFile->delete();

        return redirect()->route('digital-files.index')->with('success', 'Documento eliminado.');
    }

    /**
     * Download seguro do ficheiro (protegido por autenticação).
     */
    public function download(DigitalFile $digitalFile)
    {
        $path = Storage::disk('local')->path($digitalFile->file_path);

        if (!file_exists($path)) {
            abort(404, 'O ficheiro não foi encontrado no servidor.');
        }

        $extension = pathinfo($digitalFile->file_path, PATHINFO_EXTENSION);
        $downloadName = $digitalFile->name . '.' . $extension;

        return response()->download($path, $downloadName);
    }
}
