<?php

namespace Tests\Unit;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_limits_for_starter(): void
    {
        $user = User::factory()->create([
            'plan' => User::PLAN_STARTER,
        ]);

        $this->assertSame(5, $user->projectLimit());
    }

    public function test_project_limits_for_pro_with_active_subscription(): void
    {
        $user = User::factory()->create([
            'plan' => User::PLAN_PRO,
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'plan' => User::PLAN_PRO,
            'status' => 'active',
        ]);

        $this->assertNull($user->projectLimit());
    }

    public function test_active_subscription_plan_is_used_for_limits(): void
    {
        $user = User::factory()->create([
            'plan' => User::PLAN_STARTER,
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'plan' => User::PLAN_PRO,
            'status' => 'active',
        ]);

        $this->assertSame(User::PLAN_PRO, $user->currentPlan());
        $this->assertNull($user->projectLimit());
    }

    public function test_non_subscribed_users_default_to_starter_limits(): void
    {
        $user = User::factory()->create([
            'plan' => User::PLAN_STARTER,
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'plan' => User::PLAN_PRO,
            'status' => 'canceled',
        ]);

        $this->assertSame(User::PLAN_STARTER, $user->currentPlan());
        $this->assertSame(5, $user->projectLimit());
    }

    public function test_non_subscribed_pro_users_still_use_starter_limits(): void
    {
        $user = User::factory()->create([
            'plan' => User::PLAN_PRO,
        ]);

        $this->assertSame(User::PLAN_STARTER, $user->currentPlan());
        $this->assertSame(5, $user->projectLimit());
    }


    public function test_has_active_subscription(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->hasActiveSubscription());

        Subscription::create([
            'user_id' => $user->id,
            'plan' => User::PLAN_STARTER,
            'paddle_subscription_id' => 'sub_123',
            'paddle_price_id' => 'price_123',
            'status' => 'active',
            'renews_at' => now()->addMonth(),
        ]);

        $this->assertTrue($user->hasActiveSubscription());

        $user->subscriptions()->update(['status' => 'canceled']);

        $this->assertFalse($user->hasActiveSubscription());
    }

    public function test_can_track_time_requires_active_subscription(): void
    {
        $user = User::factory()->create([
            'plan' => User::PLAN_PRO,
        ]);

        $this->assertFalse($user->canTrackTime());

        Subscription::create([
            'user_id' => $user->id,
            'plan' => User::PLAN_PRO,
            'status' => 'active',
        ]);

        $this->assertTrue($user->canTrackTime());
    }


}
