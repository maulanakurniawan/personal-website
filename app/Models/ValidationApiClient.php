<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidationApiClient extends Model
{
    protected $fillable = [
        'product_key',
        'product_name',
        'key_prefix',
        'key_hash',
        'allowed_hosts',
        'enabled',
        'last_used_at',
        'revoked_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'allowed_hosts' => 'array',
            'enabled' => 'boolean',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }
}
