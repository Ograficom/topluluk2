# Repost Blade UI integration

This branch moves the Repost visual language into OgraFi's real Laravel Blade UI without copying the static demo pages.

## What changed
- Added a public-only Repost component stylesheet, loaded by the public app and guest layouts.
- Added reusable Blade surface and page-heading components.
- Applied Repost component hooks to the live feed cards, post detail, profile, messaging, notifications, and authentication surfaces.
- Kept controllers, route names, authorization, forms, CSRF, Livewire hooks, pagination, and database schema intact.
- Kept Filament's panel provider, panel theme, and assets untouched.

## Validation
Run `composer validate`, `php artisan test`, and `npm run build`. Verify the public routes and Filament admin separately at desktop and mobile widths. No production migration is required.
