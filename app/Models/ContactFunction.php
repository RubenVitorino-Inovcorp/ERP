<?php

namespace App\Models;

use Database\Factories\ContactFunctionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactFunction extends Model
{
    /** @use HasFactory<ContactFunctionFactory> */
    use HasFactory;

    protected $fillable = ['name'];
}
