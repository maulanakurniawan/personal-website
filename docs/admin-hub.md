# Admin Hub

The Admin Hub is the private central UI at `/admin` for managing WebhookWatch, SoloHours, MaulanaKurniawan, and future SaaS products through secure product APIs.

## Required environment variables

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

## Admin users

Create a user:

```bash
php artisan admin-user:create "Maulana Kurniawan" "your-email@example.com"
```

Reset a password:

```bash
php artisan admin-user:reset-password "your-email@example.com"
```

Other commands: `admin-user:list`, `admin-user:disable`, `admin-user:enable`, and `admin-user:delete`.

## Adding products

Add each product in `config/admin-hub.php` and provide its base URL, client ID, and client secret through environment variables. Credentials stay server-side and are sent only as headers by the Laravel HTTP client.

## Security model

`/admin` is session-authenticated with the dedicated `admin` guard. There is no public registration. Login is rate limited. Product API secrets must never be exposed in HTML, JavaScript, logs, or error pages.

## Deployment checklist

1. Deploy SaaS internal admin APIs first.
2. On each SaaS, run `php artisan internal-admin-client:create "Admin Hub" --scopes=read,write`.
3. Copy each client ID and secret once.
4. Add credentials to maulanakurniawan.com `.env`.
5. On maulanakurniawan.com, run migrations.
6. Create central admin user: `php artisan admin-user:create "Maulana Kurniawan" "your-email@example.com"`.
7. Create local internal admin client: `php artisan internal-admin-client:create "Admin Hub Local" --scopes=read,write`.
8. Add local credentials to `.env`.
9. Set `ADMIN_HUB_ENABLED=true` and `INTERNAL_ADMIN_API_ENABLED=true`.
10. Visit `/admin`.
11. Confirm product dropdown works.
12. Confirm API credentials are not exposed in logs, HTML, JavaScript, or errors.
