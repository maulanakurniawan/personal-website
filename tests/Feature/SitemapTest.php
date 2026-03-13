<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    public function test_it_includes_indexable_public_marketing_pages_and_article_pages(): void
    {
        $response = $this->get(route('sitemap'));

        $response->assertOk();

        $xml = $response->getContent();

        $publicRouteNames = [
            'home',
            'pricing',
            'contact.show',
            'guides',
            'terms',
            'privacy',
        ];

        foreach ($publicRouteNames as $routeName) {
            $this->assertStringContainsString(route($routeName), $xml);
        }

        $this->assertStringNotContainsString(route('signup'), $xml);
        $this->assertStringNotContainsString(route('login'), $xml);

        $articleSlugs = collect(File::files(resource_path('views/articles')))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.blade.php'))
            ->map(fn ($file) => str($file->getFilename())->before('.blade.php')->toString());

        foreach ($articleSlugs as $slug) {
            $this->assertStringContainsString(route('article.show', ['slug' => $slug]), $xml);
        }
    }
}
