<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketingController::class, 'home'])->name('home');
Route::get('/about', [MarketingController::class, 'about'])->name('about');
Route::get('/menawan', [MarketingController::class, 'menawan'])->name('menawan');
Route::get('/articles', [MarketingController::class, 'articles'])->name('articles.index');
Route::get('/guides', fn () => redirect()->route('articles.index', status: 301))->name('guides');
Route::get('/article/{slug}', [MarketingController::class, 'article'])->name('article.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy', 'privacy')->name('privacy');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', function () {
    $content = "User-agent: *\nAllow: /\n\nSitemap: ".route('sitemap')."\n";

    return response($content, 200, ['Content-Type' => 'text/plain']);
})->name('robots');
