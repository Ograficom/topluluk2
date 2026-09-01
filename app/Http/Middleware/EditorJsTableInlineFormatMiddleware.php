<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EditorJsTableInlineFormatMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->routeIs('blog.create', 'blog.post.edit', 'blog.repost.create')) {
            return $response;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type'));
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html)
            || $html === ''
            || str_contains($html, 'data-ografi-table-inline-format')
        ) {
            return $response;
        }

        $style = <<<'HTML'
<style data-ografi-editor-toolbar-width>
/*
 * EditorJS keeps ce-toolbar__content on its normal text-column width.
 * The project toolbar actions are 100% wide relative to that narrower box,
 * so on mobile they look squeezed inside the editor card. Make the toolbar
 * positioning box use the actual editor width instead.
 */
.create-page-fixed [data-editorjs-wrapper] .ce-toolbar__content {
    width: 100% !important;
    max-width: none !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    box-sizing: border-box !important;
}

.create-page-fixed [data-editorjs-wrapper] .ce-toolbar__actions {
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    max-width: none !important;
    min-width: 100% !important;
    box-sizing: border-box !important;
}

/* Do not let EditorJS popovers shrink as flex children of toolbar actions. */
.create-page-fixed [data-editorjs-wrapper] .ce-popover.ce-popover--opened {
    flex: 0 0 auto !important;
    flex-shrink: 0 !important;
    width: min(220px, calc(100vw - 20px)) !important;
    min-width: min(220px, calc(100vw - 20px)) !important;
    max-width: min(220px, calc(100vw - 20px)) !important;
    box-sizing: border-box !important;
    pointer-events: auto !important;
}

.create-page-fixed [data-editorjs-wrapper] .ce-popover.ce-popover--opened > .ce-popover__container,
.create-page-fixed [data-editorjs-wrapper] .ce-popover--opened .ce-popover__container {
    width: 100% !important;
    min-width: 100% !important;
    max-width: 100% !important;
    flex: 0 0 auto !important;
    box-sizing: border-box !important;
}

/* Inline toolbar itself must keep the width required by its controls. */
.create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar {
    flex-shrink: 0 !important;
    max-width: calc(100vw - 20px) !important;
    box-sizing: border-box !important;
    overflow: visible !important;
}

@media (max-width: 640px) {
    .create-page-fixed [data-editorjs-wrapper] .ce-toolbar__content {
        width: 100% !important;
        max-width: 100% !important;
    }

    .create-page-fixed [data-editorjs-wrapper] .ce-toolbar__actions {
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
    }
}
</style>
HTML;

        $script = <<<'HTML'
<script data-ografi-table-inline-format>
(() => {
    const allowedTags = new Set([
        'B',
        'STRONG',
        'I',
        'EM',
        'U',
        'S',
        'A',
        'MARK',
        'CODE',
        'BR'
    ]);

    const inlineMarkupPattern = /<\/?(?:b|strong|i|em|u|s|a|mark|code|br)\b/i;

    const safeHref = (value) => {
        const href = String(value || '').trim();
        if (!href) return null;

        try {
            const url = new URL(href, window.location.origin);
            if (!['http:', 'https:'].includes(url.protocol)) return null;
            return url.href;
        } catch (_) {
            return null;
        }
    };

    const sanitizeInlineHtml = (raw) => {
        const template = document.createElement('template');
        template.innerHTML = String(raw || '');

        const cleanNode = (node) => {
            if (node.nodeType === Node.TEXT_NODE) {
                return document.createTextNode(node.textContent || '');
            }

            if (node.nodeType !== Node.ELEMENT_NODE) {
                return document.createDocumentFragment();
            }

            const tag = node.tagName.toUpperCase();

            if (!allowedTags.has(tag)) {
                const fragment = document.createDocumentFragment();
                Array.from(node.childNodes).forEach((child) => {
                    fragment.appendChild(cleanNode(child));
                });
                return fragment;
            }

            const clean = document.createElement(tag.toLowerCase());

            if (tag === 'A') {
                const href = safeHref(node.getAttribute('href'));
                if (href) {
                    clean.setAttribute('href', href);
                    clean.setAttribute('target', '_blank');
                    clean.setAttribute('rel', 'nofollow noopener noreferrer');
                }
            }

            Array.from(node.childNodes).forEach((child) => {
                clean.appendChild(cleanNode(child));
            });

            return clean;
        };

        const fragment = document.createDocumentFragment();
        Array.from(template.content.childNodes).forEach((node) => {
            fragment.appendChild(cleanNode(node));
        });

        const holder = document.createElement('div');
        holder.appendChild(fragment);

        return holder.innerHTML;
    };

    const normalizeCell = (cell) => {
        if (!(cell instanceof HTMLElement) || !cell.classList.contains('tc-cell')) return;

        // Gercek inline HTML zaten varsa kullanicinin duzenlemesine dokunma.
        if (cell.children.length > 0) return;

        // Eski kayitlarda &lt;b&gt;...&lt;/b&gt; tabloya duz metin olarak geliyor.
        // textContent bunu yeniden <b>...</b> bicimine getirir.
        const raw = String(cell.textContent || '').trim();
        if (!raw || !inlineMarkupPattern.test(raw)) return;

        const safeHtml = sanitizeInlineHtml(raw);
        if (!safeHtml) return;

        cell.innerHTML = safeHtml;
    };

    const scan = (root) => {
        if (!root) return;

        if (root instanceof HTMLElement && root.classList.contains('tc-cell')) {
            normalizeCell(root);
        }

        root.querySelectorAll?.('.tc-cell').forEach(normalizeCell);
    };

    const start = () => {
        scan(document);

        const editorRoot = document.querySelector('[data-editorjs-wrapper]') || document.body;
        if (!editorRoot) return;

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        scan(node);
                    }
                });
            });
        });

        observer.observe(editorRoot, {
            childList: true,
            subtree: true
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
})();
</script>
HTML;

        $payload = $style . "\n" . $script;
        $html = preg_replace('/<\/body>/i', $payload . "\n</body>", $html, 1) ?? ($html . $payload);

        $response->setContent($html);
        $response->headers->set('X-Ografi-Table-Inline-Format', 'v2');

        return $response;
    }
}
