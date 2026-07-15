<?php

namespace App\Models;

use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class Article extends Model implements CipherSweetEncrypted
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, LogsActivity, UsesCipherSweet;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $fillable = ['reference', 'name', 'description', 'price', 'vat_rate_id', 'photo_path', 'notes', 'status'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow->addOptionalTextField('notes');
    }

    public function vatRate()
    {
        return $this->belongsTo(VatRate::class);
    }
}
