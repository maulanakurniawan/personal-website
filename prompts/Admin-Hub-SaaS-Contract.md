# Admin Hub SaaS Implementation Prompt

Use this prompt in any SaaS repository that needs to integrate with the central Admin Hub at `maulanakurniawan.com/admin`.

## Goal

Expose a secure internal admin API that the central Admin Hub can call. Do not build a standalone admin UI in this SaaS. All admin screens must live in the central Admin Hub; this SaaS only provides JSON endpoints.

## Authentication

Protect every endpoint under `/api/internal/admin/v1/*` with internal admin client authentication.

Required request headers:

```http
X-Admin-Client-Id: <client id generated in this SaaS>
X-Admin-Client-Secret: <client secret generated in this SaaS>
X-Admin-Hub: maulanakurniawan.com
Accept: application/json
```

Clients should support scopes:

- `read` for GET endpoints.
- `write` for POST, PATCH, and DELETE endpoints.

If internal admin API is disabled, all endpoints must return `404`.
If credentials are missing or invalid, return `401`.
If credentials are valid but inactive, revoked, IP-blocked, or scope-blocked, return `403`.

## Response format

All endpoints must return consistent JSON:

```json
{
  "success": true,
  "data": {},
  "meta": {}
}
```

Errors must use:

```json
{
  "success": false,
  "error": {
    "code": "validation_failed",
    "message": "Validation failed.",
    "validation": {}
  },
  "meta": {}
}
```

## Core endpoints

Implement these endpoints when the underlying product data exists:

| Method | Endpoint | Scope | Purpose |
| --- | --- | --- | --- |
| GET | `/api/internal/admin/v1/health` | read | Return API status, product key/name, app version if available. |
| GET | `/api/internal/admin/v1/overview` | read | Return dashboard cards/metrics for the SaaS. |
| GET | `/api/internal/admin/v1/users` | read | List users with pagination/search/filter metadata. |
| GET | `/api/internal/admin/v1/users/{id}` | read | Show one user and safe related summary data. |
| POST | `/api/internal/admin/v1/users/{id}/actions` | write | Run a single-user action such as disable, enable, impersonation-token, reset flags, or add admin note. |
| DELETE | `/api/internal/admin/v1/users/{id}` | write | Delete a user and related data only when explicitly safe for this SaaS. |
| GET | `/api/internal/admin/v1/subscriptions` | read | List subscriptions, plans, billing states, or entitlements. |
| GET | `/api/internal/admin/v1/subscriptions/{id}` | read | Show one subscription/billing record. |
| GET | `/api/internal/admin/v1/analytics` | read | Return product analytics/usage metrics. |
| GET | `/api/internal/admin/v1/settings` | read | Return admin-editable product settings. |
| PATCH | `/api/internal/admin/v1/settings` | write | Update safe admin-editable product settings. |
| GET | `/api/internal/admin/v1/audit-logs` | read | Return admin API audit logs. |

## Generic resource CRUD endpoints

Use these endpoints for safe admin-manageable resources. The central Admin Hub renders them dynamically from schemas.

| Method | Endpoint | Scope | Purpose |
| --- | --- | --- | --- |
| GET | `/api/internal/admin/v1/resources` | read | List safe resources available to Admin Hub. |
| GET | `/api/internal/admin/v1/resources/{resourceKey}/schema` | read | Return schema, fields, list columns, filters, sort options, and supported single-record operations. |
| GET | `/api/internal/admin/v1/resources/{resourceKey}` | read | List records for one resource. Support query params such as `search`, `sort`, `page`, `per_page`, and `filters[...]`. |
| GET | `/api/internal/admin/v1/resources/{resourceKey}/{id}` | read | Return one record. |
| POST | `/api/internal/admin/v1/resources/{resourceKey}` | write | Create one record if the resource supports create. |
| PATCH | `/api/internal/admin/v1/resources/{resourceKey}/{id}` | write | Update one record if the resource supports update. |
| DELETE | `/api/internal/admin/v1/resources/{resourceKey}/{id}` | write | Delete or archive one record if the resource supports delete. |
| POST | `/api/internal/admin/v1/resources/{resourceKey}/{id}/restore` | write | Restore one soft-deleted/archived record if supported. |

Do not implement bulk action endpoints. The central Admin Hub only supports per-row View, Edit, Delete/Archive, and Restore actions.

## Resource schema contract

Each resource returned by `/resources` and `/resources/{resourceKey}/schema` should use fields like:

```json
{
  "key": "users",
  "label": "Users",
  "description": "Safe user records",
  "operations": ["view", "create", "update", "delete"],
  "read_only": false,
  "danger_level": "normal",
  "soft_deletes": true,
  "list_columns": [
    { "key": "email", "label": "Email", "type": "email", "sortable": true },
    { "key": "active", "label": "Active", "type": "boolean" }
  ],
  "fields": [
    { "key": "email", "label": "Email", "type": "email", "creatable": true, "editable": true, "required": true },
    { "key": "notes", "label": "Notes", "type": "textarea", "creatable": true, "editable": true }
  ],
  "searchable": ["email", "name"],
  "filters": [
    { "key": "status", "label": "Status", "type": "select", "options": ["active", "disabled"] }
  ],
  "sortable": ["email", "created_at"]
}
```

The `operations` array should only advertise supported single-record actions. Use any of these equivalent names if already present in the SaaS: `view`/`read`/`show`, `create`/`store`, `update`/`edit`, `delete`/`destroy`, and `restore`.

## Safety requirements

- Never expose passwords, API secrets, tokens, private keys, raw payment details, or sensitive hashes.
- Validate every write request server-side.
- Return `422` with validation errors for invalid writes.
- Create an audit log for every write action, including actor/client id, action, target type/id, request IP, and timestamp.
- Disable old standalone admin routes/views/controllers or make them return `404`/redirect to the central Admin Hub.
- Add automated tests for auth failure, disabled API, read endpoints, write endpoints, validation errors, audit logging, and disabled old admin UI.
