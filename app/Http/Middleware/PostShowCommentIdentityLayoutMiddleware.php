<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostShowCommentIdentityLayoutMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->routeIs('blog.post')) {
            return $response;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type'));
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html) || $html === '' || ! str_contains($html, 'data-ogx-comment')) {
            return $response;
        }

        $assets = <<<'HTML'
<style data-ografi-comment-identity-layout>
/* Comment identity row: avatar, username and badges stay together. */
html body .ogx-comments-panel [data-ogx-comment].ogx-comment {
    display: block !important;
    grid-template-columns: none !important;
    column-gap: 0 !important;
}

html body .ogx-comments-panel [data-ogx-comment] > .ogx-comment-main {
    width: 100% !important;
    min-width: 0 !important;
}

html body .ogx-comments-panel [data-ogx-comment] .ogx-meta {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 5px !important;
    min-height: 30px !important;
    margin: 0 !important;
}

html body .ogx-comments-panel [data-ogx-comment] .ogx-meta > .ogx-avatar {
    display: inline-grid !important;
    place-items: center !important;
    width: 28px !important;
    min-width: 28px !important;
    max-width: 28px !important;
    height: 28px !important;
    min-height: 28px !important;
    max-height: 28px !important;
    margin: 0 3px 0 0 !important;
    border-radius: 999px !important;
    overflow: hidden !important;
    flex: 0 0 28px !important;
    font-size: 10px !important;
    line-height: 1 !important;
}

html body .ogx-comments-panel [data-ogx-comment] .ogx-meta > .ogx-avatar img {
    display: block !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
}

html body .ogx-comments-panel [data-ogx-comment] .ogx-meta > .ogx-username {
    display: inline-flex !important;
    align-items: center !important;
    min-width: 0 !important;
    margin: 0 !important;
    line-height: 28px !important;
}

/* Verification/custom badges rendered by x-verification-badge must never collapse. */
html body .ogx-comments-panel [data-ogx-comment] .ogx-meta > [role="img"] {
    display: inline-flex !important;
    visibility: visible !important;
    opacity: 1 !important;
    align-items: center !important;
    justify-content: center !important;
    width: 16px !important;
    min-width: 16px !important;
    max-width: 16px !important;
    height: 16px !important;
    min-height: 16px !important;
    max-height: 16px !important;
    margin: 0 !important;
    overflow: visible !important;
    flex: 0 0 16px !important;
}

html body .ogx-comments-panel [data-ogx-comment] .ogx-meta > [role="img"] :is(svg, img, span) {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 16px !important;
    min-width: 16px !important;
    max-width: 16px !important;
    height: 16px !important;
    min-height: 16px !important;
    max-height: 16px !important;
}

html body .ogx-comments-panel [data-ogx-comment] .ogx-meta > .ogx-author-label {
    display: inline-flex !important;
    align-items: center !important;
    min-height: 20px !important;
    margin: 0 !important;
    padding: 0 5px !important;
    border-radius: 5px !important;
    color: #2563eb !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    line-height: 20px !important;
    white-space: nowrap !important;
}

/* Keep timestamp/content aligned with the username rather than under the avatar. */
html body .ogx-comments-panel [data-ogx-comment] .ogx-submeta,
html body .ogx-comments-panel [data-ogx-comment] .ogx-comment-text,
html body .ogx-comments-panel [data-ogx-comment] .ogx-comment-actions,
html body .ogx-comments-panel [data-ogx-comment] .ogx-edit-form,
html body .ogx-comments-panel [data-ogx-comment] .ogx-reply-form,
html body .ogx-comments-panel [data-ogx-comment] > .ogx-comment-main > .ogx-replies {
    margin-left: 36px !important;
}

html.dark body .ogx-comments-panel [data-ogx-comment] .ogx-meta > .ogx-author-label {
    color: #60a5fa !important;
}

@media (max-width: 640px) {
    html body .ogx-comments-panel [data-ogx-comment] .ogx-meta > .ogx-avatar {
        width: 26px !important;
        min-width: 26px !important;
        max-width: 26px !important;
        height: 26px !important;
        min-height: 26px !important;
        max-height: 26px !important;
        flex-basis: 26px !important;
    }

    html body .ogx-comments-panel [data-ogx-comment] .ogx-submeta,
    html body .ogx-comments-panel [data-ogx-comment] .ogx-comment-text,
    html body .ogx-comments-panel [data-ogx-comment] .ogx-comment-actions,
    html body .ogx-comments-panel [data-ogx-comment] .ogx-edit-form,
    html body .ogx-comments-panel [data-ogx-comment] .ogx-reply-form,
    html body .ogx-comments-panel [data-ogx-comment] > .ogx-comment-main > .ogx-replies {
        margin-left: 34px !important;
    }
}
</style>
<script data-ografi-comment-identity-layout>
(() => {
    const decorateComment = (comment) => {
        if (!(comment instanceof Element)) return;

        const main = Array.from(comment.children).find((child) => child.classList?.contains('ogx-comment-main'));
        if (!main) return;

        const meta = Array.from(main.children).find((child) => child.classList?.contains('ogx-meta'));
        if (!meta) return;

        const avatar = comment.querySelector('.ogx-avatar');
        const username = meta.querySelector('.ogx-username');

        if (avatar && avatar.parentElement !== meta) {
            if (username) {
                meta.insertBefore(avatar, username);
            } else {
                meta.prepend(avatar);
            }
        }

        const submeta = Array.from(main.children).find((child) => child.classList?.contains('ogx-submeta'));
        if (submeta) {
            Array.from(submeta.children)
                .filter((child) => child.classList?.contains('ogx-author-label'))
                .forEach((badge) => meta.appendChild(badge));
        }

        meta.querySelectorAll('[role="img"]').forEach((badge) => {
            badge.removeAttribute('hidden');
            badge.setAttribute('data-comment-identity-badge', '');
        });

        comment.setAttribute('data-comment-identity-ready', '');
    };

    const apply = (root = document) => {
        root.querySelectorAll?.('.ogx-comments-panel [data-ogx-comment]').forEach(decorateComment);
        if (root.matches?.('.ogx-comments-panel [data-ogx-comment]')) {
            decorateComment(root);
        }
    };

    const boot = () => {
        apply(document);

        const panel = document.querySelector('.ogx-comments-panel');
        if (!panel || !('MutationObserver' in window)) return;

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node instanceof Element) apply(node);
                });
            });
        });

        observer.observe(panel, { childList: true, subtree: true });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();
</script>
HTML;

        $html = preg_replace('/<\/body>/i', $assets . "\n</body>", $html, 1) ?? ($html . $assets);
        $response->setContent($html);

        return $response;
    }
}
