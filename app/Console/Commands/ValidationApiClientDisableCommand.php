<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidationApiClientDisableCommand extends Command
{
    use ConcernsValidationApiClient;

    protected $signature = 'validation-api-client:disable {id_or_prefix}';

    public function handle(): int
    {
        $client = $this->findClient($this->argument('id_or_prefix'));
        $client->forceFill(['enabled' => false])->save();
        $this->info('Validation API client disabled.');

        return self::SUCCESS;
    }
}
