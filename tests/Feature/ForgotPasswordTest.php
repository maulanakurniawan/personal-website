<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_contains_forgot_password_link(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee(route('password.request'), false);
        $response->assertSee('Forgot password?');
    }

    public function test_user_can_request_password_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status', __((string) Password::RESET_LINK_SENT));

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('old-password'),
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', __((string) Password::PASSWORD_RESET));

        $this->assertTrue(password_verify('new-password-123', $user->fresh()->password));
    }
}
