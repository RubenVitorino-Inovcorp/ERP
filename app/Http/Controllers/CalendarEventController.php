<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarEventRequest;
use App\Http\Requests\UpdateCalendarEventRequest;
use App\Models\CalendarAction;
use App\Models\CalendarEvent;
use App\Models\CalendarType;
use App\Models\Entity;
use App\Models\User;
use App\Traits\Searchable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarEventController extends Controller
{
    use Searchable;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = CalendarEvent::with(['entity', 'calendarType', 'calendarAction', 'users']);

        if ($request->filled('user_id') && $request->user_id !== 'todos') {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('users.id', $request->user_id);
            });
        }

        if ($request->filled('entity_id') && $request->entity_id !== 'todas') {
            $query->where('entity_id', $request->entity_id);
        }

        $rawEvents = $query->get();

        $events = $rawEvents->map(function ($event) {
            $start = $event->date.'T'.$event->time;
            $end = null;
            if ($event->duration) {
                $end = Carbon::parse($start)->addMinutes($event->duration)->format('Y-m-d\TH:i:s');
            }

            return [
                'id' => $event->id,
                'title' => $event->description ?: ($event->calendarType->name ?? 'Evento'),
                'start' => $start,
                'end' => $end,
                'allDay' => false,
                // Classes consoante o estado do evento (opcional mas fica fixe)
                'classNames' => $event->status === 'cancelado' ? ['opacity-50', 'line-through'] : [],
                'raw' => [
                    'id' => $event->id,
                    'date' => $event->date,
                    'time' => $event->time,
                    'duration' => $event->duration,
                    'entity_id' => $event->entity_id,
                    'calendar_type_id' => $event->calendar_type_id,
                    'calendar_action_id' => $event->calendar_action_id,
                    'description' => $event->description,
                    'status' => $event->status,
                    'partilha' => $event->users->where('pivot.role', 'partilha')->pluck('id')->toArray(),
                    'conhecimento' => $event->users->where('pivot.role', 'conhecimento')->pluck('id')->toArray(),
                ],
            ];
        });

        return Inertia::render('CalendarEvents/Index', [
            'events' => $events,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'entities' => Entity::where('status', true)->orderBy('name')->get(['id', 'name']),
            'calendarTypes' => CalendarType::orderBy('name')->get(['id', 'name']),
            'calendarActions' => CalendarAction::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        // Já não é usado porque usamos a Sheet no Index, mas mantemos
        return Inertia::render('CalendarEvents/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCalendarEventRequest $request)
    {
        $event = CalendarEvent::create($request->safe()->except(['partilha', 'conhecimento']));

        $syncData = [];
        if ($request->has('partilha') && is_array($request->partilha)) {
            foreach ($request->partilha as $userId) {
                $syncData[$userId] = ['role' => 'partilha'];
            }
        }
        if ($request->has('conhecimento') && is_array($request->conhecimento)) {
            foreach ($request->conhecimento as $userId) {
                $syncData[$userId] = ['role' => 'conhecimento'];
            }
        }
        $event->users()->sync($syncData);

        return redirect()->route('calendar-events.index')->with('success', 'Evento de calendário criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CalendarEvent $calendarEvent)
    {
        // Já não é usado (tudo no Index), mas mantemos por segurança
        return Inertia::render('CalendarEvents/Show', [
            'calendarEvent' => $calendarEvent->load(['calendarAction', 'calendarType', 'entity', 'users']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CalendarEvent $calendarEvent, Request $request)
    {
        // Já não é usado
        return Inertia::render('CalendarEvents/Edit', [
            'calendarEvent' => $calendarEvent,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCalendarEventRequest $request, CalendarEvent $calendarEvent)
    {
        $calendarEvent->update($request->safe()->except(['partilha', 'conhecimento']));

        $syncData = [];
        if ($request->has('partilha') && is_array($request->partilha)) {
            foreach ($request->partilha as $userId) {
                $syncData[$userId] = ['role' => 'partilha'];
            }
        }
        if ($request->has('conhecimento') && is_array($request->conhecimento)) {
            foreach ($request->conhecimento as $userId) {
                $syncData[$userId] = ['role' => 'conhecimento'];
            }
        }
        $calendarEvent->users()->sync($syncData);

        return redirect()->route('calendar-events.index')->with('success', 'Evento de calendário atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CalendarEvent $calendarEvent)
    {
        $calendarEvent->delete();

        return redirect()->route('calendar-events.index')->with('success', 'Evento de calendário eliminado com sucesso.');
    }
}
