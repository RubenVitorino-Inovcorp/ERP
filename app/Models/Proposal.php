<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    /** @use HasFactory<\Database\Factories\ProposalFactory> */
    use HasFactory;

    protected $fillable = ['number', 'proposal_date', 'entity_id', 'validity_date', 'status'];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function lines()
    {
        return $this->hasMany(ProposalLine::class);
    }
}
