<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    public function test_contact_form_sends_message(): void
    {
        Mail::fake();
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
        ]);

        config()->set('services.turnstile.secret_key', 'test-secret');

        $response = $this->post('/contact', [
            'name' => 'Maulana',
            'email' => 'test@example.com',
            'subject' => 'Project inquiry',
            'message' => 'Need help with Laravel architecture.',
            'cf-turnstile-response' => 'token',
        ]);

        $response->assertSessionHas('success');
        Mail::assertSentCount(1);
    }
}
