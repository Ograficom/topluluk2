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

        $override = <<<'HTML'
<style data-contact-field-override>
    html:not(.dark) body #contact-full-name,
    html:not(.dark) body #contact-email,
    html:not(.dark) body #contact-subject,
    html:not(.dark) body #contact-message {
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

    html:not(.dark) body #contact-full-name,
    html:not(.dark) body #contact-email,
    html:not(.dark) body #contact-subject {
        height: 36px !important;
        min-height: 36px !important;
        padding: 0 14px !important;
    }

    html:not(.dark) body #contact-message {
        min-height: 118px !important;
        padding: 12px 14px !important;
    }

    html:not(.dark) body #contact-full-name:hover,
    html:not(.dark) body #contact-email:hover,
    html:not(.dark) body #contact-subject:hover,
    html:not(.dark) body #contact-message:hover {
        border-color: #d2d6dc !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
    }

    html:not(.dark) body #contact-full-name:focus,
    html:not(.dark) body #contact-email:focus,
    html:not(.dark) body #contact-subject:focus,
    html:not(.dark) body #contact-message:focus {
        border-color: #2563eb !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 1px #2563eb !important;
        -webkit-box-shadow: 0 0 0 1px #2563eb !important;
    }

    html:not(.dark) body #contact-full-name:-webkit-autofill,
    html:not(.dark) body #contact-full-name:-webkit-autofill:hover,
    html:not(.dark) body #contact-full-name:-webkit-autofill:focus,
    html:not(.dark) body #contact-email:-webkit-autofill,
    html:not(.dark) body #contact-email:-webkit-autofill:hover,
    html:not(.dark) body #contact-email:-webkit-autofill:focus,
    html:not(.dark) body #contact-subject:-webkit-autofill,
    html:not(.dark) body #contact-subject:-webkit-autofill:hover,
    html:not(.dark) body #contact-subject:-webkit-autofill:focus {
        -webkit-text-fill-color: #111827 !important;
        -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
        box-shadow: 0 0 0 1000px #ffffff inset !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        caret-color: #111827 !important;
        transition: background-color 99999s ease-out 0s !important;
    }
</style>
<script data-contact-field-force-script>
(function () {
    const ids = ['contact-full-name', 'contact-email', 'contact-subject', 'contact-message'];

    const applyContactFieldStyle = function () {
        if (document.documentElement.classList.contains('dark')) return;

        ids.forEach(function (id) {
            const field = document.getElementById(id);
            if (!field) return;

            field.style.setProperty('background', '#ffffff', 'important');
            field.style.setProperty('background-color', '#ffffff', 'important');
            field.style.setProperty('background-image', 'none', 'important');
            field.style.setProperty('border', '1px solid #dfe2e7', 'important');
            field.style.setProperty('border-radius', '12px', 'important');
            field.style.setProperty('box-shadow', 'none', 'important');
            field.style.setProperty('-webkit-box-shadow', 'none', 'important');
            field.style.setProperty('color', '#111827', 'important');
            field.style.setProperty('-webkit-text-fill-color', '#111827', 'important');
            field.style.setProperty('opacity', '1', 'important');
            field.style.setProperty('filter', 'none', 'important');

            if (id === 'contact-message') {
                field.style.setProperty('min-height', '118px', 'important');
            } else {
                field.style.setProperty('height', '36px', 'important');
                field.style.setProperty('min-height', '36px', 'important');
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyContactFieldStyle, { once: true });
    } else {
        applyContactFieldStyle();
    }

    window.addEventListener('pageshow', applyContactFieldStyle);
    setTimeout(applyContactFieldStyle, 50);
    setTimeout(applyContactFieldStyle, 250);
})();
</script>
HTML;

        if (str_contains($content, '</body>')) {
            $content = str_replace('</body>', $override."\n</body>", $content);
            $response->setContent($content);
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}
