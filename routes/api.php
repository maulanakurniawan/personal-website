<?php

use Illuminate\Support\Facades\Route;

Route::get('/status', fn () => ['ok' => true]);

Route::match(['get', 'post'], '/webhooks/stripe', function (Request $request) {
    return response()->json([
        'ok' => true,
        'source' => 'stripe-simulation',
        'message' => 'Simulated webhook endpoint is reachable.',
    ], 200);
});

Route::match(['get', 'post'], '/webhooks/github', function (Request $request) {
    return response()->json([
        'ok' => true,
        'source' => 'github-simulation',
        'message' => 'Simulated webhook endpoint is reachable.',
    ], 200);
});

Route::match(['get', 'post'], '/webhooks/paddle', function (Request $request) {
    return response()->json([
        'ok' => false,
        'source' => 'paddle-simulation',
        'message' => 'Simulated failure response.',
    ], 500);
});