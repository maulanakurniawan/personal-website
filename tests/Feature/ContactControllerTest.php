<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    public function test_contact_page_renders_cloudflare_turnstile_widget_when_configured(): void
    {
        config()->set('services.turnstile.site_key', 'site-key-123');

        $response = $this->get(route('contact.show'));

        $response->assertOk();
        $response->assertSee('cf-turnstile', false);
        $response->assertSee('site-key-123', false);
    }

    public function test_contact_form_sends_to_support_inbox(): void
    {
        Mail::fake();
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
        ]);

        config()->set('services.turnstile.secret_key', 'test-secret');

        $response = $this->post(route('contact.send'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Need help',
            'message' => 'Can you assist?',
            'cf-turnstile-response' => 'captcha-token',
        ]);

        $response->assertSessionHasNoErrors();

        Mail::assertSent(ContactFormMail::class);
    }

    public function test_contact_form_fails_when_turnstile_verification_fails(): void
    {
        Mail::fake();
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
        ]);

        config()->set('services.turnstile.secret_key', 'test-secret');

        $response = $this->from(route('contact.show'))->post(route('contact.send'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Need help',
            'message' => 'Can you assist?',
            'cf-turnstile-response' => 'captcha-token',
        ]);

        $response->assertRedirect(route('contact.show'));
        $response->assertSessionHasErrors('turnstile');

        Mail::assertNothingSent();
    }
}
