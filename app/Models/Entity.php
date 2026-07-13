<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\CipherSweet\BlindIndex;

class Entity extends Model implements CipherSweetEncrypted
{
    /** @use HasFactory<\Database\Factories\EntityFactory> */
    use HasFactory, UsesCipherSweet;

    protected $fillable = ['type', 'number', 'nif', 'name', 'address', 'zip_code', 'city', 'country_id', 'phone', 'mobile', 'website', 'email', 'gdpr_consent', 'notes', 'status'];

    protected $casts = [
        'gdpr_consent' => 'boolean',
        'status' => 'boolean',
    ];

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('nif')->addBlindIndex('nif', new BlindIndex('nif_index'))
            ->addField('email')->addBlindIndex('email', new BlindIndex('email_index'))
            ->addOptionalTextField('phone')->addBlindIndex('phone', new BlindIndex('phone_index'))
            ->addOptionalTextField('mobile')->addBlindIndex('mobile', new BlindIndex('mobile_index'))
            ->addOptionalTextField('notes');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
