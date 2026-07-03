You are working on the Laravel repo for:

```txt
maulanakurniawan.com
```

This repo already has a central Admin Hub at:

```txt
/admin
```

The Admin Hub calls each SaaS internal admin API using client ID and client secret.

The SaaS repos expose CRUD-capable internal admin API resources.

Now I want to improve the Admin Hub UI.

Current issue:

Some Admin Hub pages show plain JSON responses from the SaaS APIs, and the admin panel does not clearly show Create, Edit, Delete, Restore, or Bulk Action buttons.

Main goal:

Replace plain JSON display with proper admin tables, filters, detail pages, CRUD forms, and visible CRUD buttons.

The Admin Hub should support generic CRUD UI for all safe resources exposed by each SaaS internal admin API.

This must work for:

```txt
webhookwatch
solohours
maulanakurniawan
future SaaS products
```

Do not hardcode only WebhookWatch or SoloHours.

The UI should render resources dynamically based on each SaaS resource schema.

Before coding, inspect the Admin Hub implementation and summarize:

1. Existing Admin Hub routes/controllers/views.
2. Existing SaaS API client.
3. Which pages currently render plain JSON.
4. Existing layout/components.
5. Existing product dropdown behavior.
6. Existing resource/API response structure.
7. Implementation plan.

Then proceed.

============================================================
API RESOURCE CONTRACT
=====================

The SaaS APIs expose endpoints like:

```txt
GET    /api/internal/admin/v1/resources
GET    /api/internal/admin/v1/resources/{resourceKey}/schema
GET    /api/internal/admin/v1/resources/{resourceKey}
GET    /api/internal/admin/v1/resources/{resourceKey}/{id}
POST   /api/internal/admin/v1/resources/{resourceKey}
PATCH  /api/internal/admin/v1/resources/{resourceKey}/{id}
DELETE /api/internal/admin/v1/resources/{resourceKey}/{id}
POST   /api/internal/admin/v1/resources/{resourceKey}/{id}/restore
POST   /api/internal/admin/v1/resources/{resourceKey}/bulk-actions
```

The Admin Hub must call those endpoints server-side through Laravel controllers.

Do not call SaaS APIs directly from browser-side JavaScript.

Do not expose API credentials in HTML, JavaScript, logs, errors, or API responses.

============================================================
ADMIN HUB ROUTES
================

Add or update routes:

```txt
GET    /admin/{productKey}/resources
GET    /admin/{productKey}/resources/{resourceKey}
GET    /admin/{productKey}/resources/{resourceKey}/create
POST   /admin/{productKey}/resources/{resourceKey}
GET    /admin/{productKey}/resources/{resourceKey}/{id}
GET    /admin/{productKey}/resources/{resourceKey}/{id}/edit
PATCH  /admin/{productKey}/resources/{resourceKey}/{id}
DELETE /admin/{productKey}/resources/{resourceKey}/{id}
POST   /admin/{productKey}/resources/{resourceKey}/{id}/restore
POST   /admin/{productKey}/resources/{resourceKey}/bulk-actions
```

Keep existing pages:

```txt
/admin/{productKey}/overview
/admin/{productKey}/users
/admin/{productKey}/subscriptions
/admin/{productKey}/analytics
/admin/{productKey}/settings
/admin/{productKey}/audit-logs
```

Where possible, make users/subscriptions/settings link into the generic resource system if the API exposes them as resources.

For example:

```txt
/admin/webhookwatch/users
```

may continue to exist, but it should use the same table/detail rendering approach as:

```txt
/admin/webhookwatch/resources/users
```

Do not break existing Admin Hub navigation.

============================================================
ADMIN HUB API CLIENT UPDATES
============================

Update the existing `SaasAdminClient` to support generic CRUD calls:

```php
listResources(string $productKey)

getResourceSchema(string $productKey, string $resourceKey)

listResourceItems(
    string $productKey,
    string $resourceKey,
    array $query = []
)

getResourceItem(
    string $productKey,
    string $resourceKey,
    string|int $id
)

createResourceItem(
    string $productKey,
    string $resourceKey,
    array $data
)

updateResourceItem(
    string $productKey,
    string $resourceKey,
    string|int $id,
    array $data
)

deleteResourceItem(
    string $productKey,
    string $resourceKey,
    string|int $id
)

restoreResourceItem(
    string $productKey,
    string $resourceKey,
    string|int $id
)

runResourceBulkAction(
    string $productKey,
    string $resourceKey,
    string $action,
    array $ids
)
```

Rules:

* Keep credentials server-side.
* Never log client secret.
* Never send credentials to browser-side JavaScript.
* Never put credentials in query string.
* Gracefully handle 401, 403, 404, 422, and 500 responses.
* Normalize validation errors so forms can display them.
* Return safe errors to controllers/views.
* Do not expose stack traces.

Every request to SaaS internal API must include:

```txt
X-Admin-Client-Id: {client_id}
X-Admin-Client-Secret: {client_secret}
X-Admin-Hub: maulanakurniawan.com
Accept: application/json
```

============================================================
RESOURCE INDEX PAGE
===================

Create:

```txt
/admin/{productKey}/resources
```

It should call:

```txt
GET /resources
```

Render cards/list of available resources.

Each resource card should show:

```txt
label
resource key
available operations
danger level if provided
description if provided
read-only status if applicable
```

Each card links to:

```txt
/admin/{productKey}/resources/{resourceKey}
```

Do not show raw JSON unless a local debug mode is explicitly enabled.

If no resources are returned, show a friendly empty state:

```txt
No admin resources are available for this product yet.
```

============================================================
GENERIC RESOURCE TABLE PAGE
===========================

Create:

```txt
/admin/{productKey}/resources/{resourceKey}
```

It should call:

```txt
GET /resources/{resourceKey}/schema
GET /resources/{resourceKey}
```

Render a proper table using the schema.

Table requirements:

* Columns come from `schema.list_columns`.
* Rows come from API `data.items`.
* Show pagination.
* Show search box if schema has searchable fields.
* Show filters if schema has filters/filterable fields.
* Show sortable column links if column is sortable.
* Show empty state.
* Show API error state safely.
* Do not render plain JSON.
* Do not expose API credentials.
* Keep query params when paginating/sorting/filtering.
* Add an Actions column.
* Add visible View/Edit/Delete/Restore buttons based on schema operations.

Each row should have action buttons depending on supported schema operations:

```txt
View
Edit
Delete
Restore
```

Only show actions that are supported.

============================================================
MANDATORY CRUD BUTTONS AND ACTION UI
====================================

The Admin Hub currently does not show clear Create, Edit, and Delete buttons.

Add visible CRUD buttons wherever the resource schema says the operation is supported.

Important:

Do not only create routes/controllers.
Do not only create hidden forms.
The UI must show actual clickable buttons/links for supported actions.

============================================================
CREATE BUTTON
=============

On the resource table page:

```txt
/admin/{productKey}/resources/{resourceKey}
```

If the schema operations include:

```txt
create
```

show a visible button near the top-right of the page:

```txt
+ Create {Resource Label}
```

Examples:

```txt
+ Create Endpoint
+ Create Project
+ Create Client
+ Create Time Entry
+ Create Note
```

The button should link to:

```txt
/admin/{productKey}/resources/{resourceKey}/create
```

If create is not supported, do not show the button.

If create is not supported but useful to explain, show small muted text:

```txt
Create is not available for this resource.
```

Do not show create buttons for read-only resources.

If no records exist but create is supported, show an empty state with a Create button:

```txt
No records found.
Create your first {Resource Label}
```

If no records exist and create is not supported, show:

```txt
No records found.
```

============================================================
ROW ACTION BUTTONS
==================

On every resource table row, add an Actions column.

The Actions column should contain buttons/links based on supported operations.

Always show View if view/detail is supported:

```txt
View
```

If update is supported, show:

```txt
Edit
```

If delete is supported, show:

```txt
Delete
```

If restore is supported and the item is deleted/archived, show:

```txt
Restore
```

Example table actions:

```txt
View | Edit | Delete
```

For dangerous actions, use a form with confirmation, not a plain link.

Do not show unsupported actions.

============================================================
DETAIL PAGE ACTION BUTTONS
==========================

On the detail page:

```txt
/admin/{productKey}/resources/{resourceKey}/{id}
```

Show action buttons at the top-right:

```txt
Back
Edit
Delete
Restore
```

Rules:

* Always show Back.
* Show Edit only if update is supported.
* Show Delete only if delete is supported.
* Show Restore only if restore is supported and the record is deleted/archived.
* Delete must use confirmation.
* Restore must use POST.
* Do not show unsupported actions.

============================================================
EDIT BUTTON AND EDIT FORM
=========================

The Edit button should link to:

```txt
/admin/{productKey}/resources/{resourceKey}/{id}/edit
```

Only show if schema operations include:

```txt
update
```

The edit form must have:

```txt
Save Changes
Cancel
```

Build the form from schema fields where:

```txt
editable = true
```

or from:

```txt
update_fields
```

On submit, call the Admin Hub route:

```txt
PATCH /admin/{productKey}/resources/{resourceKey}/{id}
```

The Admin Hub controller should call SaaS API:

```txt
PATCH /resources/{resourceKey}/{id}
```

If validation fails, show validation errors.

After successful update:

* Show success flash message.
* Redirect back to detail page if possible.
* Otherwise redirect to the table page.

============================================================
CREATE FORM
===========

Create:

```txt
/admin/{productKey}/resources/{resourceKey}/create
```

Only available if schema operations include:

```txt
create
```

Build the form from schema fields where:

```txt
creatable = true
```

or from:

```txt
create_fields
```

Support field types:

```txt
text
textarea
email
number
money
boolean
select
date
datetime
url
json
hidden
```

The create form must have:

```txt
Create
Cancel
```

On submit, call the Admin Hub route:

```txt
POST /admin/{productKey}/resources/{resourceKey}
```

The Admin Hub controller should call SaaS API:

```txt
POST /resources/{resourceKey}
```

If validation fails, show validation errors.

If success:

* Show success flash message.
* Redirect to detail page if API returns new item ID.
* Otherwise redirect to table page.

============================================================
DELETE BUTTON
=============

The Delete button must be a form, not a GET link.

Use method spoofing if needed:

```html
<form method="POST" action="...">
    @csrf
    @method('DELETE')
    <button type="submit">Delete</button>
</form>
```

Add browser confirmation:

```txt
Are you sure you want to delete this record? This action may not be reversible.
```

If the schema says the resource uses soft delete or archive, button text should be:

```txt
Archive
```

instead of:

```txt
Delete
```

If the schema danger level is high, use stronger warning copy.

On submit, call the Admin Hub route:

```txt
DELETE /admin/{productKey}/resources/{resourceKey}/{id}
```

The Admin Hub controller should call SaaS API:

```txt
DELETE /resources/{resourceKey}/{id}
```

After successful delete/archive:

* Show success flash message.
* Redirect to the resource table page.

============================================================
RESTORE BUTTON
==============

If restore is supported, show Restore button for deleted/archived records.

Restore should use POST:

```txt
POST /admin/{productKey}/resources/{resourceKey}/{id}/restore
```

The Admin Hub controller should call SaaS API:

```txt
POST /resources/{resourceKey}/{id}/restore
```

After successful restore:

* Show success flash message.
* Redirect to detail page or table page.

If the API does not return enough metadata to know whether a record is deleted/archived, do not show Restore unless clearly supported.

============================================================
BULK ACTION BUTTONS
===================

If the schema includes bulk actions, support:

```txt
POST /admin/{productKey}/resources/{resourceKey}/bulk-actions
```

UI requirements:

* Add checkbox per table row.
* Add select-all checkbox.
* Add bulk action dropdown above the table.
* Add Apply button.
* Show confirmation for destructive bulk actions.
* Submit selected IDs and selected action to the Admin Hub controller.
* The Admin Hub controller should call SaaS API:

```txt
POST /resources/{resourceKey}/bulk-actions
```

Do not show bulk action UI if the schema has no bulk actions.

Do not show fake or non-working bulk action buttons.

============================================================
ACTION VISIBILITY RULES
=======================

The UI must decide buttons based on schema, not hardcoded assumptions.

Use schema fields such as:

```txt
operations
bulk_actions
danger_level
soft_deletes
is_deleted
is_archived
deleted_at
archived_at
```

If the schema says the resource is read-only:

* Show the table.
* Show View buttons if supported.
* Do not show Create/Edit/Delete buttons.
* Add small muted label:

```txt
Read-only resource
```

If the API does not currently return enough metadata, handle gracefully.

Do not guess dangerous actions.

Do not show Delete/Edit/Create unless the schema clearly supports it.

============================================================
DETAIL PAGE
===========

Create:

```txt
/admin/{productKey}/resources/{resourceKey}/{id}
```

It should call:

```txt
GET /resources/{resourceKey}/schema
GET /resources/{resourceKey}/{id}
```

Render a clean detail view:

* Field label
* Field value
* Proper formatting for booleans, dates, money, email, URLs, long text, JSON objects
* No raw JSON unless field type is JSON and no better rendering exists
* Edit button only if update is supported
* Delete button only if delete is supported
* Restore button only if restore is supported
* Back button always visible

============================================================
PLAIN JSON REPLACEMENT
======================

Inspect current Admin Hub pages.

Where pages currently dump API responses as JSON, replace them with proper views.

Overview:

* Metric cards
* Health cards
* Recent activity table
* No raw JSON

Users:

* Table with search/filter/pagination
* Link to detail page
* Actions where available
* No raw JSON

Subscriptions:

* Table with search/filter/pagination
* Detail page
* Read-only by default if API schema says read-only
* No raw JSON

Analytics:

* Summary cards and simple tables
* Do not require charts unless already easy
* No raw JSON

Settings:

* Table or form of safe settings
* PATCH only supported fields
* No raw JSON

Audit Logs:

* Table with filters and pagination
* Read-only by default
* No raw JSON

Product Resources:

* Generic resource cards
* Generic table/form/detail pages
* No raw JSON

If a response structure is unknown, show a friendly unsupported/empty state instead of raw JSON.

============================================================
FIELD RENDERING
===============

Create reusable Blade components/helpers for fields.

Render types:

```txt
text
textarea
email
number
money
boolean
select
date
datetime
url
json
badge
status
```

Rules:

* Escape output by default.
* Dates should be readable.
* Booleans should show Yes/No or badges.
* Email fields can be mailto links.
* URLs can be clickable.
* Long text should be truncated in table and full in detail.
* JSON should be formatted safely in a collapsed/details block if necessary.
* Null values should show a muted dash.
* Hidden/protected fields should not be rendered.

============================================================
FORM FIELD RENDERING
====================

Create reusable form components/helpers where practical.

Support:

```txt
text input
textarea
email input
number input
money/decimal input
checkbox for boolean
select dropdown
date input
datetime input
URL input
JSON textarea
hidden input
```

Rules:

* Use old input values after validation error.
* Show field-level validation errors.
* Respect required/optional metadata from schema.
* Respect readonly/disabled metadata from schema.
* Do not render hidden/protected fields.
* Do not allow client-side form to submit fields not allowed by schema.
* Server-side controller must still filter submitted fields based on schema.

============================================================
SECURITY
========

Admin Hub UI must stay protected by admin auth.

Never expose:

```txt
client secrets
API keys
.env values
password hashes
hidden fields
raw exception traces
stack traces
database credentials
payment provider secrets
internal admin client secrets
```

Do not send API credentials to browser-side JavaScript.

All CRUD requests from UI should go through Laravel controllers.

Escape output by default.

Never trust schema blindly for unsafe HTML rendering.

============================================================
UX REQUIREMENTS
===============

Use simple Blade + Tailwind unless existing project uses another stack.

Add:

```txt
success flash messages
error flash messages
validation error display
empty states
pagination links
search input
filter form
sort links
confirm delete forms
breadcrumbs
back links
visible Create buttons
visible View/Edit/Delete/Restore row buttons
visible detail page action buttons
bulk action controls when supported
read-only labels
```

The product dropdown must remain visible on all Admin Hub pages.

Current section should stay the same when switching product where possible.

Example:

```txt
/admin/webhookwatch/resources/users
```

Switch product to SoloHours:

```txt
/admin/solohours/resources/users
```

If SoloHours does not have `users` resource, redirect to:

```txt
/admin/solohours/resources
```

============================================================
ERROR HANDLING
==============

Handle API errors safely.

For 401/403:

```txt
Admin Hub could not access this product. Please check API credentials and permissions.
```

For 404:

```txt
The requested resource or record was not found.
```

For 422:

Show validation errors next to fields.

For 500 or network errors:

```txt
The product API could not be reached or returned an error.
```

Do not show raw stack traces.

Do not dump full raw response unless debug mode is explicitly enabled.

============================================================
TESTS
=====

Add or update tests for:

* Resource index page renders resources as cards/list, not raw JSON.
* Resource table page renders schema columns.
* Resource table supports pagination query params.
* Search query is passed to SaaS API.
* Sort query is passed to SaaS API.
* Row Actions column is rendered.
* View button appears when detail/view is supported.
* Create button appears when schema operations include create.
* Create button does not appear when create is unsupported.
* Empty state shows Create button when create is supported.
* Edit button appears when update is supported.
* Edit button does not appear when update is unsupported.
* Delete button appears when delete is supported.
* Delete button does not appear when delete is unsupported.
* Delete action uses form submission, not GET link.
* Detail page renders fields.
* Detail page action buttons are rendered correctly.
* Create page only appears when create is supported.
* Edit page only appears when update is supported.
* Read-only resources do not show create/edit/delete buttons.
* Bulk action controls appear only when bulk actions exist.
* Validation errors from SaaS API are displayed.
* Unknown resource returns safe 404.
* API failure shows safe error state.
* Client secrets are not rendered in HTML.
* Hidden/protected fields are not rendered.
* Unauthenticated users cannot access CRUD pages.

Use existing test style.

============================================================
DOCUMENTATION
=============

Update:

```txt
docs/admin-hub.md
```

Document:

```txt
Generic resource UI
How resource schemas work
How to add a new SaaS resource
How CRUD buttons are displayed
How CRUD actions flow through Admin Hub
How Create/Edit/Delete/Restore forms work
How bulk actions work
Security notes
Why resources are allowlisted
Troubleshooting API errors
```

============================================================
FINAL RESPONSE REQUIRED FROM CODEX
==================================

After implementation, provide:

1. Files changed.
2. Routes added.
3. Views/components added.
4. SaasAdminClient methods added.
5. Plain JSON pages replaced.
6. CRUD buttons added.
7. CRUD UI features added.
8. Tests added.
9. Manual verification steps.
10. Limitations or TODOs.
