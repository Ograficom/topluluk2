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

/* Yorum yoksa bos durum mesaji/karti hic gosterilmez. */
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
    background: #fffcf5 !important;
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
    height: auto !important;
    min-height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
    border: 0 !important;
    background: #fffcf5 !important;
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
    background: #ece2cd !important;
}

html body .ografi-comment-skeleton__shape::after {
    content: "" !important;
    position: absolute !important;
    inset: 0 !important;
    transform: translateX(-120%) !important;
    background: linear-gradient(105deg, transparent 0%, rgba(255, 255, 255, .78) 45%, rgba(14, 124, 134, .12) 60%, transparent 82%) !important;
    animation: ografiCommentSkeletonWave 980ms ease-in-out infinite !important;
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
}

html.dark body .ografi-comment-skeleton__shape::after,
body.dark .ografi-comment-skeleton__shape::after,
[data-theme="dark"] body .ografi-comment-skeleton__shape::after {
    background: linear-gradient(105deg, transparent 0%, rgba(255, 255, 255, .14) 45%, transparent 82%) !important;
}

@keyframes ografiCommentSkeletonWave {
    to {
        transform: translateX(120%);
    }
}

@keyframes ografiCommentPreloaderOut {
    to {
        opacity: 0;
        visibility: hidden;
    }
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

@media (prefers-reduced-motion: reduce) {
    html body .ografi-comment-skeleton__shape::after {
        display: none !important;
        animation: none !important;
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
        $response->headers->set('X-Ografi-Comment-Skeleton', 'v6');

        return $response;
    }
}
