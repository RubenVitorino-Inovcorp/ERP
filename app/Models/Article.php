<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\CipherSweet\BlindIndex;

class Article extends Model implements CipherSweetEncrypted
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory, UsesCipherSweet;

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
