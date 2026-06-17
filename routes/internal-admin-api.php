<?php

use App\AdminHub\Controllers\InternalAdminApiController;
use App\AdminHub\Middleware\AuthenticateInternalAdminClient;
use App\AdminHub\Middleware\EnsureInternalAdminApiEnabled;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureInternalAdminApiEnabled::class])->prefix('api/internal/admin/v1')->group(function () {
    Route::middleware([AuthenticateInternalAdminClient::class.':read'])->group(function () {
        Route::get('/health', [InternalAdminApiController::class, 'health']);
        Route::get('/overview', [InternalAdminApiController::class, 'overview']);
        Route::get('/users', [InternalAdminApiController::class, 'users']);
        Route::get('/users/{id}', [InternalAdminApiController::class, 'user']);
        Route::get('/subscriptions', [InternalAdminApiController::class, 'subscriptions']);
        Route::get('/subscriptions/{id}', [InternalAdminApiController::class, 'subscription']);
        Route::get('/analytics', [InternalAdminApiController::class, 'analytics']);
        Route::get('/settings', [InternalAdminApiController::class, 'settings']);
        Route::get('/audit-logs', [InternalAdminApiController::class, 'auditLogs']);
        Route::get('/product-resources', [InternalAdminApiController::class, 'resources']);
    });
    Route::middleware([AuthenticateInternalAdminClient::class.':write'])->group(function () {
        Route::post('/users/{id}/actions', [InternalAdminApiController::class, 'userAction']);
        Route::patch('/settings', [InternalAdminApiController::class, 'updateSettings']);
    });
});
