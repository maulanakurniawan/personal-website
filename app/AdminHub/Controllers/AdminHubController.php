<?php

namespace App\AdminHub\Controllers;

use App\AdminHub\Clients\SaasAdminClient;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminHubController extends Controller
{
    public function __construct(private SaasAdminClient $client) {}

    public function home()
    {
        $default = config('admin-hub.default_product', 'webhookwatch');
        abort_unless(array_key_exists($default, config('admin-hub.products', [])), 404);
        return redirect()->route('admin.product.overview', ['productKey' => $default]);
    }

    public function overview(string $productKey) { return $this->page($productKey, 'overview', 'overview'); }
    public function users(Request $request, string $productKey) { return $this->page($productKey, 'users', 'users', $request->query()); }
    public function userDetail(string $productKey, string $id) { return $this->page($productKey, 'user-detail', "users/$id"); }
    public function subscriptions(string $productKey) { return $this->page($productKey, 'subscriptions', 'subscriptions'); }
    public function subscriptionDetail(string $productKey, string $id) { return $this->page($productKey, 'subscription-detail', "subscriptions/$id"); }
    public function analytics(Request $request, string $productKey) { return $this->page($productKey, 'analytics', 'analytics', $request->query()); }
    public function settings(string $productKey) { return $this->page($productKey, 'settings', 'settings'); }
    public function auditLogs(Request $request, string $productKey) { return $this->page($productKey, 'audit-logs', 'audit-logs', $request->query()); }
    public function resources(string $productKey) { return $this->page($productKey, 'resources', 'product-resources'); }

    public function userAction(Request $request, string $productKey, string $id)
    {
        $response = $this->client->post($productKey, "users/$id/actions", $request->except('_token'));
        return back()->with($response->success ? 'status' : 'error', $response->success ? 'Action submitted.' : ($response->error['message'] ?? 'Action failed.'));
    }

    public function updateSettings(Request $request, string $productKey)
    {
        $response = $this->client->patch($productKey, 'settings', $request->except('_token', '_method'));
        return back()->with($response->success ? 'status' : 'error', $response->success ? 'Settings updated.' : ($response->error['message'] ?? 'Update failed.'));
    }

    private function page(string $productKey, string $section, string $endpoint, array $query = [])
    {
        $response = $this->client->get($productKey, $endpoint, $query);
        return view('admin.pages.generic', [
            'productKey' => $productKey,
            'product' => config("admin-hub.products.$productKey"),
            'section' => $section,
            'response' => $response,
            'data' => $response->data,
            'meta' => $response->meta,
        ]);
    }
}
