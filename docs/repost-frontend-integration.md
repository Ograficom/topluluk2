# OgraFi — Repost v3 public interface integration

## Current implementation

The supplied `Repost(4).zip` is the visual source of truth for the public interface. It contains 21 static reference screens and a shared responsive design system. The static HTML is not copied into Laravel as-is: it contains demonstration accounts/content and standalone links that would bypass OgraFi's route names, authentication, CSRF protection, authorization, Livewire components and real database records.

The current integration replaces the previous competing partial Repost stylesheet layers with one public stylesheet:

- `public/css/repost-full.css` is the single Repost visual layer.
- `resources/views/layouts/app.blade.php` applies the shared Repost shell to authenticated and public application pages.
- `resources/views/layouts/guest.blade.php` applies the same light theme to sign-in, registration and password flows.
- `resources/views/header.blade.php` maps the existing functional header into the Repost header rhythm.
- The shared shell uses the Repost 70px header, 55px left rail, fluid center column and 320px right widgets column, with tablet/mobile collapse.
- Old partial layers `public/css/repost-home.css`, `public/css/repost-layout.css`, `public/css/repost-theme.css` and `resources/css/repost-frontend.css` have been removed.

## Preserved application behavior

This is a presentation-layer change. It does not replace routes or controllers and does not introduce migrations. Existing post data, categories, tags, comments, reactions, bookmarks, search, notifications, messages, profiles, authentication, validation, pagination, SEO metadata and Livewire interactions remain connected to their current Laravel implementations.

Filament remains outside the public Repost layout and keeps its own panel assets and theme.

## Remaining page-by-page conversion

The shared Repost shell and visual rules are applied across the public layouts. The legacy left sidebar has been replaced with database-backed navigation, including a responsive full-width mobile drawer, category icons/labels, contacts, message settings, bookmarks and account settings. Page wrappers for feed, post detail, public profile, messages, category/tag catalogs, favorites, users, search and static content are connected to Repost component styles. The latest pass also adds explicit route classes for messages, bookmarks and category index, and restyles message lists, conversation bubbles, composer, bookmark panels and category layout using the Repost palette. Detailed page-by-page markup conversion is still required for:

1. Feed and post cards.
2. Post detail, media, comments and reaction controls.
3. Category/channel index and detail pages.
4. Tag catalog and tag detail pages.
5. Public profile, subscriptions and author ranking.
6. Messages, contacts and conversation detail.
7. Account settings, sign-in, registration and password recovery.
8. Static content, statistics, latest posts and latest comments.

For each conversion, use live OgraFi records and existing named routes; never ship the Repost demo names, posts, images or placeholder links.

## Validation

The Laravel focused test suite and MariaDB migration/rollback checks passed on the current integration commits. Cloudflare's separate Workers build check has reported a failure and must be investigated before production release. No browser-based visual acceptance test has been completed yet.

The pull-request workflow now runs for changes under `resources/**`, `public/**`, `routes/**` and `tests/**`, in addition to the existing dependency/build inputs. Required checks are PHP syntax, Blade compilation, application tests and the Vite production build. Production deployment remains gated by merging the pull request; this branch update alone does not change the live site.


## Repost(5).zip screen-to-route map

| Repost screen | OgraFi live endpoint/view | Integration rule |
|---|---|---|
| `index.html` | `home` / `templates.home-like` | Use the real feed and existing post-card component |
| `post.html` | `blog.post` / `blog.show` | Keep comments, reactions, bookmarks, poll and view endpoints |
| `profile.html` | `users.show` / `profile.public` | Use the selected user's posts, follow state, badges and tabs |
| `channels.html` | `blog.categories` | Use database categories, not the demo channel list |
| `channel.html` | `blog.category` / `blog.index` | Use the category-bound feed and join actions |
| `tagscloud.html` | `blog.tags` | Use database tags and existing search links |
| `tags-catalog.html` | `blog.index?tag=...` | Use the existing tag-filtered post query |
| `favorites.html` | `blog.bookmarks` | Authenticated user's saved posts |
| `messenger.html` | `messages.index` | Existing conversation list and unread state |
| `dialog.html` | `messages.show` | Existing conversation, message send and attachment actions |
| `contacts.html` | `messages.contacts` | Existing contacts data |
| `signin.html` | `login` | Existing Laravel authentication form and CSRF |
| `signup.html` | `register` | Existing email verification registration flow |
| `lostpassword.html` | `password.request` | Existing password reset flow |
| `subscriptions.html` | authenticated profile/following data | Do not create demo subscriptions; use current follow relationships |
| `rating-users.html` | `users.index` | Existing user listing and ranking data where available |
| `last-posts.html` | `blog.index` | Existing latest posts query |
| `last-comments.html` | existing post/comment feed sections | No dedicated named route confirmed; do not add a fake endpoint |
| `page.html` | `pages.show` | Existing published page content |
| `stats.html` | dashboard/statistics views | Keep authenticated access and only expose metrics already provided by the app |

The archive's sample names, post copy, demo avatars and placeholder `#` links are design references only and must not be rendered as production data. The existing Blade views remain responsible for authorization, validation, CSRF, pagination, SEO and real records.


## Current asset status

`public/css/repost-full.css` and `public/js/repost-ui.js` are the current OgraFi integration layer. The complete original `Repost/dist/assets/` payload (the archive's app/kit/editor/engine CSS, bundled JS, fonts, icon sprite, and optional libraries) has not yet been copied into `public/repost/`. The current `repost-full.css` is an OgraFi-specific compatibility layer that reproduces the shared layout and visual rules; it is not the archive's original compiled stylesheet. Do not treat the source asset import as complete until the assets are added and referenced by the Blade layout. Keep optional libraries page-scoped; do not load every archive plugin globally.

## Original Repost asset status

The original Repost CSS bundle from the supplied archive is now stored in `public/repost-bundle/css/` as compressed, split source parts. `public/js/repost-assets-loader.js` joins and decompresses the parts in the browser, then inserts the original styles before the OgraFi compatibility stylesheet. Inter, Roboto and Special Gothic fonts are loaded for the theme.

The original Repost JavaScript bundle and remaining local font/image/plugin assets are not yet fully imported. OgraFi's existing `repost-ui.js` and Laravel-owned interactions remain in use. Do not treat the asset import as complete until the original app/init scripts, icon sprite and any required plugin files are transferred and checked.
