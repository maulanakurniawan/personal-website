<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidationApiClientEnableCommand extends Command
{
    use ConcernsValidationApiClient;

    protected $signature = 'validation-api-client:enable {id_or_prefix}';

    public function handle(): int
    {
        $client = $this->findClient($this->argument('id_or_prefix'));
        $client->forceFill(['enabled' => true])->save();
        $this->info('Validation API client enabled.');

        return self::SUCCESS;
    }
}
