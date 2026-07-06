<?php

namespace Tests\Feature\AdminHub;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminResourceCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['admin-hub.enabled' => true, 'admin-hub.products.webhookwatch.base_url' => 'https://example.test', 'admin-hub.products.webhookwatch.client_id' => 'client-id', 'admin-hub.products.webhookwatch.client_secret' => 'super-secret']);
    }

    public function test_resource_index_redirects_to_first_available_resource(): void
    {
        Http::fake(['example.test/resources' => Http::response(['success' => true, 'data' => ['resources' => [['key' => 'users', 'label' => 'Users', 'operations' => ['view'], 'description' => 'Safe users']]]])]);

        $this->actingAsAdmin()
            ->get('/admin/webhookwatch/resources')
            ->assertRedirect('/admin/webhookwatch/resources/users');
    }

    public function test_resource_table_renders_dynamic_left_navigation_and_hides_legacy_menus(): void
    {
        $this->fakeSchemaAndItems();

        $this->actingAsAdmin()
            ->get('/admin/webhookwatch/resources/users')
            ->assertOk()
            ->assertSee('Users')
            ->assertSee('Invoices')
            ->assertDontSee('/admin/webhookwatch/overview', false)
            ->assertDontSee('/admin/webhookwatch/subscriptions', false)
            ->assertDontSee('super-secret');
    }

    public function test_table_renders_schema_columns_and_keeps_pagination_search_and_sort_query(): void
    {
        $this->fakeSchemaAndItems();
        $this->actingAsAdmin()->get('/admin/webhookwatch/resources/users?search=maulana&sort=email&page=2')->assertOk()->assertSee('Email')->assertSee('maulana@example.com')->assertSee('page=3', false);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'resources/users?') && str_contains($request->url(), 'search=maulana') && str_contains($request->url(), 'sort=email') && str_contains($request->url(), 'page=2'));
    }

    public function test_detail_page_renders_fields(): void
    {
        $this->fakeSchemaAndItems();
        $this->actingAsAdmin()->get('/admin/webhookwatch/resources/users/1')->assertOk()->assertSee('maulana@example.com')->assertSee('Yes');
    }

    public function test_create_and_edit_pages_are_not_available_when_operation_unsupported(): void
    {
        $this->fakeSchemaAndItems(['operations' => ['view']]);

        $this->actingAsAdmin()->get('/admin/webhookwatch/resources/users/create')->assertNotFound();
        $this->actingAsAdmin()->get('/admin/webhookwatch/resources/users/1/edit')->assertNotFound();
    }

    public function test_create_and_edit_pages_are_available_when_operation_supported(): void
    {
        $this->fakeSchemaAndItems(['operations' => ['view', 'create', 'update']]);

        $this->actingAsAdmin()->get('/admin/webhookwatch/resources/users/create')->assertOk()->assertSee('Create Users');
        $this->actingAsAdmin()->get('/admin/webhookwatch/resources/users/1/edit')->assertOk()->assertSee('Edit Users');
    }

    public function test_crud_buttons_appear_for_rest_style_operation_names(): void
    {
        $this->fakeSchemaAndItems(['operations' => ['index', 'show', 'store', 'edit', 'destroy']]);

        $this->actingAsAdmin()
            ->get('/admin/webhookwatch/resources/users')
            ->assertOk()
            ->assertSee('+ Create User')
            ->assertSee('View')
            ->assertSee('Edit')
            ->assertSee('Delete');
    }

    public function test_delete_button_does_not_appear_when_unsupported(): void
    {
        $this->fakeSchemaAndItems(['operations' => ['view']]);

        $this->actingAsAdmin()->get('/admin/webhookwatch/resources/users')->assertOk()->assertDontSee('_method', false)->assertDontSee('Delete');
    }

    public function test_delete_button_appears_when_supported_and_uses_form_submission(): void
    {
        $this->fakeSchemaAndItems(['operations' => ['view', 'delete']]);

        $this->actingAsAdmin()->get('/admin/webhookwatch/resources/users')->assertOk()->assertSee('method="POST"', false)->assertSee('Delete')->assertSee('confirm(', false);
    }

    public function test_bulk_actions_are_not_rendered_or_routable_even_when_schema_advertises_them(): void
    {
        $this->fakeSchemaAndItems(['bulk_actions' => [['key' => 'delete_selected', 'label' => 'Delete selected']]]);

        $this->actingAsAdmin()
            ->get('/admin/webhookwatch/resources/users')
            ->assertOk()
            ->assertDontSee('bulk-actions', false)
            ->assertDontSee('Select all')
            ->assertDontSee('Delete selected')
            ->assertDontSee('name="ids[]"', false);

        $this->actingAsAdmin()
            ->post('/admin/webhookwatch/resources/users/bulk-actions', ['action' => 'delete_selected', 'ids' => [1]])
            ->assertStatus(405);
    }

    public function test_validation_errors_from_saas_are_displayed(): void
    {
        Http::fake([
            'example.test/resources/users' => Http::response(['success' => false, 'error' => ['code' => 'validation_failed', 'message' => 'Validation failed.', 'validation' => ['email' => ['Email is required.']]]], 422),
        ]);
        $this->actingAsAdmin()->post('/admin/webhookwatch/resources/users', ['email' => ''])->assertSessionHasErrors('email');
    }

    public function test_unknown_resource_returns_safe_404_and_api_failure_shows_safe_error(): void
    {
        Http::fake(['example.test/resources/missing/schema' => Http::response(['success' => false, 'error' => ['message' => 'Missing']], 404)]);
        $this->actingAsAdmin()->get('/admin/webhookwatch/resources/missing')->assertNotFound();

        Http::fake(['example.test/resources/users/schema' => Http::response(['success' => false, 'error' => ['message' => 'Product API error.']], 500)]);
        $this->actingAsAdmin()->get('/admin/webhookwatch/resources/users')->assertOk()->assertSee('Product API error.')->assertDontSee('Stack trace');
    }

    public function test_unauthenticated_users_cannot_access_crud_pages(): void
    {
        $this->get('/admin/webhookwatch/resources/users')->assertRedirect('/admin/login');
    }

    private function actingAsAdmin(): self
    {
        $admin = AdminUser::create(['name' => 'Admin', 'email' => uniqid().'@example.com', 'password' => 'secret-password']);

        return $this->actingAs($admin, 'admin');
    }

    private function fakeSchemaAndItems(array $overrides = []): void
    {
        $schema = array_merge(['label' => 'Users', 'operations' => ['view', 'create', 'update', 'delete'], 'searchable' => ['email'], 'list_columns' => [['key' => 'email', 'label' => 'Email', 'type' => 'email', 'sortable' => true], ['key' => 'active', 'label' => 'Active', 'type' => 'boolean']], 'fields' => [['key' => 'email', 'label' => 'Email', 'type' => 'email', 'creatable' => true, 'editable' => true], ['key' => 'active', 'label' => 'Active', 'type' => 'boolean', 'creatable' => true, 'editable' => true]]], $overrides);
        Http::fake([
            'example.test/resources' => Http::response(['success' => true, 'data' => ['resources' => [['key' => 'users', 'label' => 'Users'], ['key' => 'invoices', 'label' => 'Invoices']]]]),
            'example.test/resources/users/schema' => Http::response(['success' => true, 'data' => $schema]),
            'example.test/resources/users*' => Http::response(['success' => true, 'data' => ['items' => [['id' => 1, 'email' => 'maulana@example.com', 'active' => true]]], 'meta' => ['pagination' => ['current_page' => 2, 'next_page' => 3]]]),
            'example.test/resources/users/1' => Http::response(['success' => true, 'data' => ['item' => ['id' => 1, 'email' => 'maulana@example.com', 'active' => true]]]),
        ]);
    }
}
