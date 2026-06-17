<?php

namespace App\AdminHub\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureInternalAdminApiEnabled
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(config('internal-admin-api.enabled'), 404);
        return $next($request);
    }
}
