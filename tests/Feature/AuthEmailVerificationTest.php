<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\CustomVerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_signup_sends_verification_email_and_redirects_to_verify_notice(): void
    {
        config(['auth.signup_enabled' => true]);
        Notification::fake();
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
        ]);
        config(['services.turnstile.secret_key' => 'test-secret']);

        $response = $this->post(route('signup.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Str0ng!Pass123',
            'password_confirmation' => 'Str0ng!Pass123',
            'cf-turnstile-response' => 'captcha-token',
        ]);

        $response->assertRedirect(route('verification.notice'));

        $user = User::firstWhere('email', 'test@example.com');

        $this->assertNotNull($user);
        Notification::assertSentTo($user, CustomVerifyEmailNotification::class);
    }

    public function test_unverified_user_is_redirected_to_verify_notice_after_login(): void
    {
        $user = User::factory()->unverified()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_verified_user_is_redirected_to_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_dashboard_requires_verified_email(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_unverified_user_can_verify_email_on_first_click_after_logging_in(): void
    {
        $user = User::factory()->unverified()->create([
            'password' => bcrypt('password123'),
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $this->get($verificationUrl)->assertRedirect(route('login'));

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect($verificationUrl);

        $this->get($verificationUrl)->assertRedirect(route('dashboard'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
