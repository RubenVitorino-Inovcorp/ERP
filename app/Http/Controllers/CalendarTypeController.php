<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarTypeRequest;
use App\Http\Requests\UpdateCalendarTypeRequest;
use App\Models\CalendarEvent;
use App\Models\CalendarType;
use App\Traits\Searchable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarTypeController extends Controller
{
    use Searchable;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = CalendarType::query();

        $calendarTypes = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['name'],
            blindIndexFields: [],
            sortableFields: ['name', 'created_at'],
            defaultSort: 'created_at',
            defaultDirection: 'desc'
        );

        return Inertia::render('CalendarTypes/Index', [
            'calendarTypes' => $calendarTypes,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('CalendarTypes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCalendarTypeRequest $request)
    {
        CalendarType::create($request->validated());

        return redirect()->route('calendar-types.index')->with('success', 'Tipo de calendário criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CalendarType $calendarType)
    {
        return redirect()->route('calendar-types.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CalendarType $calendarType): Response
    {
        return Inertia::render('CalendarTypes/Edit', [
            'calendarType' => $calendarType,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCalendarTypeRequest $request, CalendarType $calendarType)
    {
        $calendarType->update($request->validated());

        return redirect()->route('calendar-types.index')->with('success', 'Tipo de calendário atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CalendarType $calendarType)
    {
        if (CalendarEvent::where('calendar_type_id', $calendarType->id)->exists()) {
            return redirect()->route('calendar-types.index')->with('error', 'Não é possível eliminar este tipo de calendário porque está associado a um ou mais eventos.');
        }

        $calendarType->delete();

        return redirect()->route('calendar-types.index')->with('success', 'Tipo de calendário eliminado com sucesso.');
    }
}
