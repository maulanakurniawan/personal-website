<?php

namespace App\Console\Commands;

use App\Models\ValidationApiClient;
use Illuminate\Console\Command;

class ValidationApiClientCreateCommand extends Command
{
    use ConcernsValidationApiClient;

    protected $signature = 'validation-api-client:create {--product-key=} {--product-name=} {--allowed-host=*} {--notes=}';

    public function handle(): int
    {
        $productKey = (string) $this->option('product-key');
        $productName = (string) $this->option('product-name');
        if ($productKey === '' || $productName === '') {
            $this->error('The --product-key and --product-name options are required.');

            return self::FAILURE;
        }

        $plainKey = $this->generatePlainApiKey();
        $hosts = array_values(array_filter($this->option('allowed-host') ?: []));
        ValidationApiClient::create([
            'product_key' => $productKey,
            'product_name' => $productName,
            ...$this->keyAttributes($plainKey),
            'allowed_hosts' => $hosts ?: null,
            'enabled' => true,
            'notes' => $this->option('notes'),
        ]);

        $this->info('Validation API client created.');
        $this->newLine();
        $this->line('Product: '.$productName);
        $this->line('Product key: '.$productKey);
        $this->line('Allowed hosts: '.($hosts ? implode(', ', $hosts) : 'Any'));
        $this->newLine();
        $this->line('Use this in the validation PHP page:');
        $this->newLine();
        $this->line('VALIDATION_API_URL=https://maulanakurniawan.com/internal/validation/v1/leads');
        $this->line('VALIDATION_API_KEY='.$plainKey);
        $this->newLine();
        $this->warn('This API key will not be shown again.');

        return self::SUCCESS;
    }
}
