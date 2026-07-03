You are working on the Laravel repo for:

```txt
maulanakurniawan.com
```

This repo will host my central private Admin Hub at:

```txt
/admin
```

The Admin Hub will manage all my SaaS products from one place:

* WebhookWatch: webhookwatch.com
* SoloHours: solohours.com
* MaulanaKurniawan: maulanakurniawan.com
* Future SaaS products

Important:

This prompt is only for maulanakurniawan.com.

Do NOT remove `/admin`, because `/admin` is the new central Admin Hub.

The SaaS repos will expose secure internal admin APIs. This Admin Hub will call those APIs using client ID and client secret credentials.

Main goal:

Create a secure central Admin Hub UI, admin user login system, Admin Hub API client, product dropdown, and local internal admin API for maulanakurniawan.com itself.

Use Laravel 11 conventions where possible.

Before coding, inspect the current repo and summarize:

1. Existing auth system, if any.
2. Existing routes/views/layouts.
3. Existing contact/blog/page models, if any.
4. Whether `/admin` already exists.
5. Implementation plan.

Then proceed.

============================================================
TARGET ARCHITECTURE
===================

Final architecture:

```txt
maulanakurniawan.com/admin = central Admin Hub UI

webhookwatch.com/api/internal/admin/v1/... = secure internal admin API
solohours.com/api/internal/admin/v1/... = secure internal admin API
maulanakurniawan.com/api/internal/admin/v1/... = local internal admin API
future-saas.com/api/internal/admin/v1/... = secure internal admin API
```

Rules:

* Do NOT connect directly to other SaaS databases.
* Call each SaaS via its internal admin API.
* Keep API credentials server-side only.
* Do not expose API credentials in JavaScript, HTML, logs, or errors.
* Protect `/admin` with admin login.
* No public admin registration.
* Add Artisan command to create/manage Admin Hub users.
* Add Artisan command to create/manage local internal admin API clients.
* Add product dropdown so I can select which website to manage.

============================================================
ADMIN HUB CONFIG
================

Create config:

```txt
config/admin-hub.php
```

Example:

```php
return [
    'enabled' => env('ADMIN_HUB_ENABLED', false),

    'default_product' => env('ADMIN_HUB_DEFAULT_PRODUCT', 'webhookwatch'),

    'products' => [
        'webhookwatch' => [
            'name' => 'WebhookWatch',
            'domain' => 'webhookwatch.com',
            'base_url' => env('ADMIN_HUB_WEBHOOKWATCH_URL'),
            'client_id' => env('ADMIN_HUB_WEBHOOKWATCH_CLIENT_ID'),
            'client_secret' => env('ADMIN_HUB_WEBHOOKWATCH_CLIENT_SECRET'),
        ],

        'solohours' => [
            'name' => 'SoloHours',
            'domain' => 'solohours.com',
            'base_url' => env('ADMIN_HUB_SOLOHOURS_URL'),
            'client_id' => env('ADMIN_HUB_SOLOHOURS_CLIENT_ID'),
            'client_secret' => env('ADMIN_HUB_SOLOHOURS_CLIENT_SECRET'),
        ],

        'maulanakurniawan' => [
            'name' => 'MaulanaKurniawan',
            'domain' => 'maulanakurniawan.com',
            'base_url' => env('ADMIN_HUB_MAULANAKURNIAWAN_URL'),
            'client_id' => env('ADMIN_HUB_MAULANAKURNIAWAN_CLIENT_ID'),
            'client_secret' => env('ADMIN_HUB_MAULANAKURNIAWAN_CLIENT_SECRET'),
        ],
    ],
];
```

Env example:

```env
ADMIN_HUB_ENABLED=true
ADMIN_HUB_DEFAULT_PRODUCT=webhookwatch

ADMIN_HUB_WEBHOOKWATCH_URL=https://webhookwatch.com/api/internal/admin/v1
ADMIN_HUB_WEBHOOKWATCH_CLIENT_ID=
ADMIN_HUB_WEBHOOKWATCH_CLIENT_SECRET=

ADMIN_HUB_SOLOHOURS_URL=https://solohours.com/api/internal/admin/v1
ADMIN_HUB_SOLOHOURS_CLIENT_ID=
ADMIN_HUB_SOLOHOURS_CLIENT_SECRET=

ADMIN_HUB_MAULANAKURNIAWAN_URL=https://maulanakurniawan.com/api/internal/admin/v1
ADMIN_HUB_MAULANAKURNIAWAN_CLIENT_ID=
ADMIN_HUB_MAULANAKURNIAWAN_CLIENT_SECRET=
```

============================================================
ADMIN HUB MODULE STRUCTURE
==========================

Create structure similar to:

```txt
app/AdminHub/
app/AdminHub/Clients/
app/AdminHub/Controllers/
app/AdminHub/Middleware/
app/AdminHub/Services/
app/AdminHub/DTO/
config/admin-hub.php
routes/admin.php
resources/views/admin/
```

Follow the existing repo structure if it already has a better convention.

============================================================
ADMIN HUB ROUTES
================

Create pages:

```txt
GET  /admin/login
POST /admin/login
POST /admin/logout

GET /admin
GET /admin/{productKey}/overview
GET /admin/{productKey}/users
GET /admin/{productKey}/users/{id}
POST /admin/{productKey}/users/{id}/actions
GET /admin/{productKey}/subscriptions
GET /admin/{productKey}/subscriptions/{id}
GET /admin/{productKey}/analytics
GET /admin/{productKey}/settings
PATCH /admin/{productKey}/settings
GET /admin/{productKey}/audit-logs
GET /admin/{productKey}/resources
```

Behavior:

* `/admin` redirects to default product overview.
* All admin pages except login require admin authentication.
* If `ADMIN_HUB_ENABLED=false`, return 404 for all `/admin` routes including login.
* Product key must exist in `config/admin-hub.php`.
* Unknown product key returns 404.

============================================================
ADMIN USER AUTH SYSTEM
======================

If the existing site already has clean auth, reuse it only if suitable.

If not, create a separate admin auth system.

Create table:

```txt
admin_users
```

Columns:

```txt
id
name
email unique
password
is_active boolean default true
last_login_at nullable
created_at
updated_at
```

Rules:

* Store passwords using Laravel Hash.
* Session-based login.
* Separate admin login page.
* No public registration.
* Inactive admin users cannot log in.
* Add logout.
* Add basic login rate limiting.
* Protect all `/admin` pages.
* Do not expose password hashes.
* Do not expose exception traces.

Suggested routes:

```txt
GET  /admin/login
POST /admin/login
POST /admin/logout
```

============================================================
ADMIN USER ARTISAN COMMANDS
===========================

Create Artisan commands:

```txt
php artisan admin-user:create
php artisan admin-user:list
php artisan admin-user:disable
php artisan admin-user:enable
php artisan admin-user:reset-password
php artisan admin-user:delete
```

Create admin user:

```txt
php artisan admin-user:create "Maulana Kurniawan" "admin@example.com"
```

Behavior:

* Ask for password interactively if `--password` is not provided.
* Ask for password confirmation.
* Hash password.
* Create active admin user.
* Reject duplicate email.

Optional non-interactive usage:

```txt
php artisan admin-user:create "Maulana Kurniawan" "admin@example.com" --password="strong-password"
```

Output:

```txt
Admin user created successfully.

Name: Maulana Kurniawan
Email: admin@example.com
Active: yes
```

List admin users:

```txt
php artisan admin-user:list
```

Show:

```txt
ID
Name
Email
Active
Last Login At
Created At
```

Never show password hashes.

Disable:

```txt
php artisan admin-user:disable admin@example.com
```

Enable:

```txt
php artisan admin-user:enable admin@example.com
```

Reset password:

```txt
php artisan admin-user:reset-password admin@example.com
```

Optional:

```txt
php artisan admin-user:reset-password admin@example.com --password="new-strong-password"
```

Delete:

```txt
php artisan admin-user:delete admin@example.com
```

Ask confirmation before deleting.

============================================================
ADMIN HUB API CLIENT
====================

Create reusable client:

```php
App\AdminHub\Clients\SaasAdminClient
```

Responsibilities:

* Receive product key.
* Validate product key exists in config.
* Load base URL, client ID, and client secret from config.
* Call internal admin API endpoints.
* Add auth headers.
* Handle timeout.
* Handle 401, 403, 404, 422, 500 gracefully.
* Return normalized response.
* Log failures without exposing client secrets.
* Never put credentials in query string.

Use Laravel HTTP client.

Every request to SaaS internal API must include:

```txt
X-Admin-Client-Id: {client_id}
X-Admin-Client-Secret: {client_secret}
X-Admin-Hub: maulanakurniawan.com
Accept: application/json
```

============================================================
ADMIN HUB UI
============

Use simple Blade + Tailwind unless the app already uses another frontend approach.

UI requirements:

* Top product dropdown.
* Sidebar or nav for:

  * Overview
  * Users
  * Subscriptions
  * Analytics
  * Settings
  * Product Resources
  * Audit Logs
* Current selected product visible.
* API error states visible but safe.
* Loading/empty states where relevant.
* Do not expose credentials in HTML/JS.

Product dropdown behavior:

Current URL:

```txt
/admin/webhookwatch/users
```

When dropdown changes to SoloHours, redirect to:

```txt
/admin/solohours/users
```

If current section is not available, redirect to:

```txt
/admin/solohours/overview
```

============================================================
ADMIN HUB PAGES
===============

Overview:

```txt
/admin/{productKey}/overview
```

Call:

```txt
GET /overview
```

Render product info, metrics, recent activity, and health.

Users:

```txt
/admin/{productKey}/users
/admin/{productKey}/users/{id}
```

Call:

```txt
GET /users
GET /users/{id}
```

Support search/filter query params.

User actions:

```txt
POST /admin/{productKey}/users/{id}/actions
```

Call SaaS internal API:

```txt
POST /users/{id}/actions
```

Confirm risky actions before submitting where practical.

Subscriptions:

```txt
/admin/{productKey}/subscriptions
/admin/{productKey}/subscriptions/{id}
```

Call:

```txt
GET /subscriptions
GET /subscriptions/{id}
```

Analytics:

```txt
/admin/{productKey}/analytics?period=28d
```

Call:

```txt
GET /analytics?period=28d
```

Settings:

```txt
/admin/{productKey}/settings
```

Call:

```txt
GET /settings
PATCH /settings
```

Only render safe settings returned by the API.

Product Resources:

```txt
/admin/{productKey}/resources
```

Call:

```txt
GET /product-resources
```

Render generic cards and tables from response.

Audit Logs:

```txt
/admin/{productKey}/audit-logs
```

Call:

```txt
GET /audit-logs
```

Support filters.

============================================================
LOCAL INTERNAL ADMIN API FOR MAULANAKURNIAWAN.COM
=================================================

Also implement local internal admin API in this same repo:

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

For maulanakurniawan.com, users/subscriptions may be empty if the site has no users/subscriptions.

Product-specific resources may include available data such as:

```txt
contact messages
blog/articles
page views if available
personal website settings
```

Use the same response format as SaaS APIs.

Success:

```json
{
  "success": true,
  "product": "maulanakurniawan",
  "data": {},
  "meta": {}
}
```

Error:

```json
{
  "success": false,
  "product": "maulanakurniawan",
  "error": {
    "code": "unauthorized",
    "message": "Unauthorized"
  }
}
```

============================================================
LOCAL INTERNAL ADMIN CLIENT AUTH
================================

For the local maulanakurniawan.com internal admin API, also create client credential system.

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

Use same headers:

```txt
X-Admin-Client-Id: {client_id}
X-Admin-Client-Secret: {client_secret}
X-Admin-Hub: maulanakurniawan.com
Accept: application/json
```

Use same rules:

* Store only hashed secret.
* Show plain secret once.
* 401 for missing/invalid credentials.
* 403 for inactive/revoked/wrong scope/disallowed IP.
* Never expose secrets.

Add env flag:

```env
INTERNAL_ADMIN_API_ENABLED=true
```

If false, local internal admin API returns 404.

============================================================
LOCAL INTERNAL ADMIN CLIENT ARTISAN COMMANDS
============================================

Create commands:

```txt
php artisan internal-admin-client:create
php artisan internal-admin-client:list
php artisan internal-admin-client:rotate
php artisan internal-admin-client:revoke
php artisan internal-admin-client:enable
php artisan internal-admin-client:disable
```

These are used to create credentials for the Admin Hub to call maulanakurniawan.com's own local internal admin API.

Create client:

```txt
php artisan internal-admin-client:create "Admin Hub Local" --scopes=read,write
```

Output secret once only.

List command must never show secrets.

============================================================
LOCAL AUDIT LOGS
================

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

Every write action through the local internal admin API must create an audit log.

Admin Hub UI actions should rely on the target product API to create product-side audit logs.

============================================================
SECURITY
========

Admin Hub security rules:

* `/admin` must require admin authentication.
* `/admin/login` must be unavailable if `ADMIN_HUB_ENABLED=false`.
* No public admin registration.
* Admin passwords must be hashed.
* Admin login must be rate limited.
* Client credentials must stay server-side.
* Do not expose secrets in HTML.
* Do not expose secrets in JavaScript.
* Do not expose secrets in logs.
* Do not expose `.env`.
* Do not expose exception traces.

Internal API security rules:

* `/api/internal/admin/v1/*` must require client ID and secret.
* Use scopes.
* Use optional allowed IPs.
* Return 404 if disabled.
* Audit write actions.

============================================================
TESTS
=====

Add tests for Admin Hub:

* Admin Hub disabled returns 404.
* Admin login page loads when enabled.
* Active admin user can log in.
* Inactive admin user cannot log in.
* Unauthenticated user cannot access `/admin`.
* Admin user create command works.
* Admin user list command does not show password hashes.
* Admin user disable command blocks login.
* Admin user reset password command works.
* Product dropdown loads products from config.
* Unknown product key returns 404.
* Admin Hub client sends client ID and client secret headers.
* Admin Hub client handles successful API response.
* Admin Hub client handles 401/403/500 responses safely.

Add tests for local internal admin API:

* Internal admin API disabled returns 404.
* Missing client ID returns 401.
* Missing client secret returns 401.
* Wrong client secret returns 401.
* Inactive client returns 403.
* Revoked client returns 403.
* Disallowed IP returns 403 if allowed IPs are set.
* Correct client credentials can access overview.
* Overview endpoint returns expected JSON shape.
* Client create command displays secret once.
* Client list command does not show secrets.
* Client rotate command changes secret.
* Client revoke command disables access.

Use current test style.

============================================================
DOCUMENTATION
=============

Create or update:

```txt
docs/admin-hub.md
docs/internal-admin-api.md
```

Document:

* Required env variables.
* How to enable Admin Hub.
* How to create Admin Hub user.
* How to reset Admin Hub user password.
* How to add WebhookWatch credentials.
* How to add SoloHours credentials.
* How to add future SaaS.
* How to create local internal admin client.
* API auth headers.
* Security model.
* Deployment checklist.

Deployment checklist:

```txt
1. Deploy SaaS internal admin APIs first.
2. On each SaaS, run:
   php artisan internal-admin-client:create "Admin Hub" --scopes=read,write

3. Copy each client ID and secret once.

4. Add credentials to maulanakurniawan.com .env:
   ADMIN_HUB_WEBHOOKWATCH_CLIENT_ID=
   ADMIN_HUB_WEBHOOKWATCH_CLIENT_SECRET=
   ADMIN_HUB_SOLOHOURS_CLIENT_ID=
   ADMIN_HUB_SOLOHOURS_CLIENT_SECRET=

5. On maulanakurniawan.com, run migrations.

6. Create central admin user:
   php artisan admin-user:create "Maulana Kurniawan" "your-email@example.com"

7. Create local internal admin client:
   php artisan internal-admin-client:create "Admin Hub Local" --scopes=read,write

8. Add local credentials:
   ADMIN_HUB_MAULANAKURNIAWAN_CLIENT_ID=
   ADMIN_HUB_MAULANAKURNIAWAN_CLIENT_SECRET=

9. Set:
   ADMIN_HUB_ENABLED=true
   INTERNAL_ADMIN_API_ENABLED=true

10. Visit:
   /admin

11. Confirm product dropdown works.

12. Confirm API credentials are not exposed in logs, HTML, JavaScript, or errors.
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
views
tests
docs
```

Do not hardcode secrets.

Do not expose secrets.

Do not break public pages.

Do not change unrelated styling.

Do not remove user-facing website functionality.

Keep v1 safe and practical.

For risky write actions:

* Validate carefully.
* Require write scope.
* Add audit log.
* Return unsupported if not safely implemented.
* Prefer read-only implementation first if uncertain.

============================================================
FINAL RESPONSE REQUIRED FROM CODEX
==================================

After implementation, provide:

1. Detected repo state.
2. Files changed.
3. Migrations added.
4. Commands added.
5. Routes added.
6. Admin Hub pages added.
7. Local internal admin API endpoints added.
8. Tests added.
9. Manual deployment steps.
10. Limitations or TODOs.
