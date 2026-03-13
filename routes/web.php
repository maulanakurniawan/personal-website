<?php

use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Webhooks\PaddleWebhookController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketingController::class, 'welcome'])->name('home');
Route::get('/pricing', [MarketingController::class, 'pricing'])->name('pricing');
Route::get('/guides', [MarketingController::class, 'guides'])->name('guides');
Route::get('/article/{slug}', function (string $slug) {
    $view = "articles.{$slug}";

    abort_unless(view()->exists($view), 404);

    return view($view);
})->name('article.show');
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy', 'privacy')->name('privacy');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', function () {
    $content = "User-agent: *\nAllow: /\n\nSitemap: ".route('sitemap')."\n";

    return response($content, 200, ['Content-Type' => 'text/plain']);
})->name('robots');

Route::get('/signup', [AuthController::class, 'showSignup'])->middleware('guest')->name('signup');
Route::post('/signup', [AuthController::class, 'signup'])->middleware('guest')->name('signup.store');
Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login.store');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->middleware('guest')->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->middleware('guest')->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->middleware('guest')->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('guest')->name('password.update');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('dashboard');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::post('/webhooks/paddle', PaddleWebhookController::class)->name('webhooks.paddle');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->middleware('verified')->name('dashboard');
    Route::post('/billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/history', [BillingController::class, 'history'])->name('billing.history');
    Route::post('/billing/subscription/cancel', [BillingController::class, 'cancelSubscription'])
        ->name('billing.subscription.cancel');
    Route::post('/billing/subscription/preview', [BillingController::class, 'previewSubscriptionChange'])
        ->name('billing.subscription.preview');
    Route::post('/billing/subscription/change', [BillingController::class, 'updateSubscription'])
        ->name('billing.subscription.change');
    Route::get('/billing/success', [BillingController::class, 'success'])->name('billing.success');
    Route::get('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/delete', [ProfileController::class, 'confirmDelete'])->name('profile.delete.confirm');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::patch('/clients/{clientId}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{clientId}', [ClientController::class, 'destroy'])->name('clients.destroy');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::patch('/projects/{projectId}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{projectId}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::get('/time-entries', [TimeEntryController::class, 'index'])->name('time-entries.index');
    Route::post('/time-entries', [TimeEntryController::class, 'store'])->name('time-entries.store');
    Route::patch('/time-entries/{entryId}', [TimeEntryController::class, 'update'])->name('time-entries.update');
    Route::delete('/time-entries/{entryId}', [TimeEntryController::class, 'destroy'])->name('time-entries.destroy');
    Route::post('/timer/start', [TimeEntryController::class, 'start'])->name('timer.start');
    Route::get('/timer/task-suggestions', [TimeEntryController::class, 'taskSuggestions'])->name('timer.task-suggestions');
    Route::post('/timer/stop', [TimeEntryController::class, 'stop'])->name('timer.stop');
    Route::post('/timer/continue/{entryId}', [TimeEntryController::class, 'continue'])->name('timer.continue');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::post('/reports/mark-invoiced', [ReportController::class, 'markInvoiced'])->name('reports.mark-invoiced');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    });
});
