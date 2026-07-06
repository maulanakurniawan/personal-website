You are working on a Laravel SaaS repo that already has a secure internal admin API for the central Admin Hub.

The central Admin Hub lives on:

```txt id="r3hb1o"
maulanakurniawan.com/admin
```

This SaaS exposes:

```txt id="wep18n"
/api/internal/admin/v1/*
```

Now I want to extend the internal admin API so the central Admin Hub can manage SaaS data through safe CRUD endpoints.

Important:

Do NOT expose every database table automatically.
Do NOT expose secrets.
Do NOT expose password hashes.
Do NOT expose API tokens.
Do NOT expose internal client secrets.
Do NOT expose `.env` values.
Do NOT expose payment provider secrets.
Do NOT expose unsafe system tables.

Use an explicit allowlist/resource registry.

The goal is to expose CRUD only for safe admin-manageable resources.

Before coding, inspect the repo and summarize:

1. Existing internal admin API implementation.
2. Existing models/tables.
3. Which resources are safe to expose to Admin Hub.
4. Which resources should be read-only.
5. Which resources should support create/update/delete.
6. Which fields must be hidden.
7. Implementation plan.

Then proceed.

============================================================
MAIN GOAL
=========

Add a generic CRUD resource layer to the internal admin API.

Create endpoints like:

```txt id="8opz9m"
GET    /api/internal/admin/v1/resources
GET    /api/internal/admin/v1/resources/{resourceKey}/schema
GET    /api/internal/admin/v1/resources/{resourceKey}
GET    /api/internal/admin/v1/resources/{resourceKey}/{id}
POST   /api/internal/admin/v1/resources/{resourceKey}
PATCH  /api/internal/admin/v1/resources/{resourceKey}/{id}
DELETE /api/internal/admin/v1/resources/{resourceKey}/{id}
POST   /api/internal/admin/v1/resources/{resourceKey}/{id}/restore
```

All routes must use existing internal admin API authentication:

```txt id="u36x9o"
X-Admin-Client-Id
X-Admin-Client-Secret
X-Admin-Hub
Accept: application/json
```

All write routes must require write scope.

All write routes must create audit logs.

============================================================
RESOURCE REGISTRY
=================

Create a resource registry system.

Suggested structure:

```txt id="w5whlk"
app/InternalAdmin/Resources/
app/InternalAdmin/Resources/Contracts/
app/InternalAdmin/Resources/Definitions/
app/InternalAdmin/Controllers/ResourceController.php
app/InternalAdmin/Services/AdminResourceRegistry.php
```

Follow the existing repo style if different.

Each resource definition should describe:

```txt id="mk8o96"
resource key
label
model class
table name
primary key
available operations
list columns
detail fields
create fields
update fields
searchable fields
filterable fields
sortable fields
hidden fields
read-only fields
validation rules
relations allowed for display
danger level
soft delete support
```

Example resource config shape:

```php id="7iqwlp"
return [
    'users' => [
        'label' => 'Users',
        'model' => App\Models\User::class,
        'primary_key' => 'id',
        'operations' => ['list', 'view', 'update'],
        'searchable' => ['name', 'email'],
        'filterable' => ['created_at'],
        'sortable' => ['id', 'name', 'email', 'created_at'],
        'list_columns' => [
            'id',
            'name',
            'email',
            'created_at',
        ],
        'detail_fields' => [
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ],
        'create_fields' => [],
        'update_fields' => [
            'name',
            'email',
        ],
        'hidden_fields' => [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ],
        'validation' => [
            'update' => [
                'name' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
            ],
        ],
        'danger_level' => 'medium',
    ],
];
```

Do not hardcode only this example. Inspect the repo and add useful resources.

============================================================
WEBHOOKWATCH RESOURCE SUGGESTIONS
=================================

If this repo is WebhookWatch, consider safe resources such as:

```txt id="otfgbf"
users
endpoints
checks
incidents
notifications
subscription records
plans/pricing metadata if stored locally
coupons if stored locally
admin notes if available
```

Suggested permissions:

```txt id="w6d9ln"
users: list, view, update limited safe fields only
endpoints: list, view, create, update, delete/disable if safe
checks: list, view only
incidents: list, view, update status if safe
notifications: list, view only or update status if safe
subscriptions: list, view only by default
plans/pricing: read-only unless safely stored as app settings
```

Do not allow editing raw check history unless there is a clear product reason.

Do not allow deleting payment history.

============================================================
SOLOHOURS RESOURCE SUGGESTIONS
==============================

If this repo is SoloHours, consider safe resources such as:

```txt id="ceiv5i"
users
clients
projects
time entries
timers
subscription records
plans/pricing metadata if stored locally
coupons if stored locally
admin notes if available
```

Suggested permissions:

```txt id="5d741b"
users: list, view, update limited safe fields only
clients: list, view, create, update, delete if safe
projects: list, view, create, update, delete/archive if safe
time entries: list, view, create, update, delete if safe
timers: list, view, stop/cancel if safe
subscriptions: list, view only by default
```

Be careful with user-owned business data. Prefer soft delete/archive if available.

============================================================
DEFAULT CRUD BEHAVIOR
=====================

For each resource:

List endpoint:

```txt id="f5t4cl"
GET /api/internal/admin/v1/resources/{resourceKey}
```

Support query params:

```txt id="ecqlzt"
search=
filters[field]=value
sort=
direction=asc|desc
page=
per_page=
```

Return paginated response:

```json id="b9t58r"
{
  "success": true,
  "product": "webhookwatch",
  "data": {
    "items": [],
    "pagination": {
      "current_page": 1,
      "per_page": 25,
      "total": 0,
      "last_page": 1
    }
  },
  "meta": {
    "resource": "users"
  }
}
```

Schema endpoint:

```txt id="pzmpcp"
GET /api/internal/admin/v1/resources/{resourceKey}/schema
```

Return:

```json id="gnfghr"
{
  "success": true,
  "product": "webhookwatch",
  "data": {
    "key": "users",
    "label": "Users",
    "operations": ["list", "view", "update"],
    "list_columns": [
      {
        "key": "id",
        "label": "ID",
        "type": "number",
        "sortable": true
      },
      {
        "key": "email",
        "label": "Email",
        "type": "email",
        "sortable": true
      }
    ],
    "fields": [
      {
        "key": "name",
        "label": "Name",
        "type": "text",
        "editable": true,
        "required": false
      }
    ],
    "filters": []
  },
  "meta": {}
}
```

Detail endpoint:

```txt id="7mclj1"
GET /api/internal/admin/v1/resources/{resourceKey}/{id}
```

Create endpoint:

```txt id="yh7k63"
POST /api/internal/admin/v1/resources/{resourceKey}
```

Update endpoint:

```txt id="oysn5i"
PATCH /api/internal/admin/v1/resources/{resourceKey}/{id}
```

Delete endpoint:

```txt id="ad6fpg"
DELETE /api/internal/admin/v1/resources/{resourceKey}/{id}
```

Rules:

* If model supports soft deletes, use soft delete.
* If not safe to delete, return unsupported.
* Never hard delete unless explicitly safe.
* Always audit create/update/delete/restore actions.

Restore endpoint:

```txt id="67xuh0"
POST /api/internal/admin/v1/resources/{resourceKey}/{id}/restore
```

Only available for soft-deletable models.

Do not expose bulk action endpoints. The Admin Hub supports only single-record View, Create, Edit, Delete/Archive, and Restore actions.

============================================================
FIELD SAFETY
============

Always hide dangerous fields, including but not limited to:

```txt id="2w2p0r"
password
password_hash
remember_token
api_token
access_token
refresh_token
secret
client_secret
client_secret_hash
token
private_key
public_key only if sensitive in this app
paddle_secret
stripe_secret
webhook_secret
mail_password
database_password
two_factor_secret
two_factor_recovery_codes
```

Do not return hidden fields in list/detail/schema responses.

Do not allow hidden fields in create/update.

If request includes hidden fields, ignore them or return validation error.

============================================================
VALIDATION
==========

All create/update requests must validate only allowed fields.

Do not use `$request->all()` directly for mass assignment.

Use allowlisted fields only.

If a resource does not define create fields, create must return:

```json id="gp1hn3"
{
  "success": false,
  "product": "webhookwatch",
  "error": {
    "code": "operation_not_supported",
    "message": "Create is not supported for this resource."
  }
}
```

Same for update/delete/restore.

============================================================
AUDIT LOGGING
=============

Every write action must create an audit log.

Include:

```txt id="sylyf7"
admin_source
admin_identifier
product_key
action
target_type
target_id
payload
ip_address
user_agent
```

For update actions, include changed fields, but do not store secrets.

Example action names:

```txt id="j2xpgy"
resource.create
resource.update
resource.delete
resource.restore
```

============================================================
ERROR FORMAT
============

Use existing internal admin API error format:

```json id="l4pt0s"
{
  "success": false,
  "product": "webhookwatch",
  "error": {
    "code": "not_found",
    "message": "Resource not found."
  }
}
```

Common error codes:

```txt id="9qytwf"
resource_not_found
record_not_found
operation_not_supported
validation_failed
unauthorized
forbidden
server_error
```

============================================================
BACKWARD COMPATIBILITY
======================

Do not remove existing endpoints:

```txt id="srcsm5"
/health
/overview
/users
/subscriptions
/analytics
/settings
/audit-logs
/product-resources
```

The new generic CRUD endpoints are additional.

============================================================
TESTS
=====

Add tests for:

* Resource list endpoint requires authentication.
* Resource schema endpoint returns schema.
* Unknown resource returns 404 JSON.
* Hidden fields are not returned.
* Hidden fields cannot be updated.
* Read-only resource cannot be created/updated/deleted.
* Create requires write scope.
* Update requires write scope.
* Delete requires write scope.
* Update validates allowed fields.
* Write action creates audit log.
* Soft delete is used where available.
* List supports search/filter/sort/pagination.

Use existing test style.

============================================================
DOCUMENTATION
=============

Update:

```txt id="nj291h"
docs/internal-admin-api.md
```

Add:

```txt id="dy51ex"
CRUD resource endpoints
Resource registry explanation
Available resources in this SaaS
Read-only resources
Writable resources
Hidden fields
Example list request
Example schema request
Example create/update/delete request
Security notes
Audit logging notes
```

Example curl:

```bash id="l7kl50"
curl -X GET "https://webhookwatch.com/api/internal/admin/v1/resources/users?page=1&per_page=25" \
  -H "Accept: application/json" \
  -H "X-Admin-Hub: maulanakurniawan.com" \
  -H "X-Admin-Client-Id: CLIENT_ID" \
  -H "X-Admin-Client-Secret: CLIENT_SECRET"
```

============================================================
FINAL RESPONSE REQUIRED FROM CODEX
==================================

After implementation, provide:

1. Detected product.
2. Resources exposed.
3. Read-only resources.
4. Writable resources.
5. Hidden/protected fields.
6. Routes added.
7. Files changed.
8. Tests added.
9. Manual deployment steps.
10. Limitations/TODOs.
