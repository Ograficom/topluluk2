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
    html:not(.dark) body .contact-page form input.contact-field.contact-field,
    html:not(.dark) body .contact-page form textarea.contact-field.contact-field {
        width: 100% !important;
        border: 1px solid #dfe2e7 !important;
        border-radius: 12px !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        background-image: none !important;
        color: #111827 !important;
        -webkit-text-fill-color: #111827 !important;
        caret-color: #111827 !important;
        opacity: 1 !important;
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
        filter: none !important;
        outline: none !important;
        color-scheme: light !important;
        -webkit-appearance: none !important;
        appearance: none !important;
    }

    html:not(.dark) body .contact-page form input.contact-field.contact-field {
        height: 40px !important;
        min-height: 40px !important;
        padding: 0 14px !important;
    }

    html:not(.dark) body .contact-page form textarea.contact-field.contact-field {
        min-height: 118px !important;
        padding: 12px 14px !important;
    }

    html:not(.dark) body .contact-page form input.contact-field.contact-field:hover,
    html:not(.dark) body .contact-page form textarea.contact-field.contact-field:hover {
        border-color: #d5d9df !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
    }

    html:not(.dark) body .contact-page form input.contact-field.contact-field:focus,
    html:not(.dark) body .contact-page form input.contact-field.contact-field:focus-visible,
    html:not(.dark) body .contact-page form textarea.contact-field.contact-field:focus,
    html:not(.dark) body .contact-page form textarea.contact-field.contact-field:focus-visible {
        border-color: #c9ced6 !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
        outline: none !important;
    }

    html:not(.dark) body .contact-page form input.contact-field.contact-field:-webkit-autofill,
    html:not(.dark) body .contact-page form input.contact-field.contact-field:-webkit-autofill:hover,
    html:not(.dark) body .contact-page form input.contact-field.contact-field:-webkit-autofill:focus,
    html:not(.dark) body .contact-page form input.contact-field.contact-field:-webkit-autofill:active,
    html:not(.dark) body .contact-page form input.contact-field.contact-field:autofill {
        -webkit-text-fill-color: #111827 !important;
        -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
        box-shadow: 0 0 0 1000px #ffffff inset !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        caret-color: #111827 !important;
        transition: background-color 99999s ease-out 0s !important;
    }
</style>
HTML;

        // View icindeki eski stil kurallarindan sonra gelsin ki acik temada
        // gri alan birakan global/input kurallari bu tasarimi ezemesin.
        if (str_contains($content, '</body>')) {
            $content = str_replace('</body>', $style."\n</body>", $content);
            $response->setContent($content);
        }

        return $response;
    }
}
