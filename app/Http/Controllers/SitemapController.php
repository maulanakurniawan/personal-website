<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $publicRouteNames = [
            'home',
            'pricing',
            'contact.show',
            'guides',
            'terms',
            'privacy',
        ];

        $urls = collect($publicRouteNames)->map(function (string $routeName) {
            $loc = route($routeName);

            return [
                'loc' => $loc,
                'lastmod' => now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => $routeName === 'home' ? '1.0' : '0.8',
            ];
        });

        $articleUrls = collect(File::files(resource_path('views/articles')))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.blade.php'))
            ->map(function ($file) {
                $slug = str($file->getFilename())->before('.blade.php')->toString();

                return [
                    'loc' => route('article.show', ['slug' => $slug]),
                    'lastmod' => date('Y-m-d', $file->getMTime()),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            })
            ->sortBy('loc')
            ->values();

        $allUrls = $urls->concat($articleUrls)->values();

        $xml = view('sitemap', ['urls' => $allUrls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
