<?php

namespace App\Models;

use Database\Factories\CurrentAccountEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrentAccountEntry extends Model
{
    /** @use HasFactory<CurrentAccountEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'date',
        'document_type',
        'document_number',
        'description',
        'movement_type',
        'amount',
        'attachment_path',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the entity that this entry belongs to.
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }
}
