<?php

namespace Tests\Feature\AdminHub;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_hub_disabled_returns_404(): void
    {
        config(['admin-hub.enabled' => false]);
        $this->get('/admin/login')->assertNotFound();
    }

    public function test_admin_login_page_loads_when_enabled(): void
    {
        config(['admin-hub.enabled' => true]);
        $this->get('/admin/login')->assertOk()->assertSee('Admin Hub Login');
    }

    public function test_active_admin_user_can_log_in(): void
    {
        config(['admin-hub.enabled' => true]);
        AdminUser::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret-password', 'is_active' => true]);
        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'secret-password'])->assertRedirect('/admin');
        $this->assertAuthenticated('admin');
    }

    public function test_inactive_admin_user_cannot_log_in(): void
    {
        config(['admin-hub.enabled' => true]);
        AdminUser::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret-password', 'is_active' => false]);
        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'secret-password'])->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_unauthenticated_user_cannot_access_admin(): void
    {
        config(['admin-hub.enabled' => true]);
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_unknown_product_key_returns_404(): void
    {
        config(['admin-hub.enabled' => true]);
        $admin = AdminUser::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret-password']);
        $this->actingAs($admin, 'admin')->get('/admin/unknown/overview')->assertNotFound();
    }

    public function test_product_dropdown_loads_products_from_config(): void
    {
        config(['admin-hub.enabled' => true, 'admin-hub.products.webhookwatch.base_url' => 'https://example.test', 'admin-hub.products.webhookwatch.client_id' => 'id', 'admin-hub.products.webhookwatch.client_secret' => 'secret']);
        Http::fake(['example.test/*' => Http::response(['success' => true, 'data' => [], 'meta' => []])]);
        $admin = AdminUser::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret-password']);
        $this->actingAs($admin, 'admin')->get('/admin/webhookwatch/overview')->assertOk()->assertSee('WebhookWatch')->assertSee('SoloHours');
    }

    public function test_admin_hub_can_delete_product_user_and_related_data(): void
    {
        config(['admin-hub.enabled' => true, 'admin-hub.products.webhookwatch.base_url' => 'https://example.test', 'admin-hub.products.webhookwatch.client_id' => 'id', 'admin-hub.products.webhookwatch.client_secret' => 'secret']);
        Http::fake([
            'example.test/users' => Http::response(['success' => true, 'data' => ['items' => [['id' => 10, 'email' => 'user@example.com']]], 'meta' => []]),
            'example.test/users/10' => Http::response(['success' => true, 'data' => []]),
        ]);
        $admin = AdminUser::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret-password']);

        $this->actingAs($admin, 'admin')->get('/admin/webhookwatch/users')->assertOk()->assertSee('Delete')->assertSee('all related data');
        $this->actingAs($admin, 'admin')->delete('/admin/webhookwatch/users/10')->assertRedirect('/admin/webhookwatch/users')->assertSessionHas('status', 'User and related data deleted.');

        Http::assertSent(fn ($request) => $request->method() === 'DELETE' && $request->url() === 'https://example.test/users/10');
    }

    public function test_admin_user_commands_work_without_showing_hashes(): void
    {
        $this->artisan('admin-user:create', ['name' => 'Maulana', 'email' => 'admin@example.com', '--password' => 'secret-password'])->assertSuccessful();
        $this->assertTrue(Hash::check('secret-password', AdminUser::first()->password));
        $this->artisan('admin-user:list')->expectsOutputToContain('admin@example.com')->doesntExpectOutputToContain(AdminUser::first()->password)->assertSuccessful();
        $this->artisan('admin-user:disable', ['email' => 'admin@example.com'])->assertSuccessful();
        $this->assertFalse(AdminUser::first()->is_active);
        $this->artisan('admin-user:reset-password', ['email' => 'admin@example.com', '--password' => 'new-secret'])->assertSuccessful();
        $this->assertTrue(Hash::check('new-secret', AdminUser::first()->password));
    }

    public function test_client_sends_headers_and_handles_responses_safely(): void
    {
        config(['admin-hub.products.webhookwatch.base_url' => 'https://example.test', 'admin-hub.products.webhookwatch.client_id' => 'id', 'admin-hub.products.webhookwatch.client_secret' => 'secret']);
        Http::fake([
            'example.test/overview' => Http::sequence()
                ->push(['success' => true, 'data' => ['ok' => true], 'meta' => []])
                ->push([], 500),
        ]);

        app(\App\AdminHub\Clients\SaasAdminClient::class)->get('webhookwatch', 'overview');
        Http::assertSent(fn ($request) => $request->hasHeader('X-Admin-Client-Id', 'id') && $request->hasHeader('X-Admin-Client-Secret', 'secret'));

        $response = app(\App\AdminHub\Clients\SaasAdminClient::class)->get('webhookwatch', 'overview');
        $this->assertFalse($response->success);
        $this->assertSame('api_error', $response->error['code']);
    }
}
