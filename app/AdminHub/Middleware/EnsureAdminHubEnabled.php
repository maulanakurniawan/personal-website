<?php

namespace App\AdminHub\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdminHubEnabled
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(config('admin-hub.enabled'), 404);
        return $next($request);
    }
}
