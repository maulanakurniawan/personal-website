<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class InternalAdminClient extends Model
{
    protected $fillable = ['name', 'client_id', 'client_secret_hash', 'scopes', 'allowed_ips', 'is_active', 'last_used_at', 'last_used_ip', 'revoked_at'];

    protected $hidden = ['client_secret_hash'];

    protected $casts = [
        'scopes' => 'array',
        'allowed_ips' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function secretMatches(string $secret): bool
    {
        return Hash::check($secret, $this->client_secret_hash);
    }

    public function hasScope(string $scope): bool
    {
        $scopes = $this->scopes ?: [];
        return in_array('*', $scopes, true) || in_array($scope, $scopes, true);
    }
}
