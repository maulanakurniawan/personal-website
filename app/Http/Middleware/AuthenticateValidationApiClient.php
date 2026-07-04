<?php

namespace App\Http\Middleware;

use App\Models\ValidationApiClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateValidationApiClient
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainKey = (string) $request->header('X-Validation-Api-Key', '');
        $productKey = (string) $request->header('X-Product-Key', '');

        if ($plainKey === '' || $productKey === '') {
            return $this->unauthorized();
        }

        $client = ValidationApiClient::query()
            ->where('product_key', $productKey)
            ->where('key_hash', hash('sha256', $plainKey))
            ->where('enabled', true)
            ->whereNull('revoked_at')
            ->first();

        if (! $client) {
            return $this->unauthorized();
        }

        if (! $this->sourceHostAllowed($request, $client)) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'forbidden_host', 'message' => 'The source host is not allowed for this validation API client.'],
            ], 403);
        }

        $request->attributes->set('validation_api_client', $client);
        $client->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }

    private function sourceHostAllowed(Request $request, ValidationApiClient $client): bool
    {
        $allowedHosts = array_values(array_filter($client->allowed_hosts ?? []));
        if ($allowedHosts === []) {
            return true;
        }

        $sourceUrl = (string) $request->input('source_url', '');
        $host = parse_url($sourceUrl, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return false;
        }

        $host = strtolower($host);
        $allowedHosts = array_map(fn ($allowed) => strtolower((string) $allowed), $allowedHosts);

        return in_array($host, $allowedHosts, true);
    }

    private function unauthorized(): Response
    {
        return response()->json([
            'success' => false,
            'error' => ['code' => 'unauthorized', 'message' => 'Invalid validation API credentials.'],
        ], 401);
    }
}
