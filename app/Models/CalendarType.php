<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarType extends Model
{
    /** @use HasFactory<\Database\Factories\CalendarTypeFactory> */
    use HasFactory;

    protected $fillable = ['name'];
}
