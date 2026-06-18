# Admin Hub

The Admin Hub is the private central UI at `/admin` for managing WebhookWatch, SoloHours, MaulanaKurniawan, and future SaaS products through secure product APIs.

## Configuration

Add each product in `config/admin-hub.php` and provide its internal API base URL, client ID, and client secret through environment variables. Credentials stay server-side and are sent only by the Laravel HTTP client.

```env
ADMIN_HUB_WEBHOOKWATCH_URL=https://webhookwatch.com/api/internal/admin/v1
ADMIN_HUB_WEBHOOKWATCH_CLIENT_ID=...
ADMIN_HUB_WEBHOOKWATCH_CLIENT_SECRET=...
```

## Generic resource UI

The Admin Hub includes schema-driven CRUD pages under `/admin/{productKey}/resources`. It calls each SaaS internal admin API for an allowlisted resource catalog, then renders resource cards, tables, detail pages, create/edit forms, delete/restore actions, and optional bulk actions without exposing raw JSON to admins.

Resource routes include:

- `GET /admin/{productKey}/resources`
- `GET /admin/{productKey}/resources/{resourceKey}`
- `GET|POST /admin/{productKey}/resources/{resourceKey}/create` (GET create form, POST collection route)
- `GET /admin/{productKey}/resources/{resourceKey}/{id}`
- `GET|PATCH /admin/{productKey}/resources/{resourceKey}/{id}/edit` (GET edit form, PATCH item route)
- `DELETE /admin/{productKey}/resources/{resourceKey}/{id}`
- `POST /admin/{productKey}/resources/{resourceKey}/{id}/restore`
- `POST /admin/{productKey}/resources/{resourceKey}/bulk-actions`

## How resource schemas work

Each SaaS should expose:

- `GET /resources` for resource cards (`key`, `label`, `description`, `operations`, `danger_level`).
- `GET /resources/{resourceKey}/schema` for UI metadata.
- CRUD item endpoints for list/detail/create/update/delete/restore/bulk actions.

Important schema keys:

- `operations`: supported actions such as `view`, `create`, `update`, `delete`, and `restore`.
- `list_columns`: table columns; columns may be `sortable`.
- `fields`: detail fields.
- `create_fields` / `update_fields`: form fields. If omitted, `fields` with `creatable` / `editable` flags are used.
- `searchable` or `searchable_fields`: enables the search box.
- `filters`: renders filter inputs.
- `bulk_actions`: enables checkbox selection and bulk action submission.

Supported field types include `text`, `textarea`, `email`, `number`, `money`, `boolean`, `select`, `date`, `datetime`, `url`, `json`, `badge`, and `status`.

## Adding a new SaaS resource

1. Add the resource to the SaaS API's `/resources` response.
2. Ensure the resource is safe and intentionally allowlisted by that SaaS.
3. Implement the schema endpoint with list columns, fields, operations, and optional filters/bulk actions.
4. Implement only the CRUD endpoints that the schema advertises.
5. Visit `/admin/{productKey}/resources/{resourceKey}` in the hub.

## CRUD action flow

All browser actions post back to Laravel Admin Hub controllers. The browser never calls SaaS APIs directly. `SaasAdminClient` sends server-side requests to the configured internal API using `X-Admin-Client-Id`, `X-Admin-Client-Secret`, and `X-Admin-Hub` headers. Validation errors from `422` responses are normalized and displayed on forms.

## Security notes

The Admin Hub is protected by the dedicated `admin` guard. Do not expose client secrets, API keys, `.env` values, password hashes, hidden fields, raw exception traces, or stack traces in views. Product APIs should only expose resources that are explicitly safe for central administration.

## Why resources are allowlisted

The generic UI can create, update, delete, restore, and bulk update records based on schemas. Each SaaS must therefore publish only resources and fields that are safe for Admin Hub operators. Dangerous resources should be omitted or marked with an appropriate danger level and restricted operations.

## Troubleshooting API errors

- `Product API credentials are not configured`: check product URL, client ID, and client secret environment variables.
- `Unauthorized` / `Forbidden`: rotate or recreate the internal admin client on the SaaS and verify scopes.
- `Not found`: confirm the SaaS exposes the resource key in `/resources` and supports the schema endpoint.
- `Validation failed`: the form displays normalized field errors from the SaaS response.
- Connection failures: verify the SaaS internal admin API URL and network access from the Admin Hub server.

## Admin users

Create admin users with:

```bash
php artisan admin-user:create "Maulana Kurniawan" "your-email@example.com"
```

Reset passwords with:

```bash
php artisan admin-user:reset-password "your-email@example.com"
```

Other commands: `admin-user:list`, `admin-user:disable`, `admin-user:enable`, and `admin-user:delete`.
