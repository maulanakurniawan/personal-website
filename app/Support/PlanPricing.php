<?php

namespace App\Support;

use Illuminate\Support\Number;

class PlanPricing
{
    public static function format(?object $plan, ?string $locale = null): string
    {
        if (! $plan || $plan->amount === null) {
            return '—';
        }

        $amount = (float) $plan->amount;
        $currency = $plan->currency ?? 'USD';

        return Number::currency($amount, $currency, $locale ?? app()->getLocale());
    }
}
