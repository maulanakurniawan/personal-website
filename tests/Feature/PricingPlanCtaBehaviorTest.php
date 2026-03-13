<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingPlanCtaBehaviorTest extends TestCase
{
    use RefreshDatabase;

    public function test_pricing_page_routes_guests_to_signup_with_selected_plan(): void
    {
        $response = $this->get(route('pricing'));

        $response->assertOk();
        $response->assertSee(route('signup', ['plan' => 'starter']), false);
        $response->assertSee(route('signup', ['plan' => 'pro']), false);
    }

    public function test_pricing_page_routes_authenticated_users_to_billing_checkout_for_selected_plan(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pricing'));

        $response->assertOk();
        $response->assertSee('form method="POST" action="'.route('billing.checkout', ['plan' => 'starter']).'"', false);
        $response->assertSee('form method="POST" action="'.route('billing.checkout', ['plan' => 'pro']).'"', false);
    }

    public function test_signup_is_not_accessible_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('signup'));

        $response->assertRedirect(route('dashboard'));
    }
}
