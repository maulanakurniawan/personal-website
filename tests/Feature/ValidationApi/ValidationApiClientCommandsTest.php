<?php

namespace Tests\Feature\ValidationApi;

use App\Models\ValidationApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationApiClientCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_list_disable_enable_revoke_and_rotate_commands(): void
    {
        $this->artisan('validation-api-client:create', ['--product-key' => 'keepbydate', '--product-name' => 'KeepByDate', '--allowed-host' => ['keepbydate.com', 'www.keepbydate.com']])
            ->expectsOutputToContain('Validation API client created.')
            ->expectsOutputToContain('VALIDATION_API_KEY=vapi_')
            ->assertSuccessful();

        $client = ValidationApiClient::firstOrFail();
        $this->assertSame('keepbydate', $client->product_key);
        $this->assertNotNull($client->key_hash);
        $this->assertDatabaseMissing('validation_api_clients', ['key_hash' => $client->key_prefix]);

        $this->artisan('validation-api-client:list')->expectsOutputToContain('keepbydate')->assertSuccessful();
        $this->artisan('validation-api-client:disable', ['id_or_prefix' => (string) $client->id])->assertSuccessful();
        $this->assertFalse($client->fresh()->enabled);
        $this->artisan('validation-api-client:enable', ['id_or_prefix' => $client->key_prefix])->assertSuccessful();
        $this->assertTrue($client->fresh()->enabled);
        $oldHash = $client->fresh()->key_hash;
        $this->artisan('validation-api-client:rotate', ['id_or_prefix' => (string) $client->id])->expectsOutputToContain('VALIDATION_API_KEY=vapi_')->assertSuccessful();
        $this->assertNotSame($oldHash, $client->fresh()->key_hash);
        $this->artisan('validation-api-client:revoke', ['id_or_prefix' => (string) $client->id])->assertSuccessful();
        $this->assertFalse($client->fresh()->enabled);
        $this->assertNotNull($client->fresh()->revoked_at);
    }
}
