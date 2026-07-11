<?php

namespace App\AdminHub\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\AdminUser;
use App\Models\ValidationLead;
use Illuminate\Http\Request;

class InternalAdminApiController extends Controller
{
    public function health()
    {
        return $this->ok(['status' => 'ok']);
    }

    public function overview()
    {
        return $this->ok([
            'product' => ['name' => 'MaulanaKurniawan', 'domain' => 'maulanakurniawan.com'],
            'metrics' => ['admin_users' => AdminUser::count(), 'audit_logs' => AdminAuditLog::count()],
            'health' => ['status' => 'ok'],
            'recent_activity' => AdminAuditLog::latest()->limit(10)->get(),
        ]);
    }

    public function users()
    {
        return $this->ok(['items' => []], ['message' => 'Public site users are not implemented.']);
    }

    public function user(string $id)
    {
        return $this->error('not_found', 'User not found.', 404);
    }

    public function subscriptions()
    {
        return $this->ok(['items' => []], ['message' => 'Subscriptions are not implemented for this site.']);
    }

    public function subscription(string $id)
    {
        return $this->error('not_found', 'Subscription not found.', 404);
    }

    public function analytics(Request $request)
    {
        return $this->ok(['period' => $request->query('period', '28d'), 'metrics' => []]);
    }

    public function settings()
    {
        return $this->ok(['items' => [['key' => 'site_name', 'value' => 'Maulana Kurniawan', 'editable' => false]]]);
    }

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

    public function deleteUser(Request $request, string $id)
    {
        $this->audit($request, 'users.delete', 'user', $id, ['delete_related' => true]);

        return $this->error('unsupported', 'User deletion is not supported for this site.', 422);
    }

    public function auditLogs(Request $request)
    {
        return $this->ok(['items' => AdminAuditLog::latest()->limit(50)->get()]);
    }

    public function resources()
    {
        $views = collect(glob(resource_path('views/articles/*.blade.php')) ?: [])->map(fn ($path) => basename($path, '.blade.php'))->values();

        return $this->ok([
            'cards' => [['title' => 'Articles', 'value' => $views->count()], ['title' => 'Contact form', 'value' => 'email-only'], ['title' => 'Validation Leads', 'value' => ValidationLead::count()]],
            'tables' => ['articles' => $views],
            'resources' => [$this->validationLeadResourceSchema()],
        ]);
    }

    public function resourceSchema(string $resourceKey)
    {
        if ($resourceKey !== 'validation_leads') {
            return $this->error('not_found', 'Resource not found.', 404);
        }

        return $this->ok($this->validationLeadResourceSchema());
    }

    public function resourceItems(Request $request, string $resourceKey)
    {
        if ($resourceKey !== 'validation_leads') {
            return $this->error('not_found', 'Resource not found.', 404);
        }

        $query = ValidationLead::query();
        foreach (['product_key', 'status', 'locale', 'target_category', 'price_interest', 'price_seen_currency', 'utm_source'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->query($field));
            }
        }
        if ($request->filled('search')) {
            $search = '%'.$request->query('search').'%';
            $query->where(fn ($q) => $q->where('email', 'like', $search)->orWhere('product_key', 'like', $search)->orWhere('product_name', 'like', $search)->orWhere('notes', 'like', $search)->orWhere('target_category', 'like', $search));
        }

        return $this->ok(['items' => $query->latest()->limit(100)->get()]);
    }

    public function resourceItem(string $resourceKey, string $id)
    {
        if ($resourceKey !== 'validation_leads') {
            return $this->error('not_found', 'Resource not found.', 404);
        }

        return $this->ok(['item' => ValidationLead::findOrFail($id)]);
    }

    public function updateResourceItem(Request $request, string $resourceKey, string $id)
    {
        if ($resourceKey !== 'validation_leads') {
            return $this->error('not_found', 'Resource not found.', 404);
        }

        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:'.implode(',', ValidationLead::STATUSES)],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'target_category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'price_interest' => ['sometimes', 'nullable', 'in:yes,maybe,no'],
        ]);
        $lead = ValidationLead::findOrFail($id);
        $lead->fill($validated)->save();

        return $this->ok(['item' => $lead->fresh()]);
    }

    private function validationLeadResourceSchema(): array
    {
        return [
            'key' => 'validation_leads', 'label' => 'Validation Leads', 'description' => 'Waitlist and validation leads from small SaaS idea pages', 'operations' => ['view', 'update'],
            'list_columns' => ['id', 'product_key', 'email', 'status', 'submission_count', 'last_submitted_at'],
            'searchable' => ['email', 'product_key', 'product_name', 'notes', 'target_category'],
            'filterable' => ['product_key', 'status', 'locale', 'target_category', 'price_interest', 'price_seen_currency', 'utm_source', 'created_at'],
            'sortable' => ['id', 'product_key', 'email', 'status', 'submission_count', 'last_submitted_at', 'created_at', 'updated_at'],
            'update_fields' => ['status', 'notes', 'target_category', 'price_interest'],
            'fields' => ['id', 'product_key', 'product_name', 'source_url', 'email', 'locale', 'target_category', 'price_interest', 'notes', 'price_seen_currency', 'price_seen_amount', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'ip_hash', 'user_agent', 'status', 'submission_count', 'last_submitted_at', 'created_at', 'updated_at'],
        ];
    }

    private function audit(Request $request, string $action, string $targetType, ?string $targetId, array $payload): void
    {
        $client = $request->attributes->get('internal_admin_client');
        AdminAuditLog::create(['admin_source' => 'client_credentials', 'admin_identifier' => $client?->client_id ?? 'unknown', 'product_key' => 'maulanakurniawan', 'action' => $action, 'target_type' => $targetType, 'target_id' => $targetId, 'payload' => $payload, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]);
    }

    private function ok(array $data = [], array $meta = [])
    {
        return response()->json(['success' => true, 'product' => 'maulanakurniawan', 'data' => $data, 'meta' => $meta]);
    }

    private function error(string $code, string $message, int $status)
    {
        return response()->json(['success' => false, 'product' => 'maulanakurniawan', 'error' => ['code' => $code, 'message' => $message]], $status);
    }
}
