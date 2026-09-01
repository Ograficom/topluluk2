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

    const inlineSelector = 'b,strong,i,em,u,s,a,mark,code,br';
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

    const decodeEntities = (value) => {
        const textarea = document.createElement('textarea');
        textarea.innerHTML = String(value || '');
        return textarea.value;
    };

    const normalizeRawMarkup = (value) => {
        let raw = String(value || '').trim();

        // Eski kayitlarda HTML bir veya iki kez encode edilmis olabilir.
        for (let i = 0; i < 2 && raw && !inlineMarkupPattern.test(raw); i += 1) {
            if (!/&(?:lt|gt|amp|quot|#0*39);/i.test(raw)) break;
            const decoded = decodeEntities(raw);
            if (decoded === raw) break;
            raw = decoded.trim();
        }

        return raw;
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

    const editableTarget = (cell) => {
        if (!(cell instanceof HTMLElement)) return null;
        if (cell.matches('[contenteditable="true"]')) return cell;
        return cell.querySelector('[contenteditable="true"]') || cell;
    };

    const normalizeCell = (cell) => {
        if (!(cell instanceof HTMLElement) || !cell.classList.contains('tc-cell')) return;

        const target = editableTarget(cell);
        if (!(target instanceof HTMLElement)) return;

        // Zaten gercek inline HTML varsa tekrar dokunma.
        if (target.querySelector(inlineSelector)) return;

        const raw = normalizeRawMarkup(target.textContent || '');
        if (!raw || !inlineMarkupPattern.test(raw)) return;

        const safeHtml = sanitizeInlineHtml(raw);
        if (!safeHtml || safeHtml === target.innerHTML) return;

        target.innerHTML = safeHtml;
    };

    const scan = (root) => {
        if (!root) return;

        if (root instanceof HTMLElement && root.classList.contains('tc-cell')) {
            normalizeCell(root);
        }

        root.querySelectorAll?.('.tc-cell').forEach(normalizeCell);
    };

    const scanMutationTarget = (target) => {
        const element = target instanceof HTMLElement
            ? target
            : target?.parentElement;

        if (!(element instanceof HTMLElement)) return;

        const cell = element.classList.contains('tc-cell')
            ? element
            : element.closest('.tc-cell');

        if (cell) normalizeCell(cell);
    };

    const start = () => {
        const editorRoot = document.querySelector('[data-editorjs-wrapper]') || document.body;
        if (!editorRoot) return;

        const rescan = () => scan(editorRoot);
        rescan();

        // EditorJS tablo verisini DOM olustuktan sonra asenkron yerlestirebildigi icin
        // ilk render penceresinde birkac kez daha tara.
        [50, 180, 500, 1200].forEach((delay) => window.setTimeout(rescan, delay));

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                scanMutationTarget(mutation.target);

                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        scan(node);
                    } else if (node.nodeType === Node.TEXT_NODE) {
                        scanMutationTarget(node);
                    }
                });
            });
        });

        observer.observe(editorRoot, {
            childList: true,
            characterData: true,
            subtree: true
        });

        editorRoot.addEventListener('input', (event) => {
            const target = event.target instanceof HTMLElement ? event.target : null;
            const cell = target?.closest('.tc-cell');
            if (cell) queueMicrotask(() => normalizeCell(cell));
        }, true);
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
        $response->headers->set('X-Ografi-Table-Inline-Format', 'v4');

        return $response;
    }
}
