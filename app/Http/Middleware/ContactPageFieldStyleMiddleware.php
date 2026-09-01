<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContactPageFieldStyleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->is('contact') || ! $request->isMethod('GET')) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (! str_contains($contentType, 'text/html') || ! method_exists($response, 'getContent')) {
            return $response;
        }

        $content = $response->getContent();
        if (! is_string($content) || $content === '' || str_contains($content, 'data-contact-field-override')) {
            return $response;
        }

        $style = <<<'HTML'
<style data-contact-field-override>
    html:not(.dark) body .contact-page form input.contact-field,
    html:not(.dark) body .contact-page form textarea.contact-field {
        width: 100% !important;
        border: 1px solid #dcdfe4 !important;
        border-radius: 12px !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        background-image: none !important;
        color: #111827 !important;
        opacity: 1 !important;
        box-shadow: inset 0 0 0 1000px #ffffff !important;
        -webkit-box-shadow: inset 0 0 0 1000px #ffffff !important;
        -webkit-text-fill-color: #111827 !important;
        caret-color: #111827 !important;
        color-scheme: light !important;
        filter: none !important;
        outline: none !important;
        -webkit-appearance: none !important;
        appearance: none !important;
    }

    html:not(.dark) body .contact-page form input.contact-field {
        height: 36px !important;
        min-height: 36px !important;
        padding: 0 13px !important;
    }

    html:not(.dark) body .contact-page form textarea.contact-field {
        min-height: 118px !important;
        padding: 11px 13px !important;
    }

    html:not(.dark) body .contact-page form input.contact-field:hover,
    html:not(.dark) body .contact-page form textarea.contact-field:hover {
        border-color: #cfd4da !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        box-shadow: inset 0 0 0 1000px #ffffff !important;
        -webkit-box-shadow: inset 0 0 0 1000px #ffffff !important;
    }

    html:not(.dark) body .contact-page form input.contact-field:focus,
    html:not(.dark) body .contact-page form input.contact-field:focus-visible,
    html:not(.dark) body .contact-page form textarea.contact-field:focus,
    html:not(.dark) body .contact-page form textarea.contact-field:focus-visible {
        border-color: #2563eb !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        box-shadow: inset 0 0 0 1000px #ffffff, 0 0 0 1px #2563eb !important;
        -webkit-box-shadow: inset 0 0 0 1000px #ffffff, 0 0 0 1px #2563eb !important;
        outline: none !important;
    }

    html:not(.dark) body .contact-page form input.contact-field:-webkit-autofill,
    html:not(.dark) body .contact-page form input.contact-field:-webkit-autofill:hover,
    html:not(.dark) body .contact-page form input.contact-field:-webkit-autofill:focus,
    html:not(.dark) body .contact-page form input.contact-field:-webkit-autofill:active {
        -webkit-text-fill-color: #111827 !important;
        -webkit-box-shadow: inset 0 0 0 1000px #ffffff !important;
        box-shadow: inset 0 0 0 1000px #ffffff !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        caret-color: #111827 !important;
        transition: background-color 99999s ease-out 0s !important;
    }
</style>
HTML;

        if (str_contains($content, '</head>')) {
            $content = str_replace('</head>', $style."\n</head>", $content);
            $response->setContent($content);
        }

        return $response;
    }
}
