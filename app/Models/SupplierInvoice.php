<?php

namespace App\Models;

use Database\Factories\SupplierInvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class SupplierInvoice extends Model
{
    /** @use HasFactory<SupplierInvoiceFactory> */
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $fillable = ['number', 'invoice_date', 'due_date', 'entity_id', 'order_id', 'total_value', 'document_path', 'proof_of_payment_path', 'status'];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function digitalFiles()
    {
        return $this->morphMany(DigitalFile::class, 'fileable');
    }
}
