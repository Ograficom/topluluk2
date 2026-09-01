<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentSkeletonPreloaderMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $contentType = strtolower((string) $response->headers->get('Content-Type'));
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html)
            || $html === ''
            || ! str_contains($html, 'ogx-comments-panel')
            || str_contains($html, 'data-ografi-comment-skeleton')
        ) {
            return $response;
        }

        $skeleton = <<<'HTML'
<div class="ografi-comment-skeleton" data-ografi-comment-skeleton aria-hidden="true">
    <div class="ografi-comment-skeleton__card">
        <div class="ografi-comment-skeleton__head">
            <span class="ografi-comment-skeleton__shape ografi-comment-skeleton__avatar"></span>

            <span class="ografi-comment-skeleton__meta-wrap">
                <span class="ografi-comment-skeleton__shape ografi-comment-skeleton__name"></span>
                <span class="ografi-comment-skeleton__shape ografi-comment-skeleton__meta"></span>
            </span>
        </div>

        <span class="ografi-comment-skeleton__shape ografi-comment-skeleton__body"></span>
    </div>
</div>
HTML;

        $html = preg_replace(
            '/(<section\b[^>]*\bclass="[^"]*\bogx-comments-panel\b[^"]*"[^>]*>)/i',
            '$1' . $skeleton,
            $html,
            1
        ) ?? $html;

        $assets = <<<'HTML'
<style data-ografi-comment-skeleton-style>
html body .ogx-comments-panel {
    position: relative !important;
}

html body .ogx-comments-panel .ogx-empty {
    display: none !important;
    margin: 0 !important;
    padding: 0 !important;
    min-height: 0 !important;
    height: 0 !important;
    border: 0 !important;
}

html body .ogx-comments-panel > .ografi-comment-skeleton {
    position: absolute !important;
    top: 0 !important;
    right: 0 !important;
    bottom: auto !important;
    left: 0 !important;
    z-index: 2147482000 !important;
    display: block !important;
    width: 100% !important;
    height: auto !important;
    min-height: 0 !important;
    margin: 0 !important;
    padding: 20px !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
    border-radius: inherit !important;
    background: #ffffff !important;
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: none !important;
}

html body .ogx-comments-panel > .ografi-comment-skeleton[hidden] {
    display: none !important;
}

html body .ografi-comment-skeleton__card {
    display: block !important;
    width: 100% !important;
    height: auto !important;
    min-height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
    border: 0 !important;
    background: #ffffff !important;
}

html body .ografi-comment-skeleton__head {
    display: grid !important;
    grid-template-columns: 42px minmax(0, 1fr) !important;
    gap: 10px !important;
    align-items: center !important;
    width: 100% !important;
    min-width: 0 !important;
    margin-bottom: 18px !important;
}

html body .ografi-comment-skeleton__shape {
    position: relative !important;
    overflow: hidden !important;
    background: #eef1f5 !important;
    background-image: none !important;
    animation: none !important;
    transition: none !important;
}

html body .ografi-comment-skeleton__shape::before,
html body .ografi-comment-skeleton__shape::after {
    content: none !important;
    display: none !important;
    background: none !important;
    animation: none !important;
}

html body .ografi-comment-skeleton__avatar {
    display: block !important;
    width: 42px !important;
    min-width: 42px !important;
    height: 42px !important;
    border-radius: 9999px !important;
}

html body .ografi-comment-skeleton__meta-wrap {
    display: block !important;
    min-width: 0 !important;
}

html body .ografi-comment-skeleton__name {
    display: block !important;
    width: 128px !important;
    max-width: 72% !important;
    height: 15px !important;
    margin: 0 0 8px !important;
    border-radius: 9999px !important;
}

html body .ografi-comment-skeleton__meta {
    display: block !important;
    width: 92px !important;
    max-width: 52% !important;
    height: 12px !important;
    margin: 0 !important;
    border-radius: 9999px !important;
}

html body .ografi-comment-skeleton__body {
    display: block !important;
    width: 100% !important;
    height: 130px !important;
    margin: 0 !important;
    border-radius: 8px !important;
}

html.dark body .ogx-comments-panel > .ografi-comment-skeleton,
body.dark .ogx-comments-panel > .ografi-comment-skeleton,
[data-theme="dark"] body .ogx-comments-panel > .ografi-comment-skeleton,
html.dark body .ografi-comment-skeleton__card,
body.dark .ografi-comment-skeleton__card,
[data-theme="dark"] body .ografi-comment-skeleton__card {
    background: #18181b !important;
}

html.dark body .ografi-comment-skeleton__shape,
body.dark .ografi-comment-skeleton__shape,
[data-theme="dark"] body .ografi-comment-skeleton__shape {
    background: #27272a !important;
    background-image: none !important;
}

@media (max-width: 700px) {
    html body .ogx-comments-panel > .ografi-comment-skeleton {
        height: auto !important;
        min-height: 0 !important;
        padding: 16px !important;
    }

    html body .ografi-comment-skeleton__card {
        height: auto !important;
        min-height: 0 !important;
    }

    html body .ografi-comment-skeleton__head {
        grid-template-columns: 42px minmax(0, 1fr) !important;
        gap: 10px !important;
        margin-bottom: 16px !important;
    }

    html body .ografi-comment-skeleton__body {
        height: 128px !important;
    }
}
</style>
<script data-ografi-comment-skeleton-script>
(() => {
    const skeletons = Array.from(document.querySelectorAll('[data-ografi-comment-skeleton]'));
    if (!skeletons.length) return;

    const startedAt = Date.now();
    const minimumVisibleMs = 520;
    const maximumVisibleMs = 2400;
    let finished = false;

    skeletons.forEach((skeleton) => {
        skeleton.hidden = false;
        skeleton.dataset.booted = '1';
        skeleton.closest('.ogx-comments-panel')?.setAttribute('aria-busy', 'true');
    });

    const hideAll = () => {
        if (finished) return;
        finished = true;

        skeletons.forEach((skeleton) => {
            skeleton.hidden = true;
            skeleton.closest('.ogx-comments-panel')?.setAttribute('aria-busy', 'false');
        });
    };

    const finishAll = () => {
        if (finished) return;

        const elapsed = Date.now() - startedAt;
        const delay = Math.max(0, minimumVisibleMs - elapsed);
        window.setTimeout(hideAll, delay);
    };

    if (document.readyState === 'complete') {
        finishAll();
    } else {
        window.addEventListener('load', finishAll, { once: true });
    }

    window.setTimeout(hideAll, maximumVisibleMs);
})();
</script>
HTML;

        $html = preg_replace('/<\/body>/i', $assets . "\n</body>", $html, 1) ?? ($html . $assets);
        $response->setContent($html);
        $response->headers->set('X-Ografi-Comment-Skeleton', 'v8');

        return $response;
    }
}
