You are working on one Laravel SaaS repo in my SaaS ecosystem.

This prompt is for SaaS product repos only, for example:

* webhookwatch.com
* solohours.com
* future SaaS products

Do NOT build the central Admin Hub UI in this repo.

The central Admin Hub will live only on:

```txt
maulanakurniawan.com/admin
```

This SaaS repo should expose only a secure internal admin API that the central Admin Hub can call.

Main goal:

Create a secure internal admin API for this SaaS, remove or disable any old standalone admin panel in this SaaS, and add Artisan commands to create/manage internal API client credentials.

Use Laravel 11 conventions where possible.

Before coding, inspect the current repo and summarize:

1. Which product this repo appears to be.
2. Existing routes related to admin/dashboard/manage/backend.
3. Existing user/subscription/payment/settings/product models.
4. What internal admin API endpoints can be implemented with existing data.
5. What old admin UI routes/views/controllers will be disabled.
6. Implementation plan.

Then proceed.

============================================================
ARCHITECTURE
============

Final architecture:

```txt
maulanakurniawan.com/admin = central Admin Hub UI

this-saas.com/api/internal/admin/v1/... = secure internal admin API only
```

Rules:

* Do NOT connect this SaaS database directly to maulanakurniawan.com.
* Do NOT build admin UI in this SaaS.
* Do NOT keep old standalone admin UI active.
* Do expose secure internal admin API endpoints.
* Do use client ID and client secret authentication.
* Do add audit logs for write actions.
* Do keep product public/user-facing functionality untouched.

============================================================
CONFIG
======

Add config if needed:

```php
'product_key' => env('PRODUCT_KEY', 'webhookwatch'),
'product_name' => env('PRODUCT_NAME', 'WebhookWatch'),
'product_domain' => env('PRODUCT_DOMAIN', 'webhookwatch.com'),
```

Add env examples:

```env
PRODUCT_KEY=webhookwatch
PRODUCT_NAME=WebhookWatch
PRODUCT_DOMAIN=webhookwatch.com

INTERNAL_ADMIN_API_ENABLED=true
```

For SoloHours, use:

```env
PRODUCT_KEY=solohours
PRODUCT_NAME=SoloHours
PRODUCT_DOMAIN=solohours.com
```

============================================================
INTERNAL ADMIN API ROUTES
=========================

Create internal admin API routes:

```txt
GET    /api/internal/admin/v1/health
GET    /api/internal/admin/v1/overview
GET    /api/internal/admin/v1/users
GET    /api/internal/admin/v1/users/{id}
POST   /api/internal/admin/v1/users/{id}/actions
GET    /api/internal/admin/v1/subscriptions
GET    /api/internal/admin/v1/subscriptions/{id}
GET    /api/internal/admin/v1/analytics
GET    /api/internal/admin/v1/settings
PATCH  /api/internal/admin/v1/settings
GET    /api/internal/admin/v1/audit-logs
GET    /api/internal/admin/v1/product-resources
```

Use a dedicated route file if suitable:

```txt
routes/internal-admin.php
```

Register it using the current Laravel routing style.

All internal admin API routes must be protected by middleware:

```php
InternalAdminAuth
```

If:

```env
INTERNAL_ADMIN_API_ENABLED=false
```

then all internal admin API routes should return 404.

============================================================
CLIENT ID AND CLIENT SECRET AUTH
================================

Create table:

```txt
internal_admin_clients
```

Columns:

```txt
id
name
client_id
client_secret_hash
scopes JSON nullable
allowed_ips JSON nullable
is_active boolean default true
last_used_at nullable
last_used_ip nullable
revoked_at nullable
created_at
updated_at
```

Authentication headers:

```txt
X-Admin-Client-Id: {client_id}
X-Admin-Client-Secret: {client_secret}
X-Admin-Hub: maulanakurniawan.com
Accept: application/json
```

Rules:

* Store only hashed client secret.
* Never store plain client secret.
* Show plain client secret only once when generated.
* Generate strong random client ID.
* Generate strong random client secret.
* Use Laravel Hash for checking secret.
* Return JSON 401 for missing/invalid credentials.
* Return JSON 403 for inactive client, revoked client, missing scope, or disallowed IP.
* Update `last_used_at` and `last_used_ip` after successful authentication.
* Never expose client secrets in logs, views, exceptions, JavaScript, or API responses.
* Never accept credentials from query string.

Optional future improvement:

* HMAC request signing with timestamp and nonce.
* Do not implement unless it is clean and safe.

============================================================
SCOPES
======

Support scopes.

Suggested scopes:

```txt
read
write
users:read
users:write
subscriptions:read
subscriptions:write
settings:read
settings:write
analytics:read
audit-logs:read
resources:read
```

Read endpoints should require either:

```txt
read
```

or the specific read scope.

Write endpoints should require either:

```txt
write
```

or the specific write scope.

For v1, the Admin Hub client can use:

```txt
read,write
```

============================================================
INTERNAL ADMIN CLIENT ARTISAN COMMANDS
======================================

Create these Artisan commands:

```txt
php artisan internal-admin-client:create
php artisan internal-admin-client:list
php artisan internal-admin-client:rotate
php artisan internal-admin-client:revoke
php artisan internal-admin-client:enable
php artisan internal-admin-client:disable
```

Create client:

```txt
php artisan internal-admin-client:create "Admin Hub" --scopes=read,write --allowed-ips=1.2.3.4,5.6.7.8
```

Allowed IPs should be optional.

Output:

```txt
Client created successfully.

Name: Admin Hub
Client ID: generated_client_id
Client Secret: generated_client_secret

Important: copy this secret now. It will not be shown again.
```

List clients:

```txt
php artisan internal-admin-client:list
```

Show:

```txt
ID
Name
Client ID
Scopes
Allowed IPs
Active
Last Used At
Revoked At
Created At
```

Never show secrets.

Rotate secret:

```txt
php artisan internal-admin-client:rotate {client_id}
```

Output new secret once only.

Revoke client:

```txt
php artisan internal-admin-client:revoke {client_id}
```

Set:

```txt
revoked_at = now()
is_active = false
```

Enable/disable:

```txt
php artisan internal-admin-client:enable {client_id}
php artisan internal-admin-client:disable {client_id}
```

============================================================
STANDARD API RESPONSE FORMAT
============================

All internal admin APIs must return consistent JSON.

Success:

```json
{
  "success": true,
  "product": "webhookwatch",
  "data": {},
  "meta": {}
}
```

Error:

```json
{
  "success": false,
  "product": "webhookwatch",
  "error": {
    "code": "unauthorized",
    "message": "Unauthorized"
  }
}
```

Use the correct product key from config.

============================================================
HEALTH ENDPOINT
===============

Create:

```txt
GET /api/internal/admin/v1/health
```

Return:

```json
{
  "app": "ok",
  "database": "ok",
  "queue": "ok",
  "scheduler": "ok"
}
```

Do not expose sensitive server details.

============================================================
OVERVIEW ENDPOINT
=================

Create:

```txt
GET /api/internal/admin/v1/overview
```

Return:

```json
{
  "product": {
    "key": "webhookwatch",
    "name": "WebhookWatch",
    "domain": "webhookwatch.com",
    "environment": "production"
  },
  "metrics": {
    "users_total": 0,
    "users_today": 0,
    "users_last_7_days": 0,
    "active_subscriptions": 0,
    "trial_or_money_back_users": 0,
    "monthly_recurring_revenue": 0,
    "failed_payments": 0
  },
  "recent_activity": [],
  "health": {
    "app": "ok",
    "database": "ok",
    "queue": "ok",
    "scheduler": "ok"
  }
}
```

Use existing data where available.

If a metric cannot be calculated yet, return 0 or null and include it in:

```json
{
  "meta": {
    "missing_sources": ["traffic", "subscriptions"]
  }
}
```

============================================================
USERS ENDPOINT
==============

Create:

```txt
GET /api/internal/admin/v1/users
GET /api/internal/admin/v1/users/{id}
```

Support query params:

```txt
search=
status=
plan=
created_from=
created_to=
page=
per_page=
```

Return paginated users.

User object shape:

```json
{
  "id": 1,
  "name": "Example",
  "email": "example@email.com",
  "created_at": "2026-01-01T00:00:00Z",
  "last_login_at": null,
  "plan": "pro",
  "subscription_status": "active",
  "is_blocked": false,
  "stats": {}
}
```

Use available fields only. Do not fail if some fields do not exist.

============================================================
USER ACTIONS ENDPOINT
=====================

Create:

```txt
POST /api/internal/admin/v1/users/{id}/actions
```

Request example:

```json
{
  "action": "block"
}
```

Supported actions:

```txt
block
unblock
resend_verification_email
cancel_subscription
refresh_subscription_status
grant_coupon
add_admin_note
```

Rules:

* Require write scope.
* Validate action.
* Add audit log for every write action.
* For risky actions, return unsupported if not safely implemented yet.
* Do not fake success.

Unsupported response:

```json
{
  "success": false,
  "product": "webhookwatch",
  "error": {
    "code": "unsupported_action",
    "message": "This action is not supported for this product yet."
  }
}
```

============================================================
SUBSCRIPTIONS ENDPOINT
======================

Create:

```txt
GET /api/internal/admin/v1/subscriptions
GET /api/internal/admin/v1/subscriptions/{id}
```

Use local subscription/Paddle data if available.

Response item:

```json
{
  "id": 1,
  "user_id": 1,
  "user_email": "example@email.com",
  "plan": "pro",
  "status": "active",
  "provider": "paddle",
  "provider_subscription_id": null,
  "amount": 29,
  "currency": "USD",
  "started_at": null,
  "renews_at": null,
  "cancelled_at": null
}
```

If no subscription system exists, return empty paginated list with `meta.missing_sources`.

============================================================
ANALYTICS ENDPOINT
==================

Create:

```txt
GET /api/internal/admin/v1/analytics
```

Support:

```txt
period=today|7d|28d|90d|12m
```

Return:

```json
{
  "signups": [],
  "subscriptions": [],
  "revenue": [],
  "traffic": [],
  "conversion": {
    "visitor_to_signup": null,
    "signup_to_paid": null
  }
}
```

Use local data first.

If traffic data is unavailable, return empty arrays and add:

```json
{
  "meta": {
    "missing_sources": ["traffic"]
  }
}
```

============================================================
SETTINGS ENDPOINT
=================

Create:

```txt
GET   /api/internal/admin/v1/settings
PATCH /api/internal/admin/v1/settings
```

Expose safe app settings only, for example:

```txt
maintenance mode status
signup enabled/disabled
money-back days
plan limits
public pricing metadata
feature flags
```

Never expose:

```txt
API keys
Paddle secrets
database credentials
.env values
client secrets
mail credentials
server credentials
```

For PATCH:

* Require write scope.
* Validate allowed keys.
* Add audit log.
* If safe setting persistence does not exist yet, return unsupported instead of unsafe implementation.

============================================================
PRODUCT-SPECIFIC RESOURCES ENDPOINT
===================================

Create:

```txt
GET /api/internal/admin/v1/product-resources
```

Return product-specific management cards and tables.

For WebhookWatch, include what is available:

```txt
endpoints count
checks count
incidents count
latest failed checks
notification status
endpoint limit usage by user
```

For SoloHours, include what is available:

```txt
projects count
clients count
time entries count
uninvoiced hours
active timers
export usage
```

Response example:

```json
{
  "cards": [
    {
      "key": "endpoints",
      "label": "Endpoints",
      "value": 123,
      "description": "Total monitored endpoints"
    }
  ],
  "tables": [
    {
      "key": "latest_failed_checks",
      "label": "Latest Failed Checks",
      "columns": ["Endpoint", "Status", "Checked At"],
      "rows": []
    }
  ]
}
```

Design the response so the central Admin Hub UI can render generic cards/tables without knowing every product-specific database table.

============================================================
AUDIT LOGS
==========

Create table:

```txt
admin_audit_logs
```

Columns:

```txt
id
admin_source
admin_identifier
product_key
action
target_type
target_id
payload JSON nullable
ip_address nullable
user_agent nullable
created_at
updated_at
```

Every write action from internal admin API must create an audit log.

Create endpoint:

```txt
GET /api/internal/admin/v1/audit-logs
```

Support query params:

```txt
action=
target_type=
target_id=
created_from=
created_to=
page=
per_page=
```

============================================================
REMOVE OR DISABLE OLD ADMIN PANEL
=================================

Inspect for old admin routes/controllers/views/navigation links such as:

```txt
/admin
/admin/*
dashboard/admin
backend/*
manage/*
```

Goal:

This SaaS should not have standalone admin UI anymore.

Do this safely:

* Do not delete database tables.
* Do not delete user data.
* Do not delete useful business logic.
* Move reusable logic into services if needed.
* Disable old admin UI routes.
* Remove old admin links from navigation.
* Keep new internal admin API routes active.
* If old admin URLs are visited, return 404 or redirect to central Admin Hub.
* Add tests to confirm old admin routes are disabled.

Important:

Do NOT disable user-facing dashboard routes used by normal SaaS customers.
Only disable internal/admin/staff routes.

============================================================
TESTS
=====

Add tests for:

* Internal admin API disabled returns 404.
* Missing client ID returns 401.
* Missing client secret returns 401.
* Wrong client secret returns 401.
* Inactive client returns 403.
* Revoked client returns 403.
* Disallowed IP returns 403 if allowed IPs are set.
* Correct client credentials can access overview.
* Overview endpoint returns expected JSON shape.
* Users endpoint returns paginated JSON.
* Old standalone admin routes are disabled.
* Client create command displays secret once.
* Client list command does not show secrets.
* Client rotate command changes secret.
* Client revoke command disables access.

Use the current test style of the repo.

============================================================
DOCUMENTATION
=============

Create or update:

```txt
docs/internal-admin-api.md
```

Document:

* Required env variables.
* Route list.
* Auth headers.
* How to create client credentials.
* How to rotate client secret.
* How to revoke client.
* Scopes.
* Example curl request.
* How old admin panel was disabled.
* Deployment checklist.

Example curl:

```bash
curl -X GET "https://webhookwatch.com/api/internal/admin/v1/overview" \
  -H "Accept: application/json" \
  -H "X-Admin-Hub: maulanakurniawan.com" \
  -H "X-Admin-Client-Id: CLIENT_ID" \
  -H "X-Admin-Client-Secret: CLIENT_SECRET"
```

Deployment checklist:

```txt
1. Deploy code.
2. Run migrations.
3. Create internal admin client:
   php artisan internal-admin-client:create "Admin Hub" --scopes=read,write
4. Copy client ID and client secret once.
5. Add credentials to maulanakurniawan.com .env.
6. Confirm old admin URLs return 404 or redirect.
7. Confirm internal admin API overview works.
8. Confirm secrets are not exposed in logs/errors.
```

============================================================
CODE QUALITY
============

Follow current project style.

Use Laravel conventions.

Add files as needed:

```txt
migrations
models
controllers
middleware
services
requests
resources/transformers if useful
tests
docs
```

Do not hardcode secrets.

Do not expose secrets.

Do not change unrelated styling.

Do not break public pages.

Do not break customer dashboard features.

Do not break billing.

For dangerous write actions like cancel subscription, grant coupon, change plan, or edit settings:

* Validate carefully.
* Require write scope.
* Add audit log.
* Return unsupported if not safe yet.
* Prefer read-only implementation first if uncertain.

============================================================
FINAL RESPONSE REQUIRED FROM CODEX
==================================

After implementation, provide:

1. Detected product/repo role.
2. Files changed.
3. Migrations added.
4. Commands added.
5. Routes added.
6. Old admin routes disabled.
7. Tests added.
8. Manual deployment steps.
9. Limitations or TODOs.
