# Repost frontend integration

## Scope

This branch introduces the first integration layer for the supplied Repost HTML theme into OgraFi's existing public Blade frontend.

- Adds `resources/css/repost-frontend.css`, scoped to the existing `.alma-app` public application shell.
- Loads the new layer through the existing Vite entrypoint in `resources/css/app.css`.
- Refreshes the public header surface, three-column desktop shell, feed tabs, post cards, form controls, profile surfaces, and mobile layout using Repost's light palette, blue accent, compact radii, and restrained borders.
- Preserves the existing Laravel routes, Blade data contracts, Livewire interactions, post/reaction/comment behavior, and Filament panel assets.

## Deliberate compatibility choices

The supplied archive is a static HTML theme with demo content and DLE-oriented engine styles/scripts. Its HTML pages are not drop-in Laravel views. Copying those pages verbatim would replace real OgraFi route data with demo content and could break authentication, CSRF, route names, post actions, and Livewire behavior.

For that reason, this first layer styles the existing OgraFi components instead of replacing them with static HTML. Filament uses its own panel layout and Vite theme entry, so this public frontend stylesheet is not added to the Filament panel theme.

## Next conversion pass

Convert the public Blade views one by one to Repost component markup while retaining each controller's existing variables and actions:

1. Home/feed and post card.
2. Post detail and comments.
3. Categories/channels and tags.
4. Public profile and user directory.
5. Messages and notifications.
6. Sign-in, registration, and account settings.

For each view, keep existing named routes, authorization checks, validation, pagination, SEO metadata, and responsive behavior. Do not migrate demo text or placeholder accounts.

## Validation required before production

- Run `composer validate` and the project PHPUnit suite.
- Run `npm run build`.
- Verify home, post detail, profile, categories, messages, auth, and account pages at desktop/tablet/mobile widths.
- Verify the Filament panel and its assets remain unchanged.
- Apply no production migration; this frontend pass introduces none.
