<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentCategory extends Model
{
    protected $fillable = ['name', 'status'];

    public function digitalFiles()
    {
        return $this->hasMany(DigitalFile::class);
    }
}
