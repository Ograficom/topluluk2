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

            <span class="ografi-comment-skeleton__meta">
                <span class="ografi-comment-skeleton__shape ografi-comment-skeleton__line ografi-comment-skeleton__line--long"></span>
                <span class="ografi-comment-skeleton__shape ografi-comment-skeleton__line ografi-comment-skeleton__line--short"></span>
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
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: none !important;
}

html body .ogx-comments-panel > .ografi-comment-skeleton.is-hiding {
    animation: ografiCommentPreloaderOut 220ms ease forwards !important;
}

html body .ogx-comments-panel > .ografi-comment-skeleton[hidden] {
    display: none !important;
}

html body .ografi-comment-skeleton__card {
    display: block !important;
    width: 100% !important;
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

html body .ografi-comment-skeleton__shape {
    position: relative !important;
    overflow: hidden !important;
    background: linear-gradient(105deg, #eef2fb 0%, #ffffff 45%, #eef2fb 82%) !important;
    background-size: 200% 100% !important;
    animation: ografiImgWave 1.15s ease-in-out infinite !important;
}

html body .ografi-comment-skeleton__avatar {
    display: block !important;
    flex: 0 0 44px !important;
    width: 44px !important;
    min-width: 44px !important;
    height: 44px !important;
    border-radius: 9999px !important;
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
}

html.dark body .ogx-comments-panel > .ografi-comment-skeleton,
body.dark .ogx-comments-panel > .ografi-comment-skeleton,
[data-theme="dark"] body .ogx-comments-panel > .ografi-comment-skeleton,
html.dark body .ografi-comment-skeleton__card,
body.dark .ografi-comment-skeleton__card,
[data-theme="dark"] body .ografi-comment-skeleton__card {
    background: #111318 !important;
}

html.dark body .ografi-comment-skeleton__shape,
body.dark .ografi-comment-skeleton__shape,
[data-theme="dark"] body .ografi-comment-skeleton__shape {
    background: linear-gradient(105deg, #1e293b 0%, #334155 45%, #1e293b 82%) !important;
    background-size: 200% 100% !important;
}

@keyframes ografiCommentPreloaderOut {
    to {
        opacity: 0;
        visibility: hidden;
    }
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
}

@media (prefers-reduced-motion: reduce) {
    html body .ografi-comment-skeleton__shape {
        animation: none !important;
        background: #eef2fb !important;
    }

    html.dark body .ografi-comment-skeleton__shape,
    body.dark .ografi-comment-skeleton__shape,
    [data-theme="dark"] body .ografi-comment-skeleton__shape {
        background: #1e293b !important;
    }

    html body .ogx-comments-panel > .ografi-comment-skeleton.is-hiding {
        animation-duration: 1ms !important;
    }
}
</style>
<script data-ografi-comment-skeleton-script>
(() => {
    const skeletons = Array.from(document.querySelectorAll('[data-ografi-comment-skeleton]'));
    if (!skeletons.length) return;

    const minimumVisibleMs = 520;
    const maximumVisibleMs = 2400;

    const bootSkeleton = (skeleton) => {
        const panel = skeleton.closest('.ogx-comments-panel');
        if (!panel || skeleton.dataset.booted === '1') return;

        skeleton.dataset.booted = '1';
        skeleton.hidden = false;
        panel.setAttribute('aria-busy', 'true');

        let started = false;
        let finished = false;
        let observer = null;

        const hide = () => {
            if (finished) return;
            finished = true;

            if (observer) {
                observer.disconnect();
            }

            skeleton.classList.add('is-hiding');
            panel.setAttribute('aria-busy', 'false');

            window.setTimeout(() => {
                skeleton.hidden = true;
            }, 260);
        };

        const start = () => {
            if (started || finished) return;
            started = true;

            const startedAt = Date.now();

            const finish = () => {
                const elapsed = Date.now() - startedAt;
                const delay = Math.max(0, minimumVisibleMs - elapsed);
                window.setTimeout(hide, delay);
            };

            if (document.readyState === 'complete') {
                finish();
            } else {
                window.addEventListener('load', finish, { once: true });
            }

            window.setTimeout(hide, maximumVisibleMs);
        };

        if ('IntersectionObserver' in window) {
            observer = new IntersectionObserver((entries) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    start();
                }
            }, {
                root: null,
                rootMargin: '160px 0px',
                threshold: 0.01,
            });

            observer.observe(panel);
        } else {
            start();
        }

        if (window.location.hash === '#comments') {
            start();
        }
    };

    skeletons.forEach(bootSkeleton);
})();
</script>
HTML;

        $html = preg_replace('/<\/body>/i', $assets . "\n</body>", $html, 1) ?? ($html . $assets);
        $response->setContent($html);
        $response->headers->set('X-Ografi-Comment-Skeleton', 'v4');

        return $response;
    }
}
