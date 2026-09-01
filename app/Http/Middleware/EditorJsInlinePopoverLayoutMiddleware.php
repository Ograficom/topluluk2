<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EditorJsInlinePopoverLayoutMiddleware
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
            || str_contains($html, 'data-ografi-inline-popover-layout')
        ) {
            return $response;
        }

        $style = <<<'HTML'
<style data-ografi-inline-popover-layout>
/*
 * Inline toolbar is a horizontal EditorJS PopoverInline.
 * Generic block-menu polish must never turn this into a vertical/scrolled menu.
 */
.create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar {
    width: auto !important;
    min-width: 0 !important;
    max-width: calc(100vw - 20px) !important;
    overflow: visible !important;
    box-sizing: border-box !important;
}

.create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar > .ce-popover--inline.ce-popover--opened {
    display: block !important;
    width: auto !important;
    min-width: 0 !important;
    max-width: calc(100vw - 20px) !important;
    height: auto !important;
    overflow: visible !important;
    flex: none !important;
    box-sizing: border-box !important;
}

/* Restore EditorJS PopoverInline's natural content width and horizontal layout. */
.create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar > .ce-popover--inline.ce-popover--opened > .ce-popover__container {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    width: max-content !important;
    min-width: max-content !important;
    max-width: calc(100vw - 20px) !important;
    height: 38px !important;
    min-height: 38px !important;
    padding: 4px !important;
    overflow: visible !important;
    box-sizing: border-box !important;
}

.create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar > .ce-popover--inline.ce-popover--opened > .ce-popover__container > .ce-popover__items {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    width: max-content !important;
    min-width: max-content !important;
    max-width: none !important;
    height: auto !important;
    min-height: 0 !important;
    max-height: none !important;
    padding: 0 !important;
    margin: 0 !important;
    gap: 0 !important;
    overflow: visible !important;
    overscroll-behavior: auto !important;
    scrollbar-width: none !important;
}

.create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar > .ce-popover--inline.ce-popover--opened > .ce-popover__container > .ce-popover__items::-webkit-scrollbar {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
}

/* Inline controls must keep their own width instead of shrinking into a thin column. */
.create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar > .ce-popover--inline.ce-popover--opened > .ce-popover__container > .ce-popover__items > .ce-popover-item,
.create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar > .ce-popover--inline.ce-popover--opened > .ce-popover__container > .ce-popover__items > .ce-popover-item-html,
.create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar > .ce-popover--inline.ce-popover--opened > .ce-popover__container > .ce-popover__items > .ce-popover-item-separator {
    flex: 0 0 auto !important;
    width: auto !important;
    max-width: none !important;
    margin-bottom: 0 !important;
}

.create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar > .ce-popover--inline.ce-popover--opened > .ce-popover__container > .ce-popover__items > .ce-popover-item {
    min-width: 30px !important;
    min-height: 30px !important;
    padding: 4px !important;
}

/* Keep nested inline-tool popovers (for example link input) as normal vertical popovers. */
.create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar .ce-popover--nested .ce-popover__container {
    width: var(--width, 220px) !important;
    min-width: var(--width, 220px) !important;
    max-width: min(220px, calc(100vw - 20px)) !important;
    height: fit-content !important;
    flex-direction: column !important;
    overflow: hidden !important;
}

.create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar .ce-popover--nested .ce-popover__items {
    display: block !important;
    width: 100% !important;
    min-width: 0 !important;
    max-height: 280px !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
}

@media (max-width: 640px) {
    .create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar,
    .create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar > .ce-popover--inline.ce-popover--opened {
        max-width: calc(100vw - 16px) !important;
    }

    .create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar > .ce-popover--inline.ce-popover--opened > .ce-popover__container {
        height: 46px !important;
        min-height: 46px !important;
        max-width: calc(100vw - 16px) !important;
    }

    .create-page-fixed [data-editorjs-wrapper] .ce-inline-toolbar > .ce-popover--inline.ce-popover--opened > .ce-popover__container > .ce-popover__items > .ce-popover-item {
        min-width: 36px !important;
        min-height: 36px !important;
        padding: 4px !important;
    }
}
</style>
HTML;

        $html = preg_replace('/<\/body>/i', $style . "\n</body>", $html, 1) ?? ($html . $style);

        $response->setContent($html);
        $response->headers->set('X-Ografi-Inline-Popover-Layout', 'v1');

        return $response;
    }
}
