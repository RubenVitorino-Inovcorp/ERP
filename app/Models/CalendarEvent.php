<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    /** @use HasFactory<\Database\Factories\CalendarEventFactory> */
    use HasFactory;

    protected $fillable = ['date', 'time', 'duration', 'entity_id', 'calendar_type_id', 'calendar_action_id', 'description', 'status'];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function calendarType()
    {
        return $this->belongsTo(CalendarType::class);
    }

    public function calendarAction()
    {
        return $this->belongsTo(CalendarAction::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'calendar_event_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Partilha (para:)
     */
    public function to()
    {
        return $this->users()->wherePivot('role', 'partilha');
    }

    /**
     * Conhecimento (CC:)
     */
    public function carbonCopy()
    {
        return $this->users()->wherePivot('role', 'conhecimento');
    }
}
