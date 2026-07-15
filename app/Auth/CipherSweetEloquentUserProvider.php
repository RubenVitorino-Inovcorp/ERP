<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class CipherSweetEloquentUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by the given credentials.
     *
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials)
    {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
             array_key_exists('password', $credentials))) {
            return null;
        }

        $query = $this->newModelQuery();

        foreach ($credentials as $key => $value) {
            if (str_contains($key, 'password')) {
                continue;
            }

            $model = $this->createModel();
            if ($model instanceof CipherSweetEncrypted) {
                $encryptedRow = $model::getCipherSweetEncryptedRow();
                if (in_array($key, $encryptedRow->listEncryptedFields())) {
                    $indexName = $key.'_index';
                    $query->whereBlind($key, $indexName, $value);

                    continue;
                }
            }

            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }
}
