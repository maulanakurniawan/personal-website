<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_public_pages_render(): void
    {
        foreach (['/', '/about', '/manawan', '/articles', '/contact', '/terms', '/privacy'] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_navigation_does_not_show_saas_routes(): void
    {
        $response = $this->get('/');

        $response->assertDontSee('Pricing');
        $response->assertDontSee('Dashboard');
        $response->assertDontSee('Signup');
        $response->assertSee('Manawan');
    }
}
