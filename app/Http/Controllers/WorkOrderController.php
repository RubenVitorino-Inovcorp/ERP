<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkOrderRequest;
use App\Http\Requests\UpdateWorkOrderRequest;
use App\Models\CalendarAction;
use App\Models\CalendarEvent;
use App\Models\CalendarType;
use App\Models\Entity;
use App\Models\Order;
use App\Models\User;
use App\Models\WorkOrder;
use App\Traits\Searchable;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkOrderController extends Controller
{
    use Searchable;

    public function index(Request $request)
    {
        $query = WorkOrder::with(['entity:id,name', 'order:id,number', 'users:id,name']);

        $workOrders = $this->applyDataTable(
            query: $query,
            request: $request,
            searchableFields: ['number', 'status'],
            sortableFields: ['number', 'created_at', 'status', 'expected_duration'],
            defaultSort: 'created_at',
            defaultDirection: 'desc'
        );

        // Map relationships directly in the collection to match what DataTable expects if needed,
        // though inertia can just use workOrder.entity.name directly.

        return Inertia::render('WorkOrders/Index', [
            'workOrders' => $workOrders,
            'filters' => $this->getDataTableFilters($request),
        ]);
    }

    public function create(Request $request)
    {
        $entities = Entity::where('status', true)->orderBy('name')->get(['id', 'name', 'nif', 'type']);
        $orders = Order::orderBy('number', 'desc')->get(['id', 'number', 'entity_id']);
        $users = User::where('status', true)->orderBy('name')->get(['id', 'name']);

        $calendarTypes = CalendarType::orderBy('name')->get();
        $calendarActions = CalendarAction::orderBy('name')->get();

        return Inertia::render('WorkOrders/Create', [
            'entities' => $entities,
            'orders' => $orders,
            'users' => $users,
            'calendarTypes' => $calendarTypes,
            'calendarActions' => $calendarActions,
            'initialOrderId' => $request->query('order_id'),
        ]);
    }

    public function store(StoreWorkOrderRequest $request)
    {
        $data = $request->validated();

        $year = date('Y');
        $lastOT = WorkOrder::where('number', 'like', "OT {$year}/%")->orderBy('id', 'desc')->first();
        $sequence = $lastOT ? intval(substr($lastOT->number, -3)) + 1 : 1;
        $number = "OT {$year}/".str_pad($sequence, 3, '0', STR_PAD_LEFT);

        $calendarEventId = null;
        if (! empty($data['create_calendar_event']) && ! empty($data['date']) && ! empty($data['time'])) {
            $event = CalendarEvent::create([
                'date' => $data['date'],
                'time' => $data['time'],
                'duration' => $data['expected_duration'] ?? 60,
                'entity_id' => $data['entity_id'],
                'calendar_type_id' => $data['calendar_type_id'],
                'calendar_action_id' => $data['calendar_action_id'],
                'description' => 'OT: '.$data['description'],
                'status' => 'agendado',
            ]);

            if (! empty($data['technicians'])) {
                $event->users()->attach($data['technicians'], ['role' => 'partilha']);
            }

            $calendarEventId = $event->id;
        }

        $workOrder = WorkOrder::create([
            'number' => $number,
            'entity_id' => $data['entity_id'],
            'order_id' => $data['order_id'] ?? null,
            'calendar_event_id' => $calendarEventId,
            'description' => $data['description'],
            'priority' => $data['priority'],
            'status' => $data['status'],
            'expected_duration' => $data['expected_duration'] ?? null,
            'actual_duration' => $data['actual_duration'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        if (! empty($data['technicians'])) {
            $workOrder->users()->attach($data['technicians']);
        }

        return redirect()->route('work-orders.index')->with('success', 'Ordem de Trabalho criada com sucesso.');
    }

    public function edit(WorkOrder $work_order)
    {
        $work_order->load(['users', 'calendarEvent']);

        $entities = Entity::where('status', true)->orderBy('name')->get(['id', 'name', 'nif', 'type']);
        $orders = Order::orderBy('number', 'desc')->get(['id', 'number', 'entity_id']);
        $users = User::where('status', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('WorkOrders/Edit', [
            'workOrder' => $work_order,
            'entities' => $entities,
            'orders' => $orders,
            'users' => $users,
        ]);
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $work_order)
    {
        $data = $request->validated();

        $work_order->update([
            'entity_id' => $data['entity_id'],
            'order_id' => $data['order_id'] ?? null,
            'description' => $data['description'],
            'priority' => $data['priority'],
            'status' => $data['status'],
            'expected_duration' => $data['expected_duration'] ?? null,
            'actual_duration' => $data['actual_duration'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        if (isset($data['technicians'])) {
            $work_order->users()->sync($data['technicians']);
        } else {
            $work_order->users()->detach();
        }

        return redirect()->route('work-orders.index')->with('success', 'Ordem de Trabalho atualizada com sucesso.');
    }

    public function destroy(WorkOrder $work_order)
    {
        $work_order->delete();

        return redirect()->route('work-orders.index')->with('success', 'Ordem de Trabalho eliminada com sucesso.');
    }
}
