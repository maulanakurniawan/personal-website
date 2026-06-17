<?php

namespace App\AdminHub\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class InternalAdminApiController extends Controller
{
    public function health() { return $this->ok(['status' => 'ok']); }

    public function overview()
    {
        return $this->ok([
            'product' => ['name' => 'MaulanaKurniawan', 'domain' => 'maulanakurniawan.com'],
            'metrics' => ['admin_users' => AdminUser::count(), 'audit_logs' => AdminAuditLog::count()],
            'health' => ['status' => 'ok'],
            'recent_activity' => AdminAuditLog::latest()->limit(10)->get(),
        ]);
    }

    public function users() { return $this->ok(['items' => []], ['message' => 'Public site users are not implemented.']); }
    public function user(string $id) { return $this->error('not_found', 'User not found.', 404); }
    public function subscriptions() { return $this->ok(['items' => []], ['message' => 'Subscriptions are not implemented for this site.']); }
    public function subscription(string $id) { return $this->error('not_found', 'Subscription not found.', 404); }
    public function analytics(Request $request) { return $this->ok(['period' => $request->query('period', '28d'), 'metrics' => []]); }
    public function settings() { return $this->ok(['items' => [['key' => 'site_name', 'value' => 'Maulana Kurniawan', 'editable' => false]]]); }

    public function updateSettings(Request $request)
    {
        $this->audit($request, 'settings.update', 'settings', null, $request->except([]));
        return $this->error('unsupported', 'Settings writes are not supported yet.', 422);
    }

    public function userAction(Request $request, string $id)
    {
        $this->audit($request, 'users.action', 'user', $id, $request->all());
        return $this->error('unsupported', 'User actions are not supported for this site.', 422);
    }

    public function auditLogs(Request $request)
    {
        return $this->ok(['items' => AdminAuditLog::latest()->limit(50)->get()]);
    }

    public function resources()
    {
        $views = collect(glob(resource_path('views/articles/*.blade.php')) ?: [])->map(fn ($path) => basename($path, '.blade.php'))->values();
        return $this->ok(['cards' => [['title' => 'Articles', 'value' => $views->count()], ['title' => 'Contact form', 'value' => 'email-only']], 'tables' => ['articles' => $views]]);
    }

    private function audit(Request $request, string $action, string $targetType, ?string $targetId, array $payload): void
    {
        $client = $request->attributes->get('internal_admin_client');
        AdminAuditLog::create(['admin_source' => 'client_credentials', 'admin_identifier' => $client?->client_id ?? 'unknown', 'product_key' => 'maulanakurniawan', 'action' => $action, 'target_type' => $targetType, 'target_id' => $targetId, 'payload' => $payload, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]);
    }

    private function ok(array $data = [], array $meta = []) { return response()->json(['success' => true, 'product' => 'maulanakurniawan', 'data' => $data, 'meta' => $meta]); }
    private function error(string $code, string $message, int $status) { return response()->json(['success' => false, 'product' => 'maulanakurniawan', 'error' => ['code' => $code, 'message' => $message]], $status); }
}
