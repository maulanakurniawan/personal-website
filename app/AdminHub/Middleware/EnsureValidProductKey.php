<?php

namespace App\AdminHub\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureValidProductKey
{
    public function handle(Request $request, Closure $next)
    {
        $productKey = $request->route('productKey');
        abort_unless(is_string($productKey) && array_key_exists($productKey, config('admin-hub.products', [])), 404);
        return $next($request);
    }
}
