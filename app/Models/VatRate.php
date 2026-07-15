<?php

namespace App\Models;

use Database\Factories\VatRateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VatRate extends Model
{
    /** @use HasFactory<VatRateFactory> */
    use HasFactory;

    protected $fillable = ['name', 'value'];
}
