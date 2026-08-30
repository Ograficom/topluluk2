<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostPresentationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $post = $this->resolveRoutePost($request);
        $shouldNoindex = $post && ((bool) $post->noindex || (bool) $post->followers_only);

        if ($shouldNoindex) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type'));
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html) || $html === '') {
            return $response;
        }

        if ($shouldNoindex && ! preg_match('/<meta\s+name=["\']robots["\']/i', $html)) {
            $html = preg_replace(
                '/<\/head>/i',
                "    <meta name=\"robots\" content=\"noindex,nofollow,noarchive\">\n</head>",
                $html,
                1
            ) ?? $html;
        }

        if ($request->routeIs('blog.create', 'blog.post.edit')) {
            $preferencesScript = $this->preferencesScript($post);
            $html = preg_replace('/<\/body>/i', $preferencesScript . "\n</body>", $html, 1) ?? ($html . $preferencesScript);
        }

        $aiIds = [];
        if (preg_match_all('/data-post-id=["\'](\d+)["\']/i', $html, $matches)) {
            $ids = array_values(array_unique(array_map('intval', $matches[1] ?? [])));
            if ($ids !== []) {
                $aiIds = Post::withoutGlobalScopes()
                    ->whereIn('id', $ids)
                    ->where('is_ai_product', true)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();
            }
        }

        $currentAi = (bool) ($post?->is_ai_product ?? false);
        if ($aiIds !== [] || $currentAi) {
            $script = $this->badgeScript($aiIds, $currentAi);
            $html = preg_replace('/<\/body>/i', $script . "\n</body>", $html, 1) ?? ($html . $script);
        }

        $response->setContent($html);

        return $response;
    }

    private function resolveRoutePost(Request $request): ?Post
    {
        $routePost = $request->route('post');
        if ($routePost instanceof Post) {
            return $routePost;
        }

        $slug = trim((string) $routePost);
        if ($slug === '') {
            return null;
        }

        return Post::withoutGlobalScopes()->where('slug', $slug)->first();
    }

    private function preferencesScript(?Post $post): string
    {
        $states = json_encode([
            'followers_only' => (bool) ($post?->followers_only ?? false),
            'noindex' => (bool) ($post?->noindex ?? false),
            'is_ai_product' => (bool) ($post?->is_ai_product ?? false),
            'hide_from_feeds' => (bool) ($post?->hide_from_feeds ?? false),
            'suppress_follower_notifications' => (bool) ($post?->suppress_follower_notifications ?? false),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';

        return <<<HTML
<script data-ografi-post-distribution-settings>
(() => {
    const states = {$states};
    const options = [
        ['followers_only', 'Sadece takipçiler'],
        ['noindex', 'Arama motorlarından sakla'],
        ['is_ai_product', 'Bu bir yapay zeka ürünü'],
        ['hide_from_feeds', 'Akıştan uzak tut'],
        ['suppress_follower_notifications', 'Takipçilere bildirme'],
    ];

    const switchMarkup = (name, checked) => `
        <label class="group relative inline-flex cursor-pointer items-center">
            <input type="hidden" name="\${name}" value="0">
            <input type="checkbox" name="\${name}" id="\${name}" value="1" role="switch" class="peer sr-only" \${checked ? 'checked' : ''}>
            <span class="relative h-7 w-12 rounded-full border border-slate-300 bg-slate-200 transition-all duration-200 group-hover:bg-white peer-focus-visible:ring-4 peer-focus-visible:ring-blue-500/15 peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:group-hover:bg-blue-600" aria-hidden="true"></span>
            <span class="pointer-events-none absolute left-[3px] top-[3px] h-5 w-5 rounded-full bg-white shadow-[0_2px_8px_rgba(15,23,42,0.18)] transition-all duration-200 peer-checked:translate-x-5" aria-hidden="true"></span>
        </label>`;

    const mount = () => {
        const titles = Array.from(document.querySelectorAll('#settings-modal .settings-accordion-title'));
        const title = titles.find((node) => String(node.textContent || '').trim() === 'Tercihler');
        const details = title?.closest('details.settings-accordion');
        const content = details?.querySelector('.settings-accordion-content');
        const list = content?.querySelector('.divide-y');
        if (!list || list.dataset.distributionReady === '1') return Boolean(list);

        options.forEach(([name, label]) => {
            if (list.querySelector(`[name="\${name}"]`)) return;
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between gap-4 px-3 py-3';
            row.innerHTML = `<span class="text-sm text-slate-800">\${label}</span>\${switchMarkup(name, Boolean(states[name]))}`;
            list.appendChild(row);
        });

        list.dataset.distributionReady = '1';
        return true;
    };

    const boot = () => {
        if (mount()) return;
        let attempts = 0;
        const timer = window.setInterval(() => {
            attempts += 1;
            if (mount() || attempts > 40) window.clearInterval(timer);
        }, 100);
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
    else boot();
})();
</script>
HTML;
    }

    private function badgeScript(array $aiIds, bool $currentAi): string
    {
        $ids = json_encode(array_values($aiIds), JSON_UNESCAPED_SLASHES) ?: '[]';
        $current = $currentAi ? 'true' : 'false';

        return <<<HTML
<script data-ografi-ai-product-badges>
(() => {
    const ids = {$ids};
    const currentAi = {$current};
    const makeBadge = () => {
        const badge = document.createElement('span');
        badge.setAttribute('data-ai-product-badge', '');
        badge.textContent = 'Yapay zeka ürünü';
        badge.style.cssText = 'display:inline-flex;align-items:center;width:max-content;margin:0 0 8px;padding:4px 8px;border:1px solid #dbe2ea;border-radius:999px;background:#f8fafc;color:#475569;font-size:11px;line-height:1.2;font-weight:600;';
        return badge;
    };
    const place = (root) => {
        if (!root || root.querySelector('[data-ai-product-badge]')) return;
        const target = root.querySelector('.alma-post-card__title,.post-card__title,.blog-post-card__title,.entry-title,h1,h2,h3');
        const badge = makeBadge();
        if (target?.parentElement) target.parentElement.insertBefore(badge, target);
        else root.prepend(badge);
    };
    const run = () => {
        ids.forEach((id) => document.querySelectorAll(`[data-post-id="\${id}"]`).forEach(place));
        if (currentAi) {
            const root = document.querySelector('article') || document.querySelector('main') || document.body;
            place(root);
        }
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run, { once: true });
    else run();
})();
</script>
HTML;
    }
}
