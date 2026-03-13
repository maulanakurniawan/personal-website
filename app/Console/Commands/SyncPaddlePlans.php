<?php

namespace App\Console\Commands;

use App\Models\Plan;
use Illuminate\Console\Command;
use Paddle\SDK\Client;
use Paddle\SDK\Environment;
use Paddle\SDK\Options;
use Paddle\SDK\Resources\Prices\Operations\ListPrices;
use Paddle\SDK\Resources\Shared\Operations\List\Pager;

class SyncPaddlePlans extends Command
{
    protected $signature = 'paddle:sync-plans';

    protected $description = 'Sync Paddle plan pricing into the plans table';

    public function handle(): int
    {
        $apiKey = config('paddle.api_key');
        $productId = config('paddle.product_id');
        $priceIds = config('paddle.prices', []);

        if (! $apiKey || ! $productId) {
            $this->error('Missing Paddle configuration (api key or product id).');

            return self::FAILURE;
        }

        if (empty(array_filter($priceIds))) {
            $this->error('Missing Paddle price IDs in configuration.');

            return self::FAILURE;
        }

        $environment = config('paddle.environment') === 'production'
            ? Environment::PRODUCTION
            : Environment::SANDBOX;

        $paddle = new Client($apiKey, options: new Options($environment));

        try {
            $prices = collect(iterator_to_array($paddle->prices->list(new ListPrices(
                pager: new Pager(perPage: 100),
                productIds: [$productId],
                recurring: true,
            ))));
        } catch (\Throwable $e) {
            $this->error('Failed to fetch prices from Paddle.');
            $this->line($e->getMessage());

            return self::FAILURE;
        }

        if ($prices->isEmpty()) {
            $this->warn('No prices returned from Paddle.');

            return self::SUCCESS;
        }

        $synced = [];
        foreach ($priceIds as $internalCode => $priceId) {
            if (! $priceId) {
                $this->warn("Missing price ID for {$internalCode}.");
                continue;
            }

            $price = $prices->firstWhere('id', $priceId);
            if (! $price) {
                $this->warn("Price {$priceId} not found in Paddle response.");
                continue;
            }

            if (! $this->isMonthlyPrice($price->billingCycle?->interval?->getValue(), $price->billingCycle?->frequency)) {
                $this->warn("Price {$priceId} is not monthly recurring.");
                continue;
            }

            $plan = Plan::updateOrCreate(
                ['internal_code' => $internalCode],
                [
                    'paddle_product_id' => $productId,
                    'paddle_price_id' => $priceId,
                    'name' => $price->name ?: $internalCode,
                    'currency' => $price->unitPrice->currencyCode?->getValue() ?? 'USD',
                    'amount' => $this->normalizeAmount($price->unitPrice->amount),
                    'billing_interval' => 'month',
                    'active' => $price->status->getValue() === 'active',
                ]
            );

            $synced[] = $plan;
        }

        if (empty($synced)) {
            $this->warn('No plans were synced.');

            return self::SUCCESS;
        }

        $this->table(
            ['Plan Code', 'Plan Name', 'Paddle Price', 'Amount', 'Currency', 'Active'],
            collect($synced)->map(function (Plan $plan) {
                return [
                    $plan->internal_code,
                    $plan->name,
                    $plan->paddle_price_id,
                    $plan->amount,
                    $plan->currency,
                    $plan->active ? 'yes' : 'no',
                ];
            })->all()
        );

        return self::SUCCESS;
    }

    private function isMonthlyPrice(?string $interval, ?int $frequency): bool
    {
        return $interval === 'month' && $frequency === 1;
    }

    private function normalizeAmount(mixed $amount): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        if (is_numeric($amount)) {
            return number_format(((float) $amount) / 100, 2, '.', '');
        }

        return null;
    }
}
