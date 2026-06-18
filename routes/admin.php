<?php

use App\AdminHub\Controllers\AdminAuthController;
use App\AdminHub\Controllers\AdminHubController;
use App\AdminHub\Controllers\AdminResourceController;
use App\AdminHub\Middleware\EnsureAdminHubEnabled;
use App\AdminHub\Middleware\EnsureValidProductKey;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureAdminHubEnabled::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.store');

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', [AdminHubController::class, 'home'])->name('home');
        Route::middleware(EnsureValidProductKey::class)->prefix('{productKey}')->name('product.')->group(function () {
            Route::get('/overview', [AdminHubController::class, 'overview'])->name('overview');
            Route::get('/users', [AdminHubController::class, 'users'])->name('users');
            Route::get('/users/{id}', [AdminHubController::class, 'userDetail'])->name('users.show');
            Route::post('/users/{id}/actions', [AdminHubController::class, 'userAction'])->name('users.actions');
            Route::get('/subscriptions', [AdminHubController::class, 'subscriptions'])->name('subscriptions');
            Route::get('/subscriptions/{id}', [AdminHubController::class, 'subscriptionDetail'])->name('subscriptions.show');
            Route::get('/analytics', [AdminHubController::class, 'analytics'])->name('analytics');
            Route::get('/settings', [AdminHubController::class, 'settings'])->name('settings');
            Route::patch('/settings', [AdminHubController::class, 'updateSettings'])->name('settings.update');
            Route::get('/audit-logs', [AdminHubController::class, 'auditLogs'])->name('audit-logs');
            Route::get('/resources', [AdminResourceController::class, 'index'])->name('resources');
            Route::get('/resources/{resourceKey}', [AdminResourceController::class, 'showTable'])->name('resources.table');
            Route::get('/resources/{resourceKey}/create', [AdminResourceController::class, 'create'])->name('resources.create');
            Route::post('/resources/{resourceKey}', [AdminResourceController::class, 'store'])->name('resources.store');
            Route::get('/resources/{resourceKey}/{id}', [AdminResourceController::class, 'detail'])->name('resources.show');
            Route::get('/resources/{resourceKey}/{id}/edit', [AdminResourceController::class, 'edit'])->name('resources.edit');
            Route::patch('/resources/{resourceKey}/{id}', [AdminResourceController::class, 'update'])->name('resources.update');
            Route::delete('/resources/{resourceKey}/{id}', [AdminResourceController::class, 'destroy'])->name('resources.destroy');
            Route::post('/resources/{resourceKey}/{id}/restore', [AdminResourceController::class, 'restore'])->name('resources.restore');
            Route::post('/resources/{resourceKey}/bulk-actions', [AdminResourceController::class, 'bulk'])->name('resources.bulk');
        });
    });
});
