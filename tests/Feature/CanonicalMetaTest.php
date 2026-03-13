<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CanonicalMetaTest extends TestCase
{
    public function test_public_pages_render_with_solohours_branding(): void
    {
        $routes = ['home', 'pricing', 'terms', 'privacy'];

        foreach ($routes as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertOk();
            $response->assertSee('SoloHours');
        }
    }

    public function test_article_pages_remain_accessible(): void
    {
        $articleSlugs = collect(File::files(resource_path('views/articles')))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.blade.php'))
            ->map(fn ($file) => str($file->getFilename())->before('.blade.php')->toString());

        $this->assertCount(0, $articleSlugs);
    }
}
