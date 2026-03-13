<?php

namespace Tests\Feature;

use App\Models\PaddleTransaction;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaddleWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_transaction_webhooks(): void
    {
        config()->set('paddle.webhook_secret', 'test-secret');

        $user = User::factory()->create();
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan' => 'starter',
            'paddle_subscription_id' => 'sub_123',
            'status' => 'active',
        ]);

        $payload = [
            'event_type' => 'transaction.completed',
            'data' => [
                'id' => 'txn_123',
                'subscription_id' => $subscription->paddle_subscription_id,
                'status' => 'completed',
                'billed_at' => '2026-02-17T10:00:00Z',
                'details' => [
                    'totals' => [
                        'currency_code' => 'USD',
                        'grand_total' => '4900',
                        'credit' => '0.00',
                    ],
                ],
            ],
        ];

        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp . ':' . $json, 'test-secret');

        $response = $this->call('POST', route('webhooks.paddle'), server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PADDLE_SIGNATURE' => "ts={$timestamp};h1={$signature}",
        ], content: $json);

        $response->assertOk();

        $record = PaddleTransaction::where('paddle_transaction_id', 'txn_123')->first();

        $this->assertNotNull($record);
        $this->assertSame($user->id, $record->user_id);
        $this->assertSame($subscription->id, $record->subscription_id);
        $this->assertSame('transaction.completed', $record->event_type);
        $this->assertSame('49.00', $record->amount);
    }

    public function test_it_records_adjustment_webhooks_with_refund_details(): void
    {
        config()->set('paddle.webhook_secret', 'test-secret');

        $user = User::factory()->create();
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan' => 'pro',
            'paddle_subscription_id' => 'sub_refund',
            'status' => 'active',
        ]);

        PaddleTransaction::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'paddle_transaction_id' => 'txn_refund',
            'paddle_subscription_id' => $subscription->paddle_subscription_id,
            'event_type' => 'transaction.paid',
            'status' => 'paid',
            'currency' => 'USD',
            'amount' => '99.00',
            'payload' => ['id' => 'txn_refund'],
        ]);

        $payload = [
            'event_type' => 'adjustment.updated',
            'data' => [
                'id' => 'adj_123',
                'transaction_id' => 'txn_refund',
                'action' => 'refund',
                'status' => 'approved',
                'currency_code' => 'USD',
                'totals' => [
                    'credit' => '3550',
                ],
                'items' => [
                    ['item_id' => 'item_1', 'amount' => '3550'],
                ],
                'reason' => 'requested_by_customer',
                'processed_at' => '2026-02-17T12:00:00Z',
            ],
        ];

        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp . ':' . $json, 'test-secret');

        $response = $this->call('POST', route('webhooks.paddle'), server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PADDLE_SIGNATURE' => "ts={$timestamp};h1={$signature}",
        ], content: $json);

        $response->assertOk();

        $record = PaddleTransaction::where('paddle_adjustment_id', 'adj_123')->first();

        $this->assertNotNull($record);
        $this->assertSame($user->id, $record->user_id);
        $this->assertSame($subscription->id, $record->subscription_id);
        $this->assertSame('adjustment.updated', $record->event_type);
        $this->assertSame('refund', $record->adjustment_action);
        $this->assertSame('35.50', $record->refund_amount);
    }
}
