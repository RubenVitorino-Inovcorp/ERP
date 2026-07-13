<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

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
}
