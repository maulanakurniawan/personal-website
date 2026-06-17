<?php

namespace Tests\Feature\InternalAdminApi;

use App\Models\InternalAdminClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternalAdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_admin_api_disabled_returns_404(): void
    {
        config(['internal-admin-api.enabled' => false]);
        $this->getJson('/api/internal/admin/v1/overview')->assertNotFound();
    }

    public function test_credentials_are_required_and_validated(): void
    {
        config(['internal-admin-api.enabled' => true]);
        $client = $this->client('secret');
        $this->getJson('/api/internal/admin/v1/overview')->assertUnauthorized();
        $this->withHeader('X-Admin-Client-Id', $client->client_id)->getJson('/api/internal/admin/v1/overview')->assertUnauthorized();
        $this->withHeaders(['X-Admin-Client-Id' => $client->client_id, 'X-Admin-Client-Secret' => 'wrong'])->getJson('/api/internal/admin/v1/overview')->assertUnauthorized();
    }

    public function test_inactive_revoked_and_disallowed_clients_are_forbidden(): void
    {
        config(['internal-admin-api.enabled' => true]);
        $inactive = $this->client('secret', ['is_active' => false]);
        $this->auth($inactive, 'secret')->getJson('/api/internal/admin/v1/overview')->assertForbidden();
        $revoked = $this->client('secret', ['revoked_at' => now()]);
        $this->auth($revoked, 'secret')->getJson('/api/internal/admin/v1/overview')->assertForbidden();
        $ip = $this->client('secret', ['allowed_ips' => ['192.0.2.10']]);
        $this->auth($ip, 'secret')->getJson('/api/internal/admin/v1/overview')->assertForbidden();
    }

    public function test_correct_credentials_can_access_overview_shape(): void
    {
        config(['internal-admin-api.enabled' => true]);
        $client = $this->client('secret');
        $this->auth($client, 'secret')->getJson('/api/internal/admin/v1/overview')->assertOk()->assertJsonStructure(['success', 'product', 'data', 'meta']);
    }

    public function test_client_commands_manage_secrets_safely(): void
    {
        $this->artisan('internal-admin-client:create', ['name' => 'Admin Hub Local', '--scopes' => 'read,write'])->expectsOutputToContain('Client Secret:')->assertSuccessful();
        $client = InternalAdminClient::first();
        $oldHash = $client->client_secret_hash;
        $this->artisan('internal-admin-client:list')->expectsOutputToContain($client->client_id)->doesntExpectOutputToContain($oldHash)->assertSuccessful();
        $this->artisan('internal-admin-client:rotate', ['client_id' => $client->client_id])->expectsOutputToContain('Client Secret:')->assertSuccessful();
        $this->assertNotSame($oldHash, $client->fresh()->client_secret_hash);
        $this->artisan('internal-admin-client:revoke', ['client_id' => $client->client_id])->assertSuccessful();
        $this->assertNotNull($client->fresh()->revoked_at);
    }

    private function client(string $secret, array $overrides = []): InternalAdminClient
    {
        return InternalAdminClient::create(array_merge(['name' => 'Client', 'client_id' => fake()->uuid(), 'client_secret_hash' => Hash::make($secret), 'scopes' => ['read', 'write'], 'is_active' => true], $overrides));
    }

    private function auth(InternalAdminClient $client, string $secret): self
    {
        return $this->withHeaders(['X-Admin-Client-Id' => $client->client_id, 'X-Admin-Client-Secret' => $secret, 'X-Admin-Hub' => 'maulanakurniawan.com', 'Accept' => 'application/json']);
    }
}
