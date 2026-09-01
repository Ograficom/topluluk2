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

        $html = preg_replace('/<\/body>/i', $script . "\n</body>", $html, 1) ?? ($html . $script);

        $response->setContent($html);
        $response->headers->set('X-Ografi-Table-Inline-Format', 'v1');

        return $response;
    }
}
