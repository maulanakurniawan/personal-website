<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\PaddleSubscriptionRefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function checkout(Request $request, string $plan): View|RedirectResponse
    {
        $plan = strtolower($plan);
        if (! in_array($plan, ['starter', 'pro'], true)) {
            abort(404);
        }

        $planRecord = Plan::where('internal_code', $plan)->first();
        $priceId = $planRecord?->paddle_price_id ?? config("paddle.prices.{$plan}");

        if (! $priceId) {
            return back()->with('error', 'Pricing is not available yet. Please contact support.');
        }

        $clientToken = config('paddle.client_side_token');
        if (! $clientToken) {
            return back()->with('error', 'Checkout is not configured yet. Please contact support.');
        }

        return view('billing.checkout', [
            'clientToken' => $clientToken,
            'environment' => config('paddle.environment', 'sandbox'),
            'priceId' => $priceId,
            'plan' => $plan,
            'customerEmail' => $request->user()->email,
            'userId' => $request->user()->id,
            'successUrl' => route('billing.success'),
            'cancelUrl' => route('billing.cancel'),
        ]);
    }

    public function success(): View
    {
        return view('billing.success');
    }

    public function cancel(): View
    {
        return view('billing.cancel');
    }

    public function history(Request $request): View
    {
        $subscriptions = $request->user()
            ->subscriptions()
            ->latest('created_at')
            ->get();

        $planRecords = Plan::whereIn('internal_code', $subscriptions->pluck('plan')->filter())
            ->get()
            ->keyBy('internal_code');

        $availablePlans = Plan::whereIn('internal_code', ['starter', 'pro'])
            ->get()
            ->keyBy('internal_code');

        $changePlanOptions = collect(['starter', 'pro'])
            ->map(function (string $plan) use ($availablePlans) {
                $planRecord = $availablePlans->get($plan);
                $priceId = $planRecord?->paddle_price_id ?? config("paddle.prices.{$plan}");

                if (! $priceId) {
                    return null;
                }

                return [
                    'code' => $plan,
                    'name' => $planRecord?->name ?? ucfirst($plan),
                    'priceId' => $priceId,
                ];
            })
            ->filter()
            ->values();

        return view('billing.history', [
            'subscriptions' => $subscriptions,
            'planRecords' => $planRecords,
            'activeSubscription' => $request->user()->activeSubscription()->first(),
            'changePlanOptions' => $changePlanOptions,
        ]);
    }

    public function previewSubscriptionChange(Request $request): View|RedirectResponse
    {
        $plan = strtolower((string) $request->input('plan', ''));
        if (! in_array($plan, ['starter', 'pro'], true)) {
            return back()->with('error', 'Please select a valid plan to continue.');
        }

        $subscription = $request->user()->activeSubscription()->first();
        if (! $subscription) {
            return back()->with('error', 'No active subscription found to change.');
        }

        if (! $subscription->paddle_subscription_id) {
            return back()->with('error', 'Subscription details are missing. Please contact support.');
        }

        if ($subscription->plan === $plan) {
            return back()->with('error', 'You are already on that plan.');
        }

        $planRecord = Plan::where('internal_code', $plan)->first();
        $priceId = $planRecord?->paddle_price_id ?? config("paddle.prices.{$plan}");

        if (! $priceId) {
            return back()->with('error', 'Pricing is not available yet. Please contact support.');
        }

        $apiKey = config('paddle.api_key');
        if (! $apiKey) {
            return back()->with('error', 'Billing is not configured yet. Please contact support.');
        }

        $currentPlanRecord = $subscription->plan
            ? Plan::where('internal_code', $subscription->plan)->first()
            : null;

        $isUpgrade = $currentPlanRecord && $planRecord
            ? (int) $planRecord->amount > (int) $currentPlanRecord->amount
            : true;

        $items = [
            new \Paddle\SDK\Entities\Subscription\SubscriptionItems($priceId, 1),
        ];
        $prorationMode = $isUpgrade
            ? \Paddle\SDK\Entities\Subscription\SubscriptionProrationBillingMode::ProratedImmediately()
            : \Paddle\SDK\Entities\Subscription\SubscriptionProrationBillingMode::ProratedNextBillingPeriod();

        $paddleEnvironment = config('paddle.environment') === 'production'
            ? \Paddle\SDK\Environment::PRODUCTION
            : \Paddle\SDK\Environment::SANDBOX;

        $paddle = new \Paddle\SDK\Client($apiKey, options: new \Paddle\SDK\Options($paddleEnvironment));

        $operation = new \Paddle\SDK\Resources\Subscriptions\Operations\PreviewUpdateSubscription(
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            $items,
            new \Paddle\SDK\Undefined(),
            $prorationMode,
            new \Paddle\SDK\Undefined(),
        );

        try {
            $response = $paddle->subscriptions->previewUpdate($subscription->paddle_subscription_id, $operation);
        } catch (\Throwable $exception) {
            return back()->with('error', 'We could not preview your subscription change. Please contact support.');
        }

        $locale = $request->user()->locale ?? app()->getLocale();
        $formatDate = static function ($value) use ($locale): ?string {
            if ($value === null) {
                return null;
            }

            return Carbon::parse($value)
                ->locale($locale)
                ->isoFormat('LLL');
        };
        $formatMoney = static function ($value, ?string $currency): ?string {
            if ($value === null || ! $currency) {
                return null;
            }

            $amount = (float) $value / 100;

            return Number::currency($amount, $currency);
        };

        $preview = [
            'charge_amount' => $formatMoney(
                data_get($response, 'updateSummary.charge.amount'),
                data_get($response, 'updateSummary.charge.currencyCode')
            ),
            'credit_amount' => $formatMoney(
                data_get($response, 'updateSummary.credit.amount'),
                data_get($response, 'updateSummary.credit.currencyCode')
            ),
            'result_amount' => $formatMoney(
                data_get($response, 'updateSummary.result.amount'),
                data_get($response, 'updateSummary.result.currencyCode')
            ),
            'result_action' => data_get($response, 'updateSummary.result.action'),
            'immediate_total' => $formatMoney(
                data_get($response, 'immediateTransaction.details.totals.total'),
                data_get($response, 'immediateTransaction.details.totals.currencyCode')
            ),
            'next_billed_at' => $formatDate(
                data_get($response, 'nextBilledAt')
                    ?? data_get($response, 'nextTransaction.billingPeriod.startsAt')
            ),
            'next_billed_total' => $formatMoney(
                data_get($response, 'nextTransaction.details.totals.total'),
                data_get($response, 'nextTransaction.details.totals.currencyCode')
            ),
            'effective_from' => $formatDate(data_get($response, 'effectiveFrom')),
            'proration' => data_get($response, 'prorationBillingMode'),
        ];

        return view('billing.change-preview', [
            'selectedPlan' => $planRecord?->name ?? ucfirst($plan),
            'previousPlan' => $currentPlanRecord?->name ?? ucfirst($subscription->plan ?? 'plan'),
            'planCode' => $plan,
            'preview' => $preview,
            'isUpgrade' => $isUpgrade,
        ]);
    }

    public function updateSubscription(Request $request): View|RedirectResponse
    {
        $plan = strtolower((string) $request->input('plan', ''));
        if (! in_array($plan, ['starter', 'pro'], true)) {
            return back()->with('error', 'Please select a valid plan to continue.');
        }

        $subscription = $request->user()->activeSubscription()->first();
        if (! $subscription) {
            return back()->with('error', 'No active subscription found to change.');
        }

        if (! $subscription->paddle_subscription_id) {
            return back()->with('error', 'Subscription details are missing. Please contact support.');
        }

        if ($subscription->plan === $plan) {
            return back()->with('error', 'You are already on that plan.');
        }

        $planRecord = Plan::where('internal_code', $plan)->first();
        $priceId = $planRecord?->paddle_price_id ?? config("paddle.prices.{$plan}");

        if (! $priceId) {
            return back()->with('error', 'Pricing is not available yet. Please contact support.');
        }

        $apiKey = config('paddle.api_key');
        if (! $apiKey) {
            return back()->with('error', 'Billing is not configured yet. Please contact support.');
        }

        $currentPlanRecord = $subscription->plan
            ? Plan::where('internal_code', $subscription->plan)->first()
            : null;

        $isUpgrade = $currentPlanRecord && $planRecord
            ? (int) $planRecord->amount > (int) $currentPlanRecord->amount
            : true;

        $items = [
            new \Paddle\SDK\Entities\Subscription\SubscriptionItems($priceId, 1),
        ];
        $prorationMode = $isUpgrade
            ? \Paddle\SDK\Entities\Subscription\SubscriptionProrationBillingMode::ProratedImmediately()
            : \Paddle\SDK\Entities\Subscription\SubscriptionProrationBillingMode::ProratedNextBillingPeriod();

        $paddleEnvironment = config('paddle.environment') === 'production'
            ? \Paddle\SDK\Environment::PRODUCTION
            : \Paddle\SDK\Environment::SANDBOX;

        $paddle = new \Paddle\SDK\Client($apiKey, options: new \Paddle\SDK\Options($paddleEnvironment));

        $operation = new \Paddle\SDK\Resources\Subscriptions\Operations\UpdateSubscription(
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            new \Paddle\SDK\Undefined(),
            $items,
            new \Paddle\SDK\Undefined(),
            $prorationMode,
            new \Paddle\SDK\Undefined(),
        );

        try {
            $response = $paddle->subscriptions->update($subscription->paddle_subscription_id, $operation);
        } catch (\Throwable $exception) {
            return back()->with('error', 'We could not update your subscription. Please contact support.');
        }

        $locale = $request->user()->locale ?? app()->getLocale();
        $formatDate = static function ($value) use ($locale): ?string {
            if ($value === null) {
                return null;
            }

            return Carbon::parse($value)
                ->locale($locale)
                ->isoFormat('LLL');
        };

        return view('billing.change-result', [
            'selectedPlan' => $planRecord?->name ?? ucfirst($plan),
            'previousPlan' => $currentPlanRecord?->name ?? ucfirst($subscription->plan ?? 'plan'),
            'details' => [
                'id' => data_get($response, 'id'),
                'status' => data_get($response, 'status'),
                'next_billed_at' => $formatDate(data_get($response, 'nextBilledAt')),
                'current_period_ends_at' => $formatDate(data_get($response, 'currentBillingPeriod.endsAt')),
                'proration' => data_get($response, 'prorationBillingMode'),
                'effective_from' => $formatDate(data_get($response, 'effectiveFrom')),
            ],
        ]);
    }

    public function cancelSubscription(Request $request): RedirectResponse
    {
        $subscription = $request->user()->activeSubscription()->first();

        if (! $subscription) {
            return back()->with('error', 'No active subscription found to cancel.');
        }

        $apiKey = config('paddle.api_key');

        if (! $apiKey) {
            return back()->with('error', 'Billing is not configured yet. Please contact support.');
        }

        if (! $subscription->paddle_subscription_id) {
            return back()->with('error', 'Subscription details are missing. Please contact support.');
        }

        $paddleEnvironment = config('paddle.environment') === 'production'
            ? \Paddle\SDK\Environment::PRODUCTION
            : \Paddle\SDK\Environment::SANDBOX;

        $paddle = new \Paddle\SDK\Client($apiKey, options: new \Paddle\SDK\Options($paddleEnvironment));

        try {
            $response = $paddle->subscriptions->cancel(
                $subscription->paddle_subscription_id,
                new \Paddle\SDK\Resources\Subscriptions\Operations\CancelSubscription(
                    \Paddle\SDK\Entities\Subscription\SubscriptionEffectiveFrom::Immediately()
                )
            );
        } catch (\Throwable $exception) {
            return back()->with('error', 'We could not cancel your subscription. Please contact support.');
        }

        $refundResult = PaddleSubscriptionRefundService::RESULT_NONE;
        $refundService = new PaddleSubscriptionRefundService();

        try {
            $refundResult = $refundService->issueRefund($paddle, $subscription);
        } catch (\Throwable $exception) {
            report($exception);
        }

        $status = data_get($response, 'status')
            ?? data_get($response, 'data.status');
        $endsAt = data_get($response, 'current_billing_period.ends_at')
            ?? data_get($response, 'currentBillingPeriod.endsAt')
            ?? data_get($response, 'data.current_billing_period.ends_at');
        $renewsAt = $endsAt;

        $subscription->update([
            'status' => $status ?? $subscription->status,
            'ends_at' => $endsAt ?? $subscription->ends_at,
            'renews_at' => $renewsAt ?? $subscription->renews_at,
        ]);

        if ($refundResult === PaddleSubscriptionRefundService::RESULT_FULL) {
            return back()->with('success', 'Your subscription has been canceled. A full refund is being processed.');
        }

        if ($refundResult === PaddleSubscriptionRefundService::RESULT_PARTIAL) {
            return back()->with('success', 'Your subscription has been canceled. A prorated refund for unused days is being processed.');
        }

        return back()->with(
            'success',
            'Your subscription has been canceled. We were unable to issue your refund automatically; please contact support.'
        );
    }
}
