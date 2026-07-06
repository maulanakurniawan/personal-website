<?php

namespace App\AdminHub\Clients;

use App\AdminHub\DTO\AdminApiResponse;
use App\Models\ValidationLead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
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

    public function listResources(string $productKey): AdminApiResponse
    {
        return $this->get($productKey, 'resources');
    }

    public function getResourceSchema(string $productKey, string $resourceKey): AdminApiResponse
    {
        return $this->get($productKey, "resources/$resourceKey/schema");
    }

    public function listResourceItems(string $productKey, string $resourceKey, array $query = []): AdminApiResponse
    {
        return $this->get($productKey, "resources/$resourceKey", $query);
    }

    public function getResourceItem(string $productKey, string $resourceKey, string|int $id): AdminApiResponse
    {
        return $this->get($productKey, "resources/$resourceKey/$id");
    }

    public function createResourceItem(string $productKey, string $resourceKey, array $data): AdminApiResponse
    {
        return $this->post($productKey, "resources/$resourceKey", $data);
    }

    public function updateResourceItem(string $productKey, string $resourceKey, string|int $id, array $data): AdminApiResponse
    {
        return $this->patch($productKey, "resources/$resourceKey/$id", $data);
    }

    public function deleteResourceItem(string $productKey, string $resourceKey, string|int $id): AdminApiResponse
    {
        return $this->delete($productKey, "resources/$resourceKey/$id");
    }

    public function restoreResourceItem(string $productKey, string $resourceKey, string|int $id): AdminApiResponse
    {
        return $this->post($productKey, "resources/$resourceKey/$id/restore");
    }

    private function localMaulanakurniawanResourceRequest(string $method, string $endpoint, array $payload = []): AdminApiResponse
    {
        $parts = explode('/', trim($endpoint, '/'));
        if (($parts[0] ?? null) !== 'resources') {
            return new AdminApiResponse(false, error: ['code' => 'not_found', 'message' => 'Resource endpoint not found.'], status: 404);
        }

        if (count($parts) === 1 && $method === 'get') {
            return new AdminApiResponse(true, ['resources' => [$this->validationLeadResourceSchema()]], status: 200);
        }

        $resourceKey = $parts[1] ?? null;
        if ($resourceKey !== 'validation_leads') {
            return new AdminApiResponse(false, error: ['code' => 'not_found', 'message' => 'Resource not found.'], status: 404);
        }

        if (($parts[2] ?? null) === 'schema' && $method === 'get') {
            return new AdminApiResponse(true, $this->validationLeadResourceSchema(), status: 200);
        }

        if (count($parts) === 2 && $method === 'get') {
            $query = ValidationLead::query();
            foreach (['product_key', 'status', 'locale', 'target_category', 'price_interest', 'price_seen_currency', 'utm_source'] as $field) {
                if (filled($payload[$field] ?? null)) {
                    $query->where($field, $payload[$field]);
                }
            }
            if (filled($payload['search'] ?? null)) {
                $search = '%'.$payload['search'].'%';
                $query->where(fn ($q) => $q->where('email', 'like', $search)->orWhere('product_key', 'like', $search)->orWhere('product_name', 'like', $search)->orWhere('notes', 'like', $search)->orWhere('target_category', 'like', $search));
            }

            return new AdminApiResponse(true, ['items' => $query->latest()->limit(100)->get()->toArray()], status: 200);
        }

        $id = $parts[2] ?? null;
        if ($id && count($parts) === 3 && $method === 'get') {
            $lead = ValidationLead::find($id);

            return $lead
                ? new AdminApiResponse(true, ['item' => $lead->toArray()], status: 200)
                : new AdminApiResponse(false, error: ['code' => 'not_found', 'message' => 'Validation lead not found.'], status: 404);
        }

        if ($id && count($parts) === 3 && $method === 'patch') {
            $validator = Validator::make($payload, [
                'status' => ['sometimes', 'string', 'in:'.implode(',', ValidationLead::STATUSES)],
                'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
                'target_category' => ['sometimes', 'nullable', 'string', 'max:100'],
                'price_interest' => ['sometimes', 'nullable', 'in:yes,maybe,no'],
            ]);
            if ($validator->fails()) {
                return new AdminApiResponse(false, error: ['code' => 'validation_failed', 'message' => 'Validation failed.', 'validation' => $validator->errors()->toArray()], status: 422);
            }
            $lead = ValidationLead::find($id);
            if (! $lead) {
                return new AdminApiResponse(false, error: ['code' => 'not_found', 'message' => 'Validation lead not found.'], status: 404);
            }
            $lead->fill($validator->validated())->save();

            return new AdminApiResponse(true, ['item' => $lead->fresh()->toArray()], status: 200);
        }

        return new AdminApiResponse(false, error: ['code' => 'unsupported', 'message' => 'This resource action is not supported.'], status: 422);
    }

    private function validationLeadResourceSchema(): array
    {
        return [
            'key' => 'validation_leads', 'label' => 'Validation Leads', 'description' => 'Waitlist and validation leads from small SaaS idea pages', 'operations' => ['view', 'update'],
            'list_columns' => ['id', 'product_key', 'email', 'locale', 'target_category', 'price_interest', 'price_seen_currency', 'price_seen_amount', 'status', 'submission_count', 'last_submitted_at', 'created_at'],
            'searchable' => ['email', 'product_key', 'product_name', 'notes', 'target_category'],
            'filterable' => ['product_key', 'status', 'locale', 'target_category', 'price_interest', 'price_seen_currency', 'utm_source', 'created_at'],
            'sortable' => ['id', 'product_key', 'email', 'status', 'submission_count', 'last_submitted_at', 'created_at', 'updated_at'],
            'update_fields' => ['status', 'notes', 'target_category', 'price_interest'],
            'fields' => ['id', 'product_key', 'product_name', 'source_url', 'email', 'locale', 'target_category', 'price_interest', 'notes', 'price_seen_currency', 'price_seen_amount', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'ip_hash', 'user_agent', 'status', 'submission_count', 'last_submitted_at', 'created_at', 'updated_at'],
        ];
    }

    private function request(string $method, string $productKey, string $endpoint, array $payload = []): AdminApiResponse
    {
        if ($productKey === 'maulanakurniawan' && str_starts_with($endpoint, 'resources')) {
            return $this->localMaulanakurniawanResourceRequest($method, $endpoint, $payload);
        }

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
        } catch (\Throwable $e) {
            Log::warning('Admin Hub API connection failed.', ['product' => $productKey, 'endpoint' => $endpoint, 'message' => $e->getMessage()]);

            return new AdminApiResponse(false, error: ['code' => 'connection_failed', 'message' => 'The product API could not be reached or returned an error.'], status: 503);
        }

        $json = $response->json();
        if (is_array($json) && array_key_exists('success', $json)) {
            return AdminApiResponse::fromArray($json, $response->status());
        }

        if (! $response->successful()) {
            Log::warning('Admin Hub API request failed.', ['product' => $productKey, 'endpoint' => $endpoint, 'status' => $response->status()]);
        }

        $messages = [401 => 'Admin Hub could not access this product. Please check API credentials and permissions.', 403 => 'Admin Hub could not access this product. Please check API credentials and permissions.', 404 => 'The requested resource or record was not found.', 422 => 'Validation failed'];

        return new AdminApiResponse(
            $response->successful(),
            $response->successful() && is_array($json) ? $json : [],
            status: $response->status(),
            error: $response->successful() ? null : ['code' => 'api_error', 'message' => $messages[$response->status()] ?? 'The product API could not be reached or returned an error.'],
        );
    }
}
