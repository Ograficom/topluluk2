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
