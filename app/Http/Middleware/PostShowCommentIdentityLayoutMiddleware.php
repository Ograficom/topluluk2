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

/* Comment and filter popovers use one typography/spacing system. */
html body .ogx-comments-panel .ogx-filter-item,
html body .ogx-comments-panel .ogx-comment-menu button,
html body .ogx-comments-panel .ogx-comment-menu a {
    min-height: 34px !important;
    padding: 7px 9px !important;
    gap: 8px !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 18px !important;
}

html body .ogx-comments-panel .ogx-filter-item {
    justify-content: flex-start !important;
}

html body .ogx-comments-panel .ogx-filter-item::after {
    margin-left: auto !important;
}

html body .ogx-comments-panel .ogx-filter-item > iconify-icon,
html body .ogx-comments-panel .ogx-comment-menu-icon {
    width: 16px !important;
    min-width: 16px !important;
    height: 16px !important;
    font-size: 16px !important;
    flex: 0 0 16px !important;
}

/* Up/down vote buttons: neutral normally, green/red on interaction. */
html body .ogx-comments-panel .ogx-vote-btn {
    width: 28px !important;
    min-width: 28px !important;
    height: 28px !important;
    border-radius: 999px !important;
    color: #374151 !important;
}

html body .ogx-comments-panel .ogx-vote-btn[aria-label="Beğen"]:is(:hover, :focus-visible) {
    background: #dcfce7 !important;
    color: #16a34a !important;
}

html body .ogx-comments-panel .ogx-vote-btn[aria-label="Beğen"]:active {
    background: #bbf7d0 !important;
    color: #15803d !important;
}

html body .ogx-comments-panel .ogx-vote-btn[aria-label="Beğenme"]:is(:hover, :focus-visible) {
    background: #fee2e2 !important;
    color: #ef4444 !important;
}

html body .ogx-comments-panel .ogx-vote-btn[aria-label="Beğenme"]:active {
    background: #fecaca !important;
    color: #dc2626 !important;
}

html.dark body .ogx-comments-panel .ogx-vote-btn {
    color: #d4d4d8 !important;
}

html.dark body .ogx-comments-panel .ogx-vote-btn[aria-label="Beğen"]:is(:hover, :focus-visible) {
    background: rgba(34, 197, 94, .16) !important;
    color: #4ade80 !important;
}

html.dark body .ogx-comments-panel .ogx-vote-btn[aria-label="Beğenme"]:is(:hover, :focus-visible) {
    background: rgba(239, 68, 68, .16) !important;
    color: #f87171 !important;
}

/* Reply/edit textarea caret must follow the typed text normally. */
html body .ogx-comments-panel .ogx-reply-compose textarea {
    position: static !important;
    display: block !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 6px 0 !important;
    border: 0 !important;
    outline: 0 !important;
    background: transparent !important;
    text-align: left !important;
    direction: ltr !important;
    unicode-bidi: plaintext !important;
    text-indent: 0 !important;
    writing-mode: horizontal-tb !important;
    letter-spacing: normal !important;
    caret-color: currentColor !important;
    transform: none !important;
}

html body .ogx-comments-panel .ogx-reply-compose textarea::placeholder {
    text-align: left !important;
    direction: ltr !important;
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
    const directChild = (parent, className) => {
        if (!parent) return null;
        return Array.from(parent.children).find((child) => child.classList?.contains(className)) || null;
    };

    const enforceCommentOwnershipUi = (comment, main) => {
        if (comment.getAttribute('data-ogx-mine') === '1') return;

        const actions = directChild(main, 'ogx-comment-actions');
        const menu = actions?.querySelector('[data-comment-more-menu]');

        if (menu) {
            menu.querySelectorAll('[data-comment-edit-toggle]').forEach((node) => node.remove());
            menu.querySelectorAll('form').forEach((form) => {
                const method = form.querySelector('input[name="_method"]')?.value?.toUpperCase();
                if (method === 'DELETE') form.remove();
            });
        }

        const editForm = directChild(main, 'ogx-edit-form');
        if (editForm) editForm.remove();
    };

    const decorateComment = (comment) => {
        if (!(comment instanceof Element)) return;

        const main = directChild(comment, 'ogx-comment-main');
        if (!main) return;

        const meta = directChild(main, 'ogx-meta');
        if (!meta) return;

        const avatar = Array.from(comment.children).find((child) => child.classList?.contains('ogx-avatar')) || null;
        const username = meta.querySelector('.ogx-username');

        if (avatar && avatar.parentElement !== meta) {
            if (username) {
                meta.insertBefore(avatar, username);
            } else {
                meta.prepend(avatar);
            }
        }

        const submeta = directChild(main, 'ogx-submeta');
        if (submeta) {
            Array.from(submeta.children)
                .filter((child) => child.classList?.contains('ogx-author-label'))
                .forEach((badge) => meta.appendChild(badge));
        }

        meta.querySelectorAll('[role="img"]').forEach((badge) => {
            badge.removeAttribute('hidden');
            badge.setAttribute('data-comment-identity-badge', '');
        });

        enforceCommentOwnershipUi(comment, main);
        comment.setAttribute('data-comment-identity-ready', '');
    };

    const decorateFilterMenu = (root = document) => {
        const iconMap = {
            popular: 'lucide:flame',
            new: 'lucide:clock-3',
        };

        root.querySelectorAll?.('.ogx-comments-panel [data-ogx-sort]').forEach((item) => {
            if (item.querySelector(':scope > iconify-icon[data-comment-filter-icon]')) return;

            const mode = item.getAttribute('data-ogx-sort') || '';
            const icon = document.createElement('iconify-icon');
            icon.setAttribute('icon', iconMap[mode] || 'lucide:list-filter');
            icon.setAttribute('data-comment-filter-icon', '');
            icon.setAttribute('aria-hidden', 'true');
            item.prepend(icon);
        });
    };

    const apply = (root = document) => {
        root.querySelectorAll?.('.ogx-comments-panel [data-ogx-comment]').forEach(decorateComment);
        if (root.matches?.('.ogx-comments-panel [data-ogx-comment]')) {
            decorateComment(root);
        }
        decorateFilterMenu(root);
    };

    const moveReplyCaretToEnd = (toggle) => {
        const root = toggle.closest?.('[data-ogx-comments]');
        if (!root) return;

        const selector = toggle.getAttribute('data-comment-reply-toggle');
        if (!selector) return;

        window.setTimeout(() => {
            const form = root.querySelector(selector);
            const textarea = form?.querySelector('textarea[name="content"]');
            if (!textarea || !form.classList.contains('is-open')) return;

            const end = textarea.value.length;
            textarea.focus({ preventScroll: true });
            try {
                textarea.setSelectionRange(end, end);
            } catch (_) {
                // Browser does not support selection range for this field.
            }
        }, 0);
    };

    document.addEventListener('click', (event) => {
        const toggle = event.target?.closest?.('[data-comment-reply-toggle]');
        if (toggle) moveReplyCaretToEnd(toggle);
    }, true);

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
