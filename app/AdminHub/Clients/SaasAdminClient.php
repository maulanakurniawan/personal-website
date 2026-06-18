<?php

namespace App\AdminHub\Clients;

use App\AdminHub\DTO\AdminApiResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SaasAdminClient
{
    public function get(string $productKey, string $endpoint, array $query = []): AdminApiResponse
    {
        return $this->request('get', $productKey, $endpoint, $query);
    }

    public function post(string $productKey, string $endpoint, array $payload = []): AdminApiResponse
    {
        return $this->request('post', $productKey, $endpoint, $payload);
    }

    public function patch(string $productKey, string $endpoint, array $payload = []): AdminApiResponse
    {
        return $this->request('patch', $productKey, $endpoint, $payload);
    }

    public function delete(string $productKey, string $endpoint, array $payload = []): AdminApiResponse
    {
        return $this->request('delete', $productKey, $endpoint, $payload);
    }

    public function listResources(string $productKey): AdminApiResponse { return $this->get($productKey, 'resources'); }
    public function getResourceSchema(string $productKey, string $resourceKey): AdminApiResponse { return $this->get($productKey, "resources/$resourceKey/schema"); }
    public function listResourceItems(string $productKey, string $resourceKey, array $query = []): AdminApiResponse { return $this->get($productKey, "resources/$resourceKey", $query); }
    public function getResourceItem(string $productKey, string $resourceKey, string|int $id): AdminApiResponse { return $this->get($productKey, "resources/$resourceKey/$id"); }
    public function createResourceItem(string $productKey, string $resourceKey, array $data): AdminApiResponse { return $this->post($productKey, "resources/$resourceKey", $data); }
    public function updateResourceItem(string $productKey, string $resourceKey, string|int $id, array $data): AdminApiResponse { return $this->patch($productKey, "resources/$resourceKey/$id", $data); }
    public function deleteResourceItem(string $productKey, string $resourceKey, string|int $id): AdminApiResponse { return $this->delete($productKey, "resources/$resourceKey/$id"); }
    public function restoreResourceItem(string $productKey, string $resourceKey, string|int $id): AdminApiResponse { return $this->post($productKey, "resources/$resourceKey/$id/restore"); }
    public function runResourceBulkAction(string $productKey, string $resourceKey, string $action, array $ids): AdminApiResponse { return $this->post($productKey, "resources/$resourceKey/bulk-actions", ['action' => $action, 'ids' => $ids]); }

    private function request(string $method, string $productKey, string $endpoint, array $payload = []): AdminApiResponse
    {
        $product = config("admin-hub.products.$productKey");
        if (! is_array($product)) {
            throw new InvalidArgumentException('Unknown product key.');
        }

        if (blank($product['base_url'] ?? null) || blank($product['client_id'] ?? null) || blank($product['client_secret'] ?? null)) {
            return new AdminApiResponse(false, error: ['code' => 'not_configured', 'message' => 'Product API credentials are not configured.'], status: 500);
        }

        $url = rtrim($product['base_url'], '/').'/'.ltrim($endpoint, '/');

        try {
            $response = Http::timeout(10)->acceptJson()->withHeaders([
                'X-Admin-Client-Id' => $product['client_id'],
                'X-Admin-Client-Secret' => $product['client_secret'],
                'X-Admin-Hub' => 'maulanakurniawan.com',
            ])->{$method}($url, $payload);
        } catch (ConnectionException $e) {
            Log::warning('Admin Hub API connection failed.', ['product' => $productKey, 'endpoint' => $endpoint, 'message' => $e->getMessage()]);
            return new AdminApiResponse(false, error: ['code' => 'connection_failed', 'message' => 'Unable to reach product API.'], status: 503);
        }

        $json = $response->json();
        if (is_array($json) && array_key_exists('success', $json)) {
            return AdminApiResponse::fromArray($json, $response->status());
        }

        if (! $response->successful()) {
            Log::warning('Admin Hub API request failed.', ['product' => $productKey, 'endpoint' => $endpoint, 'status' => $response->status()]);
        }

        $messages = [401 => 'Unauthorized', 403 => 'Forbidden', 404 => 'Not found', 422 => 'Validation failed'];
        return new AdminApiResponse(
            $response->successful(),
            $response->successful() && is_array($json) ? $json : [],
            status: $response->status(),
            error: $response->successful() ? null : ['code' => 'api_error', 'message' => $messages[$response->status()] ?? 'Product API error.'],
        );
    }
}
