<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminHubLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_sets_a_session_token_that_can_submit_login_form(): void
    {
        config(['admin-hub.enabled' => true]);

        AdminUser::create([
            'name' => 'Mobile Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('correct-password'),
            'is_active' => true,
        ]);

        $loginPage = $this->get('/admin/login');

        $loginPage->assertOk();
        $token = session()->token();

        $response = $this->post('/admin/login', [
            '_token' => $token,
            'email' => 'admin@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('admin.home'));
        $this->assertAuthenticated('admin');
    }
}
