<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostCardStatsPresentationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! method_exists($response, 'getContent')) {
            return $response;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));
        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html) || $html === '' || ! str_contains($html, 'data-post-card-stats-modal')) {
            return $response;
        }

        // Post-card istatistik kutusundaki metinleri daha kisa ve anlasilir hale getir.
        $html = str_replace(
            [
                '<span>akışlardaki izlenimler</span>',
                '<span>ilanları</span>',
                '<span>gönderilere verilen tepkiler</span>',
                '<span>yorumlar</span>',
                '<span>yer işaretleri</span>',
                '</span> etkileşim</strong>',
                "label.textContent = 'Tepki';",
            ],
            [
                '<span>Akışlardan Gösterim</span>',
                '<span>Görüntülenen</span>',
                '<span>Tepkiler</span>',
                '<span>Yorumlar</span>',
                '<span>Kaydedildi</span>',
                '</span> Etkileşim</strong>',
                "label.textContent = 'Tepkiler';",
            ],
            $html
        );

        if (! str_contains($html, 'data-og-post-stats-presentation')) {
            $assets = <<<'HTML'
<style data-og-post-stats-presentation>
/* Istatistik dialogunu tarayici viewport'unun matematiksel merkezine kilitle. */
dialog.post-card__stats-modal[open] {
    position: fixed !important;
    inset: 0 !important;
    display: grid !important;
    place-items: center !important;
    width: 100vw !important;
    height: 100dvh !important;
    min-width: 100vw !important;
    min-height: 100dvh !important;
    margin: 0 !important;
    padding: 12px !important;
    box-sizing: border-box !important;
}

dialog.post-card__stats-modal[open] > .post-card__stats-panel {
    position: relative !important;
    top: auto !important;
    right: auto !important;
    bottom: auto !important;
    left: auto !important;
    margin: 0 !important;
    transform: none !important;
}

@supports not (height: 100dvh) {
    dialog.post-card__stats-modal[open] {
        height: 100vh !important;
        min-height: 100vh !important;
    }
}
</style>
HTML;

            if (stripos($html, '</body>') !== false) {
                $html = preg_replace('/<\/body>/i', $assets . "\n</body>", $html, 1) ?? ($html . $assets);
            } else {
                $html .= $assets;
            }
        }

        $response->setContent($html);
        $response->headers->remove('Content-Length');

        return $response;
    }
}
