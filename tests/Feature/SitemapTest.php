<?php

namespace Tests\Feature;

use Tests\TestCase;

class SitemapTest extends TestCase
{
    public function test_sitemap_includes_public_routes_and_articles(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        foreach (['/', '/menawan', '/articles', '/contact', '/terms', '/privacy'] as $path) {
            $this->assertStringContainsString(url($path), $xml);
        }

        $this->assertStringContainsString(route('article.show', ['slug' => 'manual-vs-automatic-time-tracking']), $xml);
        $this->assertStringNotContainsString('/pricing', $xml);
        $this->assertStringNotContainsString('/dashboard', $xml);
    }
}
