<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SignupAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_signup_page_shows_contact_prompt_when_signup_is_disabled(): void
    {
        config(['auth.signup_enabled' => false]);

        $response = $this->get(route('signup'));

        $response->assertOk();
        $response->assertSee('Signup is currently unavailable');
        $response->assertSee('Contact us');
    }

    public function test_signup_page_shows_registration_form_when_signup_is_enabled(): void
    {
        config(['auth.signup_enabled' => true]);

        $response = $this->get(route('signup'));

        $response->assertOk();
        $response->assertSee('Create your SoloHours account');
        $response->assertSee('By signing up, you agree to our', false);
        $response->assertSee(route('terms'), false);
        $response->assertSee(route('privacy'), false);
        $response->assertSee('Turnstile is currently unavailable. Please try again later.');
        $response->assertDontSee('name="plan"', false);
    }

    public function test_signup_post_redirects_to_signup_page_when_signup_is_disabled(): void
    {
        config(['auth.signup_enabled' => false]);

        $response = $this->post(route('signup.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('signup'));
    }

    public function test_signup_creates_user_without_assigning_a_plan(): void
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
        $this->assertNull($user->plan);
        $this->assertAuthenticatedAs($user);
    }

    public function test_signup_fails_when_turnstile_verification_fails(): void
    {
        config(['auth.signup_enabled' => true]);
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
        ]);
        config(['services.turnstile.secret_key' => 'test-secret']);

        $response = $this->from(route('signup'))->post(route('signup.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Str0ng!Pass123',
            'password_confirmation' => 'Str0ng!Pass123',
            'cf-turnstile-response' => 'captcha-token',
        ]);

        $response->assertRedirect(route('signup'));
        $response->assertSessionHasErrors('turnstile');
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }
}
