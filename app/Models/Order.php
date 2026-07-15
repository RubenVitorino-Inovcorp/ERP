<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $fillable = ['type', 'number', 'order_date', 'entity_id', 'proposal_id', 'status'];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function lines()
    {
        return $this->hasMany(OrderLine::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}
