<?php

namespace App\Models;

use Database\Factories\ProposalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Proposal extends Model
{
    /** @use HasFactory<ProposalFactory> */
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

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
