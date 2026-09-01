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
            <span class="ografi-comment-skeleton__avatar"></span>
            <span class="ografi-comment-skeleton__meta">
                <span class="ografi-comment-skeleton__line ografi-comment-skeleton__line--long"></span>
                <span class="ografi-comment-skeleton__line ografi-comment-skeleton__line--short"></span>
            </span>
        </div>
        <span class="ografi-comment-skeleton__body"></span>
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

html body .ogx-comments-panel > .ografi-comment-skeleton {
    position: absolute !important;
    inset: 0 !important;
    z-index: 2147482000 !important;
    display: block !important;
    box-sizing: border-box !important;
    width: 100% !important;
    min-height: 230px !important;
    margin: 0 !important;
    padding: 14px !important;
    overflow: hidden !important;
    border-radius: inherit !important;
    background: #ffffff !important;
    pointer-events: none !important;
}

html body .ogx-comments-panel > .ografi-comment-skeleton[hidden] {
    display: none !important;
}

html body .ografi-comment-skeleton__card {
    display: block !important;
    width: 100% !important;
    max-width: none !important;
    box-sizing: border-box !important;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
}

html body .ografi-comment-skeleton__head {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    width: 100% !important;
    min-width: 0 !important;
}

html body .ografi-comment-skeleton__avatar {
    display: block !important;
    width: 44px !important;
    min-width: 44px !important;
    height: 44px !important;
    border-radius: 9999px !important;
    background: #eef1f5 !important;
}

html body .ografi-comment-skeleton__meta {
    display: flex !important;
    flex: 1 1 auto !important;
    min-width: 0 !important;
    flex-direction: column !important;
    gap: 9px !important;
}

html body .ografi-comment-skeleton__line {
    display: block !important;
    height: 10px !important;
    border-radius: 9999px !important;
    background: #eef1f5 !important;
}

html body .ografi-comment-skeleton__line--long {
    width: min(68%, 410px) !important;
}

html body .ografi-comment-skeleton__line--short {
    width: min(40%, 240px) !important;
}

html body .ografi-comment-skeleton__body {
    display: block !important;
    width: 100% !important;
    height: 130px !important;
    margin-top: 14px !important;
    border-radius: 14px !important;
    background: #eef1f5 !important;
}

html.dark body .ogx-comments-panel > .ografi-comment-skeleton,
body.dark .ogx-comments-panel > .ografi-comment-skeleton,
[data-theme="dark"] body .ogx-comments-panel > .ografi-comment-skeleton {
    background: #111318 !important;
}

html.dark body .ografi-comment-skeleton__avatar,
html.dark body .ografi-comment-skeleton__line,
html.dark body .ografi-comment-skeleton__body,
body.dark .ografi-comment-skeleton__avatar,
body.dark .ografi-comment-skeleton__line,
body.dark .ografi-comment-skeleton__body,
[data-theme="dark"] body .ografi-comment-skeleton__avatar,
[data-theme="dark"] body .ografi-comment-skeleton__line,
[data-theme="dark"] body .ografi-comment-skeleton__body {
    background: #2a2f38 !important;
}

@media (max-width: 700px) {
    html body .ogx-comments-panel > .ografi-comment-skeleton {
        padding: 12px !important;
        min-height: 218px !important;
    }

    html body .ografi-comment-skeleton__avatar {
        width: 42px !important;
        min-width: 42px !important;
        height: 42px !important;
    }

    html body .ografi-comment-skeleton__line--long {
        width: 72% !important;
    }

    html body .ografi-comment-skeleton__line--short {
        width: 42% !important;
    }

    html body .ografi-comment-skeleton__body {
        height: 130px !important;
    }
}
</style>
<script data-ografi-comment-skeleton-script>
(() => {
    const skeletons = Array.from(document.querySelectorAll('[data-ografi-comment-skeleton]'));
    if (!skeletons.length) return;

    skeletons.forEach((skeleton) => {
        skeleton.closest('.ogx-comments-panel')?.setAttribute('aria-busy', 'true');
    });

    let finished = false;
    const finish = () => {
        if (finished) return;
        finished = true;

        window.requestAnimationFrame(() => {
            skeletons.forEach((skeleton) => {
                skeleton.hidden = true;
                skeleton.closest('.ogx-comments-panel')?.setAttribute('aria-busy', 'false');
            });
        });
    };

    if (document.readyState === 'complete') {
        finish();
    } else {
        window.addEventListener('load', finish, { once: true });
        window.setTimeout(finish, 5000);
    }
})();
</script>
HTML;

        $html = preg_replace('/<\/body>/i', $assets . "\n</body>", $html, 1) ?? ($html . $assets);
        $response->setContent($html);
        $response->headers->set('X-Ografi-Comment-Skeleton', 'v1');

        return $response;
    }
}
