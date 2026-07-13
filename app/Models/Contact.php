<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\CipherSweet\BlindIndex;

class Contact extends Model implements CipherSweetEncrypted
{
    /** @use HasFactory<\Database\Factories\ContactFactory> */
    use HasFactory, UsesCipherSweet;

    protected $fillable = ['number', 'entity_id', 'name', 'last_name', 'contact_function_id', 'phone', 'mobile', 'email', 'gdpr_consent', 'notes', 'status'];

    protected $casts = [
        'gdpr_consent' => 'boolean',
        'status' => 'boolean',
    ];

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('email')->addBlindIndex('email', new BlindIndex('email_index'))
            ->addOptionalTextField('phone')->addBlindIndex('phone', new BlindIndex('phone_index'))
            ->addOptionalTextField('mobile')->addBlindIndex('mobile', new BlindIndex('mobile_index'))
            ->addOptionalTextField('notes');
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function contactFunction()
    {
        return $this->belongsTo(ContactFunction::class);
    }
}
