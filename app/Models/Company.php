<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\CipherSweet\BlindIndex;

class Company extends Model implements CipherSweetEncrypted
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory, UsesCipherSweet;

    protected $fillable = ['name', 'address', 'zip_code', 'city', 'nif', 'logo_path'];

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('nif')
            ->addBlindIndex('nif', new BlindIndex('nif_index'));
    }
}
