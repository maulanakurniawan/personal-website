<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidationApiClientRevokeCommand extends Command
{
    use ConcernsValidationApiClient;

    protected $signature = 'validation-api-client:revoke {id_or_prefix}';

    public function handle(): int
    {
        $client = $this->findClient($this->argument('id_or_prefix'));
        $client->forceFill(['enabled' => false, 'revoked_at' => now()])->save();
        $this->info('Validation API client revoked.');

        return self::SUCCESS;
    }
}
