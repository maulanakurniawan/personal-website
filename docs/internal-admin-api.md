# Internal Admin API

The local API for maulanakurniawan.com is mounted at `/api/internal/admin/v1` and is controlled by `INTERNAL_ADMIN_API_ENABLED`.

## Auth headers

Every request must include:

```txt
X-Admin-Client-Id: {client_id}
X-Admin-Client-Secret: {client_secret}
X-Admin-Hub: maulanakurniawan.com
Accept: application/json
```

Secrets are stored hashed in `internal_admin_clients` and shown once during creation or rotation.

## Client commands

```bash
php artisan internal-admin-client:create "Admin Hub Local" --scopes=read,write
php artisan internal-admin-client:list
php artisan internal-admin-client:rotate {client_id}
php artisan internal-admin-client:revoke {client_id}
php artisan internal-admin-client:enable {client_id}
php artisan internal-admin-client:disable {client_id}
```

`list` never displays secrets.

## Endpoints

- `GET /health`
- `GET /overview`
- `GET /users`
- `GET /users/{id}`
- `POST /users/{id}/actions`
- `GET /subscriptions`
- `GET /subscriptions/{id}`
- `GET /analytics`
- `GET /settings`
- `PATCH /settings`
- `GET /audit-logs`
- `GET /product-resources`

Responses use `{ success, product, data, meta }` for success and `{ success, product, error }` for errors. Write endpoints require the `write` scope and create audit log records.
