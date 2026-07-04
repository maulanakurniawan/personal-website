<?php

namespace Tests\Feature\ValidationApi;

use App\Models\ValidationApiClient;
use App\Models\ValidationLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationLeadApiTest extends TestCase
{
    use RefreshDatabase;

    private string $plainKey = 'vapi_test_secret';

    public function test_authentication_failures_and_forbidden_host(): void
    {
        $client = $this->client();
        $payload = $this->payload();

        $this->postJson('/internal/validation/v1/leads', $payload)->assertStatus(401);
        $this->postJson('/internal/validation/v1/leads', $payload, ['X-Validation-Api-Key' => $this->plainKey])->assertStatus(401);
        $this->postJson('/internal/validation/v1/leads', $payload, $this->headers('wrong'))->assertStatus(401);

        $client->update(['enabled' => false]);
        $this->postJson('/internal/validation/v1/leads', $payload, $this->headers())->assertStatus(401);
        $client->update(['enabled' => true, 'revoked_at' => now()]);
        $this->postJson('/internal/validation/v1/leads', $payload, $this->headers())->assertStatus(401);

        $this->client(['revoked_at' => null, 'allowed_hosts' => ['example.com']]);
        $this->postJson('/internal/validation/v1/leads', $payload, $this->headers())->assertStatus(403);
    }

    public function test_valid_payload_creates_and_stores_metadata_without_raw_ip(): void
    {
        $this->client();

        $this->postJson('/internal/validation/v1/leads', $this->payload(), $this->headers())
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.created', true);

        $this->assertDatabaseHas('validation_leads', [
            'product_key' => 'keepbydate', 'email' => 'user@example.com', 'utm_source' => 'x',
            'price_seen_currency' => 'USD', 'price_seen_amount' => 5, 'ip_hash' => 'hashed-ip-from-source-site',
        ]);
        $this->assertFalse(collect(ValidationLead::first()->getAttributes())->contains('127.0.0.1'));
    }

    public function test_duplicate_updates_increments_and_keeps_status(): void
    {
        $this->client();
        ValidationLead::create($this->payload(['status' => 'reviewed', 'submission_count' => 2]));

        $this->postJson('/internal/validation/v1/leads', $this->payload(['notes' => 'new note', 'target_category' => 'freezer']), $this->headers())
            ->assertOk()
            ->assertJsonPath('data.created', false);

        $lead = ValidationLead::first();
        $this->assertSame(3, $lead->submission_count);
        $this->assertSame('reviewed', $lead->status);
        $this->assertSame('new note', $lead->notes);
        $this->assertSame('freezer', $lead->target_category);
    }

    public function test_validation_errors(): void
    {
        $this->client();
        $this->postJson('/internal/validation/v1/leads', $this->payload(['product_key' => 'wrong']), $this->headers())->assertStatus(422)->assertJsonPath('error.code', 'validation_error');
        $this->postJson('/internal/validation/v1/leads', $this->payload(['email' => 'bad']), $this->headers())->assertStatus(422)->assertJsonPath('error.code', 'validation_error');
        $this->postJson('/internal/validation/v1/leads', $this->payload(['price_interest' => 'later']), $this->headers())->assertStatus(422)->assertJsonPath('error.code', 'validation_error');
    }

    private function client(array $overrides = []): ValidationApiClient
    {
        return ValidationApiClient::updateOrCreate(['product_key' => 'keepbydate'], array_merge([
            'product_name' => 'KeepByDate', 'key_prefix' => substr($this->plainKey, 0, 12), 'key_hash' => hash('sha256', $this->plainKey),
            'allowed_hosts' => ['keepbydate.com', 'www.keepbydate.com'], 'enabled' => true, 'revoked_at' => null,
        ], $overrides));
    }

    private function headers(?string $key = null): array
    {
        return ['X-Validation-Api-Key' => $key ?? $this->plainKey, 'X-Product-Key' => 'keepbydate'];
    }

    private function payload(array $overrides = []): array
    {
        return array_merge(['product_key' => 'keepbydate', 'product_name' => 'KeepByDate', 'source_url' => 'https://keepbydate.com/?utm_source=x', 'email' => 'user@example.com', 'locale' => 'en', 'target_category' => 'food', 'price_interest' => 'maybe', 'notes' => 'I forget food in the freezer.', 'price_seen_currency' => 'USD', 'price_seen_amount' => 5, 'utm_source' => 'x', 'utm_medium' => 'social', 'utm_campaign' => 'launch', 'utm_content' => null, 'utm_term' => null, 'ip_hash' => 'hashed-ip-from-source-site', 'user_agent' => 'browser user agent'], $overrides);
    }
}
