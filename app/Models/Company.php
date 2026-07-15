<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class Company extends Model implements CipherSweetEncrypted
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, UsesCipherSweet;

    protected $fillable = ['name', 'address', 'zip_code', 'city', 'nif', 'logo_path'];

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('nif')
            ->addBlindIndex('nif', new BlindIndex('nif_index'));
    }
}
