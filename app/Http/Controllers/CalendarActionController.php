<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarActionRequest;
use App\Http\Requests\UpdateCalendarActionRequest;
use App\Models\CalendarEvent;
use App\Models\CalendarAction;
use App\Traits\Searchable;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Illuminate\Http\Request;

class CalendarActionController extends Controller
{

    use Searchable;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = CalendarAction::query();

        $calendaryActions = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['name'],
            blindIndexFields: [],
            sortableFields: ['name', 'created_at'],
            defaultSort: 'created_at',
            defaultDirection: 'desc'
        );

        return Inertia::render('CalendarActions/Index', [
            'calendarActions' => $calendaryActions,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('CalendarActions/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCalendarActionRequest $request)
    {
        CalendarAction::create($request->validated());

        return redirect()->route('calendar-actions.index')->with('success', 'Ação de calendário criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CalendarAction $calendarAction)
    {
        return redirect()->route('calendar-actions.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CalendarAction $calendarAction)
    {
        return Inertia::render('CalendarActions/Edit', [
            'calendarAction' => $calendarAction,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCalendarActionRequest $request, CalendarAction $calendarAction)
    {
        $calendarAction->update($request->validated());

        return redirect()->route('calendar-actions.index')->with('success', 'Ação de calendário atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CalendarAction $calendarAction)
    {
        if (CalendarEvent::where('calendar_action_id', $calendarAction->id)->exists()) {
            return redirect()->route('calendar-actions.index')->with('error', 'Não é possível eliminar esta ação de evento porque está associada a um ou mais eventos.');
        }

        $calendarAction->delete();

        return redirect()->route('calendar-actions.index')->with('success', 'Ação de calendário eliminada com sucesso.');
    }
}
