<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierInvoice extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierInvoiceFactory> */
    use HasFactory;

    protected $fillable = ['number', 'invoice_date', 'due_date', 'entity_id', 'order_id', 'total_value', 'document_path', 'proof_of_payment_path', 'status'];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
