<?php

namespace App\Models;

use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class Contact extends Model implements CipherSweetEncrypted
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory, LogsActivity, UsesCipherSweet;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

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
