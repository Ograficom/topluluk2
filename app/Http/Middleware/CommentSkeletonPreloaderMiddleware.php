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
    width: 100% !important;
    min-height: 228px !important;
    margin: 0 !important;
    padding: 14px 16px !important;
    box-sizing: border-box !important;
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
    height: 100% !important;
    min-height: 198px !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
    border: 0 !important;
    background: #ffffff !important;
}

html body .ografi-comment-skeleton__head {
    display: flex !important;
    align-items: center !important;
    width: 100% !important;
    min-width: 0 !important;
    gap: 12px !important;
}

html body .ografi-comment-skeleton__avatar {
    display: block !important;
    flex: 0 0 44px !important;
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
    justify-content: center !important;
    gap: 9px !important;
}

html body .ografi-comment-skeleton__line {
    display: block !important;
    height: 10px !important;
    border-radius: 9999px !important;
    background: #eef1f5 !important;
}

html body .ografi-comment-skeleton__line--long {
    width: min(72%, 410px) !important;
}

html body .ografi-comment-skeleton__line--short {
    width: min(42%, 238px) !important;
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
[data-theme="dark"] body .ogx-comments-panel > .ografi-comment-skeleton,
html.dark body .ografi-comment-skeleton__card,
body.dark .ografi-comment-skeleton__card,
[data-theme="dark"] body .ografi-comment-skeleton__card {
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
        min-height: 218px !important;
        padding: 12px !important;
    }

    html body .ografi-comment-skeleton__card {
        min-height: 194px !important;
    }

    html body .ografi-comment-skeleton__avatar {
        flex-basis: 42px !important;
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
        margin-top: 14px !important;
        border-radius: 14px !important;
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
        $response->headers->set('X-Ografi-Comment-Skeleton', 'v2');

        return $response;
    }
}
