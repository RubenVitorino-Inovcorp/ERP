<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalLine extends Model
{
    /** @use HasFactory<\Database\Factories\ProposalLineFactory> */
    use HasFactory;

    protected $fillable = ['proposal_id', 'article_id', 'supplier_id', 'cost_price', 'quantity', 'unit_price', 'vat_rate_id'];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Entity::class, 'supplier_id');
    }

    public function vatRate()
    {
        return $this->belongsTo(VatRate::class);
    }
}
