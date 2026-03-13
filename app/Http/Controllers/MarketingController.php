<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class MarketingController extends Controller
{
    public function home(): View
    {
        return view('welcome');
    }

    public function about(): View
    {
        return view('about');
    }

    public function manawan(): View
    {
        return view('manawan');
    }

    public function articles(): View
    {
        return view('guides', [
            'articles' => $this->articleCollection(),
        ]);
    }

    public function article(string $slug): View
    {
        $view = "articles.{$slug}";
        abort_unless(view()->exists($view), 404);

        return view($view);
    }

    private function articleCollection()
    {
        return collect(File::files(resource_path('views/articles')))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.blade.php'))
            ->map(function ($file) {
                $slug = str($file->getFilename())->before('.blade.php')->toString();

                return [
                    'slug' => $slug,
                    'title' => str($slug)->replace('-', ' ')->title()->toString(),
                ];
            })
            ->sortBy('title')
            ->values();
    }
}
