<?php

namespace App\AdminHub\Controllers;

use App\AdminHub\Clients\SaasAdminClient;
use App\AdminHub\ResourceSchema;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class AdminResourceController extends Controller
{
    public function __construct(private SaasAdminClient $client) {}

    public function index(string $productKey)
    {
        return view('admin.resources.index', $this->base($productKey) + ['section' => 'resources', 'response' => $this->client->listResources($productKey)]);
    }

    public function showTable(Request $request, string $productKey, string $resourceKey)
    {
        $schema = $this->client->getResourceSchema($productKey, $resourceKey);
        if (! $schema->success) abort_if($schema->status === 404, 404); 
        $items = $schema->success ? $this->client->listResourceItems($productKey, $resourceKey, $request->query()) : $schema;
        return view('admin.resources.table', $this->base($productKey, $resourceKey) + ['section' => 'resources', 'schemaResponse' => $schema, 'itemsResponse' => $items, 'schema' => $schema->data]);
    }

    public function detail(string $productKey, string $resourceKey, string $id)
    {
        $schema = $this->client->getResourceSchema($productKey, $resourceKey);
        abort_if(! $schema->success && $schema->status === 404, 404);
        $item = $schema->success ? $this->client->getResourceItem($productKey, $resourceKey, $id) : $schema;
        abort_if(! $item->success && $item->status === 404, 404);
        return view('admin.resources.detail', $this->base($productKey, $resourceKey) + ['section' => 'resources', 'schemaResponse' => $schema, 'itemResponse' => $item, 'schema' => $schema->data, 'item' => $item->data['item'] ?? $item->data, 'id' => $id]);
    }

    public function create(string $productKey, string $resourceKey)
    {
        $schema = $this->client->getResourceSchema($productKey, $resourceKey); abort_if(! $schema->success || ! ResourceSchema::supports($schema->data, 'create'), 404);
        return view('admin.resources.form', $this->base($productKey, $resourceKey) + ['section' => 'resources', 'schema' => $schema->data, 'item' => [], 'mode' => 'create']);
    }

    public function store(Request $request, string $productKey, string $resourceKey)
    {
        $response = $this->client->createResourceItem($productKey, $resourceKey, $request->except('_token'));
        if (! $response->success) return $this->failedForm($response);
        $id = $response->data['id'] ?? data_get($response->data, 'item.id');
        return redirect($id ? route('admin.product.resources.show', [$productKey, $resourceKey, $id]) : route('admin.product.resources.table', [$productKey, $resourceKey]))->with('status', 'Resource created.');
    }

    public function edit(string $productKey, string $resourceKey, string $id)
    {
        $schema = $this->client->getResourceSchema($productKey, $resourceKey); abort_if(! $schema->success || ! ResourceSchema::supports($schema->data, 'update'), 404);
        $item = $this->client->getResourceItem($productKey, $resourceKey, $id); abort_if(! $item->success && $item->status === 404, 404);
        return view('admin.resources.form', $this->base($productKey, $resourceKey) + ['section' => 'resources', 'schema' => $schema->data, 'item' => $item->data['item'] ?? $item->data, 'mode' => 'edit', 'id' => $id]);
    }

    public function update(Request $request, string $productKey, string $resourceKey, string $id)
    {
        $response = $this->client->updateResourceItem($productKey, $resourceKey, $id, $request->except('_token', '_method'));
        if (! $response->success) return $this->failedForm($response);
        return redirect()->route('admin.product.resources.show', [$productKey, $resourceKey, $id])->with('status', 'Resource updated.');
    }

    public function destroy(string $productKey, string $resourceKey, string $id)
    {
        $response = $this->client->deleteResourceItem($productKey, $resourceKey, $id);
        return back()->with($response->success ? 'status' : 'error', $response->success ? 'Resource deleted.' : ($response->error['message'] ?? 'Delete failed.'));
    }

    public function restore(string $productKey, string $resourceKey, string $id)
    {
        $response = $this->client->restoreResourceItem($productKey, $resourceKey, $id);
        return back()->with($response->success ? 'status' : 'error', $response->success ? 'Resource restored.' : ($response->error['message'] ?? 'Restore failed.'));
    }

    public function bulk(Request $request, string $productKey, string $resourceKey)
    {
        $response = $this->client->runResourceBulkAction($productKey, $resourceKey, (string) $request->input('action'), Arr::wrap($request->input('ids', [])));
        return back()->with($response->success ? 'status' : 'error', $response->success ? 'Bulk action completed.' : ($response->error['message'] ?? 'Bulk action failed.'));
    }

    private function base(string $productKey, ?string $resourceKey = null): array { return ['productKey' => $productKey, 'product' => config("admin-hub.products.$productKey"), 'resourceKey' => $resourceKey]; }
    private function failedForm($response) { if ($response->status === 422) throw ValidationException::withMessages($response->error['validation'] ?? $response->error['errors'] ?? ['form' => $response->error['message'] ?? 'Validation failed.']); return back()->withInput()->with('error', $response->error['message'] ?? 'Request failed.'); }
}
