<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_does_not_render_tracking_script_on_localhost(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this
            ->actingAs($user)
            ->get('http://localhost/dashboard');

        $response->assertOk();
        $response->assertDontSee('googletagmanager.com/gtag/js', false);
    }

    public function test_dashboard_renders_tracking_script_for_non_admin_user_outside_localhost(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this
            ->actingAs($user)
            ->get('http://app.solohours.test/dashboard');

        $response->assertOk();
        $response->assertSee('googletagmanager.com/gtag/js?id=G-CD4QLCV9LG', false);
        $response->assertSee("gtag('config', 'G-CD4QLCV9LG');", false);
    }


    public function test_billing_success_page_includes_purchase_event_tracking(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this
            ->actingAs($user)
            ->get('http://app.solohours.test/billing/success?checkout_id=chk_123');

        $response->assertOk();
        $response->assertSee("gtag('event', 'purchase'", false);
        $response->assertSee("checkout_id", false);
    }

    public function test_dashboard_does_not_render_tracking_script_for_admin_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this
            ->actingAs($admin)
            ->get('http://app.solohours.test/dashboard');

        $response->assertOk();
        $response->assertDontSee('googletagmanager.com/gtag/js', false);
    }
}
