<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostShowReactionLayoutMiddleware
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
        if (! is_string($html) || $html === '') {
            return $response;
        }

        $post = $request->route('post');
        if (! $post instanceof Post) {
            $slug = trim((string) $post);
            $post = $slug !== ''
                ? Post::withoutGlobalScopes()->where('slug', $slug)->first()
                : null;
        }

        $assets = $this->assets((int) ($post?->views_count ?? 0));
        $html = preg_replace('/<\/body>/i', $assets . "\n</body>", $html, 1) ?? ($html . $assets);

        $response->setContent($html);

        return $response;
    }

    private function assets(int $viewsCount): string
    {
        $views = max(0, $viewsCount);

        return <<<HTML
<style data-ografi-post-show-reaction-fix>
/* Post-show mobile layout: match the compact reference supplied for Ografi. */
@media (max-width: 640px) {
    article .post-show-tags-shell {
        margin: 0 !important;
        padding: 12px 0 0 !important;
        border-top: 1px solid #e5e7eb !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    article .post-show-tags-row {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        gap: 4px 10px !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    article .post-show-tag-link {
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: #2563eb !important;
        font-size: 15px !important;
        font-weight: 500 !important;
        line-height: 1.45 !important;
        text-decoration: none !important;
    }

    article .post-show-reaction-root {
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    article .post-show-reaction-root > .flex {
        gap: 10px !important;
    }

    article .post-show-reaction-root [data-reaction-pills] {
        justify-content: flex-start !important;
        gap: 8px !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    article .post-show-reaction-root .rx-summary-pill {
        min-height: 36px !important;
        gap: 5px !important;
        padding: 6px 10px !important;
        border-radius: 999px !important;
        background: #f3f4f6 !important;
        color: #111827 !important;
        font-size: 14px !important;
        box-shadow: none !important;
    }

    article .post-show-reaction-root .rx-summary-pill__icon {
        width: 20px !important;
        height: 20px !important;
        font-size: 17px !important;
    }

    article .post-show-reaction-root .rx-summary-pill__icon img,
    article .post-show-reaction-root .rx-summary-pill__icon svg,
    article .post-show-reaction-root .rx-summary-pill__icon iconify-icon {
        width: 20px !important;
        height: 20px !important;
    }

    article .post-show-reaction-root .rx-add-trigger {
        width: 36px !important;
        height: 36px !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 999px !important;
        background: #f8fafc !important;
        color: #111827 !important;
        box-shadow: none !important;
    }

    article .post-show-action-row {
        display: flex !important;
        width: 100% !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        gap: 12px !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    article .post-show-action-row .post-show-action,
    article .post-show-action-row .post-reaction-bookmark-btn,
    article .post-show-action-row [data-share-btn] {
        min-width: 0 !important;
        width: auto !important;
        height: 32px !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: #111827 !important;
        box-shadow: none !important;
    }

    article .post-show-action-row .post-reaction-bookmark-btn,
    article .post-show-action-row [data-share-btn] {
        width: 32px !important;
    }

    article .post-show-action-row .post-show-action {
        gap: 3px !important;
    }

    article .post-show-action-row iconify-icon,
    article .post-show-action-row .post-reaction-bookmark-btn svg {
        width: 20px !important;
        height: 20px !important;
        font-size: 20px !important;
    }

    article .post-show-view-count {
        display: inline-flex !important;
        height: 32px !important;
        align-items: center !important;
        gap: 6px !important;
        margin-left: auto !important;
        color: #111827 !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        line-height: 1 !important;
    }

    article .post-show-view-count iconify-icon {
        width: 21px !important;
        height: 21px !important;
        font-size: 21px !important;
    }

    /* The picker must stay compact and open just below the reaction-add icon. */
    article [data-rx-panel],
    article .rx-panel,
    article .rx-panel.rx-panel--compact {
        width: 188px !important;
        max-width: calc(100vw - 16px) !important;
        padding: 8px 9px 9px !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 8px !important;
        background: #ffffff !important;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12) !important;
    }

    article [data-rx-panel] .rx-panel__title,
    article .rx-panel .rx-panel__title {
        margin: 0 0 7px !important;
        padding: 0 1px 6px !important;
        border-bottom: 1px solid #e5e7eb !important;
        color: #6b7280 !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        line-height: 1.2 !important;
        text-transform: none !important;
    }

    article [data-rx-panel] .rx-panel__options,
    article .rx-panel .rx-panel__options {
        display: grid !important;
        grid-template-columns: repeat(4, 34px) !important;
        justify-content: center !important;
        gap: 5px 7px !important;
        max-height: 190px !important;
        overflow-y: auto !important;
    }

    article [data-rx-panel] .rx-panel__option,
    article .rx-panel .rx-panel__option {
        width: 34px !important;
        height: 34px !important;
        margin: 0 !important;
        padding: 0 !important;
        border-radius: 999px !important;
        background: transparent !important;
    }

    article [data-rx-panel] .rx-panel__option-icon,
    article .rx-panel .rx-panel__option-icon {
        font-size: 24px !important;
        line-height: 1 !important;
    }

    article [data-rx-panel] .rx-panel__option-icon img,
    article [data-rx-panel] .rx-panel__option-icon svg,
    article [data-rx-panel] .rx-panel__option-icon iconify-icon,
    article .rx-panel .rx-panel__option-icon img,
    article .rx-panel .rx-panel__option-icon svg,
    article .rx-panel .rx-panel__option-icon iconify-icon {
        width: 27px !important;
        height: 27px !important;
        border-radius: 999px !important;
        object-fit: cover !important;
    }
}

html.dark article .post-show-tags-shell {
    border-top-color: #334155 !important;
}

html.dark article .post-show-tag-link {
    color: #60a5fa !important;
}

html.dark article .post-show-reaction-root .rx-summary-pill,
html.dark article .post-show-reaction-root .rx-add-trigger {
    border-color: #334155 !important;
    background: #1e293b !important;
    color: #f8fafc !important;
}

html.dark article .post-show-action-row .post-show-action,
html.dark article .post-show-action-row .post-reaction-bookmark-btn,
html.dark article .post-show-action-row [data-share-btn],
html.dark article .post-show-view-count {
    color: #f8fafc !important;
}
</style>
<script data-ografi-post-show-reaction-fix>
(() => {
    const viewsCount = {$views};

    const applyPostShowLayout = () => {
        const article = document.querySelector('article');
        if (!article) return;

        const tagLinks = Array.from(article.querySelectorAll('a')).filter((link) => {
            const text = (link.textContent || '').trim();
            const href = link.getAttribute('href') || '';
            return text.startsWith('#') && href.includes('tag=');
        });

        if (tagLinks.length) {
            const tagRow = tagLinks[0].parentElement;
            const tagShell = tagRow?.parentElement;

            tagRow?.classList.add('post-show-tags-row');
            tagShell?.classList.add('post-show-tags-shell');
            tagLinks.forEach((link) => link.classList.add('post-show-tag-link'));
        }

        const reactionRoot = article.querySelector('[data-reaction-root]');
        if (reactionRoot) {
            reactionRoot.classList.add('post-show-reaction-root');

            const commentLink = reactionRoot.querySelector('a[href="#comments"]');
            if (commentLink) {
                commentLink.classList.add('post-show-action');
                const commentIcon = commentLink.querySelector('iconify-icon');
                commentIcon?.setAttribute('icon', 'lucide:message-circle-more');
            }

            const actionRow = commentLink?.parentElement;
            if (actionRow) {
                actionRow.classList.add('post-show-action-row');

                if (!actionRow.querySelector('[data-post-show-view-count]')) {
                    const view = document.createElement('span');
                    view.className = 'post-show-view-count';
                    view.setAttribute('data-post-show-view-count', '');
                    view.setAttribute('title', 'Görüntülenme');
                    view.innerHTML = '<iconify-icon icon="lucide:eye"></iconify-icon><span>' + new Intl.NumberFormat('tr-TR').format(viewsCount) + '</span>';
                    actionRow.appendChild(view);
                }
            }
        }

        article.querySelectorAll('[data-rx-panel]').forEach((panel) => {
            panel.classList.add('post-show-rx-panel');
            const title = panel.querySelector('.rx-panel__title');
            if (title) title.textContent = 'Tepkiler';
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyPostShowLayout, { once: true });
    } else {
        applyPostShowLayout();
    }

    document.addEventListener('click', () => requestAnimationFrame(applyPostShowLayout), true);
})();
</script>
HTML;
    }
}
