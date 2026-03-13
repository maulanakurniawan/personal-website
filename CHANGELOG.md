# Refactor Changelog

## Removed
- SoloHours SaaS surface area: authentication flows, dashboard, billing/subscription/Paddle webhooks, time tracking modules (clients/projects/time entries/reports), admin area, and related views/controllers/middleware.
- SoloHours-specific models, notifications, commands, service classes, and migrations tied to product billing/tracking data.
- Unused SaaS tests and views that referenced removed routes/features.
- SoloHours-only config files (`config/paddle.php`, `config/plans.php`) and related `.env` keys.

## Kept
- Public website infrastructure: shared public layout, SEO metadata, canonical tags, robots, sitemap endpoint.
- Contact page and working contact form handling (with Turnstile verification + mail send).
- Legal pages (Privacy and Terms), rewritten for a personal/content website context.
- Existing article system (article listing + article detail routes + sitemap inclusion).

## Added
- New public page: `/manawan` with initiative positioning and CTA to contact.
- New `/about` page for profile content.
- Refactored homepage content for Maulana Kurniawan personal positioning.
- Updated navigation to only relevant public links (Home, Manawan, Articles, Contact).
- Cleanup summary docs: `ROUTE_AUDIT.md` and `BRANDING_NOTES.md`.

## Manual follow-up
- Run `composer install` in a network-enabled environment and regenerate `composer.lock` to align with updated `composer.json`.
- If this environment has existing SoloHours DB state, plan a migration/reset strategy before deploying to production.
- Update `APP_URL`, mail settings, and Turnstile keys in production `.env`.
