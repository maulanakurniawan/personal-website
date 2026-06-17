<?php

namespace App\AdminHub\Middleware;

use App\Models\InternalAdminClient;
use Closure;
use Illuminate\Http\Request;

class AuthenticateInternalAdminClient
{
    public function handle(Request $request, Closure $next, string $scope = 'read')
    {
        $id = $request->header('X-Admin-Client-Id');
        $secret = $request->header('X-Admin-Client-Secret');

        if (! $id || ! $secret) {
            return $this->error('unauthorized', 'Unauthorized', 401);
        }

        $client = InternalAdminClient::where('client_id', $id)->first();
        if (! $client || ! $client->secretMatches($secret)) {
            return $this->error('unauthorized', 'Unauthorized', 401);
        }

        if (! $client->is_active || $client->revoked_at || ! $client->hasScope($scope)) {
            return $this->error('forbidden', 'Forbidden', 403);
        }

        $allowedIps = $client->allowed_ips ?: [];
        if ($allowedIps !== [] && ! in_array($request->ip(), $allowedIps, true)) {
            return $this->error('forbidden', 'Forbidden', 403);
        }

        $client->forceFill(['last_used_at' => now(), 'last_used_ip' => $request->ip()])->save();
        $request->attributes->set('internal_admin_client', $client);

        return $next($request);
    }

    private function error(string $code, string $message, int $status)
    {
        return response()->json(['success' => false, 'product' => 'maulanakurniawan', 'error' => ['code' => $code, 'message' => $message]], $status);
    }
}
