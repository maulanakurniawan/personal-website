<?php

namespace App\Console\Commands;

use App\Models\ValidationApiClient;
use Illuminate\Console\Command;

class ValidationApiClientListCommand extends Command
{
    protected $signature = 'validation-api-client:list';

    public function handle(): int
    {
        $this->table(['id', 'product_key', 'product_name', 'key_prefix', 'allowed_hosts', 'enabled', 'last_used_at', 'revoked_at', 'created_at'], ValidationApiClient::query()->orderBy('id')->get()->map(fn ($client) => [
            $client->id, $client->product_key, $client->product_name, $client->key_prefix, implode(', ', $client->allowed_hosts ?? []), $client->enabled ? 'yes' : 'no', $client->last_used_at, $client->revoked_at, $client->created_at,
        ])->all());

        return self::SUCCESS;
    }
}
