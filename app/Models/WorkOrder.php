<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class WorkOrder extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $fillable = [
        'number',
        'entity_id',
        'order_id',
        'calendar_event_id',
        'description',
        'priority',
        'status',
        'expected_duration',
        'actual_duration',
        'notes',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function calendarEvent()
    {
        return $this->belongsTo(CalendarEvent::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_work_order');
    }
}
