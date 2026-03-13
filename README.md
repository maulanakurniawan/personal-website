# SaaS Operations Starter

This `codebase/` directory is a generic Laravel SaaS starter you can adapt to many products.

## Included basic features
- Email/password authentication with email verification
- Account profile management and account deletion
- Team-ready dashboard for tracked resources
- Status checks and incident timelines
- Configurable alert channels (email)
- Subscription billing flow (checkout, plan changes, cancellation, history)
- Admin views for users, subscriptions, and transactions
- SEO-ready public pages (home, pricing, guides, contact, legal)

## Requirements
- PHP 8.2+
- Composer
- Node.js + npm
- MySQL or PostgreSQL

## Local setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

## Run in development
```bash
php artisan serve
php artisan queue:work
php artisan schedule:work
```

## Run tests
```bash
php artisan test
```

## Notes
- Plan pricing is configured from your database plan records.
- Alert delivery uses Laravel mail configuration.
- Replace the public marketing copy, guides, and branding with your own domain language.
