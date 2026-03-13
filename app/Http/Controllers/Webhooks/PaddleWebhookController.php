<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\PaddleTransaction;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PaddleWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = config('paddle.webhook_secret');
        $payload = $request->getContent();

        if (! $secret) {
            Log::warning('Paddle webhook secret missing.');
            return response('Webhook secret not configured.', Response::HTTP_OK);
        }

        if (! $this->isValidSignature($request, $payload, $secret)) {
            Log::warning('Invalid Paddle webhook signature.');
            return response('Invalid signature.', Response::HTTP_UNAUTHORIZED);
        }

        $eventType = $request->input('event_type');
        $data = $request->input('data', []);

        if (Str::startsWith($eventType, 'subscription.')) {
            $this->handleSubscriptionEvent($eventType, $data);
        } elseif (Str::startsWith($eventType, 'transaction.')) {
            $this->handleTransactionEvent($eventType, $data);
        } elseif (Str::startsWith($eventType, 'adjustment.')) {
            $this->handleAdjustmentEvent($eventType, $data);
        } elseif ($eventType === 'price.updated') {
            $this->handlePriceUpdated($data);
        } else {
            Log::info('Unhandled Paddle webhook event.', ['event_type' => $eventType]);
        }

        return response('OK', Response::HTTP_OK);
    }

    private function handleSubscriptionEvent(string $eventType, array $data): void
    {
        $subscriptionId = data_get($data, 'id');
        $priceId = data_get($data, 'items.0.price.id') ?? data_get($data, 'price_id');
        $status = data_get($data, 'status');
        $customData = data_get($data, 'custom_data');
        if (! is_array($customData)) {
            $customData = [];
        }

        if ($customData === []) {
            $customData = array_filter([
                'user_id' => data_get($data, 'customer_id') ?? data_get($data, 'customer.id'),
            ]);
        }

        $userId = data_get($customData, 'user_id');
        $plan = $priceId
            ? Plan::where('paddle_price_id', $priceId)->value('internal_code')
            : data_get($customData, 'plan');

        if (! $subscriptionId || ! $userId) {
            Log::warning('Missing subscription metadata in Paddle event.', [
                'event_type' => $eventType,
                'subscription_id' => $subscriptionId,
            ]);
            return;
        }

        $subscription = Subscription::updateOrCreate(
            ['paddle_subscription_id' => $subscriptionId],
            [
                'user_id' => $userId,
                'plan' => $plan ?? 'starter',
                'paddle_price_id' => $priceId,
                'status' => $status,
                'renews_at' => data_get($data, 'next_billed_at'),
                'ends_at' => data_get($data, 'canceled_at')
                    ?? data_get($data, 'ends_at')
                    ?? data_get($data, 'current_billing_period.ends_at'),
            ]
        );

        if ($plan && $user = User::find($userId)) {
            $user->forceFill(['plan' => $plan])->save();
        }

        Log::info('Synced Paddle subscription.', [
            'subscription_id' => $subscription->id,
            'event_type' => $eventType,
        ]);
    }

    private function handlePriceUpdated(array $data): void
    {
        $priceId = data_get($data, 'id');
        if (! $priceId) {
            Log::warning('Price updated webhook missing price id.');
            return;
        }

        $plan = Plan::where('paddle_price_id', $priceId)->first();
        if (! $plan) {
            Log::info('Price update received for unknown plan.', ['price_id' => $priceId]);
            return;
        }

        $plan->update([
            'name' => data_get($data, 'name', $plan->name),
            'currency' => data_get($data, 'unit_price.currency_code', $plan->currency),
            'amount' => data_get($data, 'unit_price.amount', $plan->amount),
            'billing_interval' => data_get($data, 'billing_cycle.interval', $plan->billing_interval),
            'active' => data_get($data, 'status', $plan->active ? 'active' : 'inactive') === 'active',
        ]);
    }

    private function handleTransactionEvent(string $eventType, array $data): void
    {
        $paddleTransactionId = data_get($data, 'id');
        if (! $paddleTransactionId) {
            Log::warning('Transaction webhook missing transaction id.', ['event_type' => $eventType]);

            return;
        }

        $paddleSubscriptionId = data_get($data, 'subscription_id');
        $subscription = $paddleSubscriptionId
            ? Subscription::where('paddle_subscription_id', $paddleSubscriptionId)->first()
            : null;

        $userId = $subscription?->user_id;

        if (! $userId) {
            $customData = data_get($data, 'custom_data', []);
            if (! is_array($customData)) {
                $customData = [];
            }

            $candidateId = data_get($customData, 'user_id');
            if ($candidateId && User::whereKey($candidateId)->exists()) {
                $userId = (int) $candidateId;
            }
        }

        $totals = data_get($data, 'details.totals', []);

        PaddleTransaction::create([
            'user_id' => $userId,
            'subscription_id' => $subscription?->id,
            'paddle_transaction_id' => $paddleTransactionId,
            'paddle_subscription_id' => $paddleSubscriptionId,
            'event_type' => $eventType,
            'status' => data_get($data, 'status'),
            'currency' => data_get($totals, 'currency_code') ?? data_get($data, 'currency_code'),
            'amount' => $this->normalizeAmount(
                data_get($totals, 'grand_total')
                ?? data_get($totals, 'total')
                ?? data_get($totals, 'total_amount')
            ),
            'refund_amount' => $this->normalizeAmount(data_get($totals, 'credit') ?? data_get($totals, 'credit_total')),
            'occurred_at' => data_get($data, 'billed_at') ?? data_get($data, 'updated_at') ?? data_get($data, 'created_at'),
            'details' => [
                'invoice_number' => data_get($data, 'invoice_number'),
                'origin' => data_get($data, 'origin'),
                'billing_period' => data_get($data, 'billing_period'),
                'line_items' => data_get($data, 'details.line_items', []),
                'totals' => $totals,
            ],
            'payload' => $data,
        ]);
    }

    private function handleAdjustmentEvent(string $eventType, array $data): void
    {
        $adjustmentId = data_get($data, 'id');
        $paddleTransactionId = data_get($data, 'transaction_id');

        if (! $adjustmentId && ! $paddleTransactionId) {
            Log::warning('Adjustment webhook missing both adjustment and transaction identifiers.', [
                'event_type' => $eventType,
            ]);

            return;
        }

        $subscription = null;
        $userId = null;

        if ($paddleTransactionId) {
            $transactionRecord = PaddleTransaction::query()
                ->where('paddle_transaction_id', $paddleTransactionId)
                ->latest('id')
                ->first();

            $subscription = $transactionRecord?->subscription;
            $userId = $transactionRecord?->user_id;
        }

        PaddleTransaction::create([
            'user_id' => $userId,
            'subscription_id' => $subscription?->id,
            'paddle_transaction_id' => $paddleTransactionId,
            'paddle_subscription_id' => $subscription?->paddle_subscription_id,
            'paddle_adjustment_id' => $adjustmentId,
            'event_type' => $eventType,
            'status' => data_get($data, 'status'),
            'currency' => data_get($data, 'currency_code'),
            'amount' => $this->normalizeAmount(data_get($data, 'totals.grand_total')),
            'refund_amount' => $this->normalizeAmount(data_get($data, 'totals.credit') ?? data_get($data, 'totals.total')),
            'adjustment_action' => data_get($data, 'action'),
            'adjustment_status' => data_get($data, 'status'),
            'occurred_at' => data_get($data, 'processed_at')
                ?? data_get($data, 'updated_at')
                ?? data_get($data, 'created_at'),
            'details' => [
                'reason' => data_get($data, 'reason'),
                'items' => data_get($data, 'items', []),
                'totals' => data_get($data, 'totals', []),
                'invoice_id' => data_get($data, 'invoice_id'),
            ],
            'payload' => $data,
        ]);
    }

    private function normalizeAmount(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return number_format((float) $value / 100, 2, '.', '');
        }

        return null;
    }

    private function isValidSignature(Request $request, string $payload, string $secret): bool
    {
        $signatureHeader = $request->header('Paddle-Signature');
        if (! $signatureHeader) {
            return false;
        }

        $parts = collect(explode(';', $signatureHeader))
            ->mapWithKeys(function (string $pair) {
                [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);
                return [$key => $value];
            });

        $timestamp = $parts->get('ts');
        $signature = $parts->get('h1');

        if (! $timestamp || ! $signature) {
            return false;
        }

        $computed = hash_hmac('sha256', $timestamp . ':' . $payload, $secret);

        return hash_equals($computed, $signature);
    }
}
