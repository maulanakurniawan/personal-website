<?php

namespace App\Console\Commands;

use App\Models\ValidationApiClient;
use Illuminate\Support\Str;

trait ConcernsValidationApiClient
{
    protected function findClient(string $idOrPrefix): ValidationApiClient
    {
        return ValidationApiClient::query()
            ->where('id', $idOrPrefix)
            ->orWhere('key_prefix', $idOrPrefix)
            ->firstOrFail();
    }

    protected function generatePlainApiKey(): string
    {
        return 'vapi_'.Str::random(64);
    }

    protected function keyAttributes(string $plainKey): array
    {
        return ['key_prefix' => substr($plainKey, 0, 12), 'key_hash' => hash('sha256', $plainKey)];
    }
}
