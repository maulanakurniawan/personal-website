<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidationApiClientRotateCommand extends Command
{
    use ConcernsValidationApiClient;

    protected $signature = 'validation-api-client:rotate {id_or_prefix}';

    public function handle(): int
    {
        $client = $this->findClient($this->argument('id_or_prefix'));
        $plainKey = $this->generatePlainApiKey();
        $client->forceFill($this->keyAttributes($plainKey))->save();
        $this->info('Validation API client key rotated.');
        $this->line('VALIDATION_API_KEY='.$plainKey);
        $this->warn('This API key will not be shown again.');

        return self::SUCCESS;
    }
}
