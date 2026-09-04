<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnifiedStatsModalMiddleware
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
        if (! is_string($html) || $html === '') {
            return $response;
        }

        $hasFeedStats = str_contains($html, 'data-post-card-stats-modal');
        $hasShowStats = str_contains($html, 'data-show-stats-modal');

        if (! $hasFeedStats && ! $hasShowStats) {
            return $response;
        }

        if ($hasShowStats) {
            $html = str_replace(
                [
                    '<span>akıştaki izlenimler</span>',
                    '<span>paylaşımlar</span>',
                    '<span>gönderilere verilen tepkiler</span>',
                    '<span>yorumlar</span>',
                    '<span>yer işaretleri</span>',
                    ' etkileşim</strong>',
                ],
                [
                    '<span>Akışlardan Gösterim</span>',
                    '<span>Paylaşımlar</span>',
                    '<span>Tepkiler</span>',
                    '<span>Yorumlar</span>',
                    '<span>Kaydedildi</span>',
                    ' Etkileşim</strong>',
                ],
                $html
            );
        }

        if (! str_contains($html, 'data-og-unified-stats-modal')) {
            $assets = <<<'HTML'
<style data-og-unified-stats-modal>
/* Feed ve post-show istatistik pencerelerini tek bir görsel düzende tut. */
.post-show-shell .ps-show-stats-modal:not([hidden]) {
    position: fixed !important;
    inset: 0 !important;
    z-index: 999999999 !important;
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

.post-show-shell .ps-show-stats-backdrop {
    position: absolute !important;
    inset: 0 !important;
    background: rgba(0, 0, 0, .82) !important;
    -webkit-backdrop-filter: blur(14px) saturate(140%) !important;
    backdrop-filter: blur(14px) saturate(140%) !important;
}

.post-show-shell .ps-show-stats-panel {
    position: relative !important;
    inset: auto !important;
    z-index: 1 !important;
    width: min(520px, calc(100vw - 24px)) !important;
    min-height: 236px !important;
    max-height: calc(100dvh - 24px) !important;
    margin: 0 !important;
    padding: 20px 24px 26px !important;
    overflow-y: auto !important;
    border: 0 !important;
    border-radius: 12px !important;
    background: #ffffff !important;
    color: #111111 !important;
    box-shadow: none !important;
    transform: none !important;
}

.post-show-shell .ps-show-stats-head {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 16px !important;
    margin: 0 0 26px !important;
}

.post-show-shell .ps-show-stats-title {
    margin: 0 !important;
    color: #111111 !important;
    font-size: 20px !important;
    font-weight: 700 !important;
    line-height: 1.2 !important;
    letter-spacing: 0 !important;
}

.post-show-shell .ps-show-stats-grid {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    column-gap: 34px !important;
    row-gap: 46px !important;
}

.post-show-shell .ps-show-stats-item {
    min-width: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
}

.post-show-shell .ps-show-stats-item strong {
    display: block !important;
    margin: 0 0 3px !important;
    color: #111111 !important;
    font-size: 20px !important;
    font-weight: 700 !important;
    line-height: 1.1 !important;
}

.post-show-shell .ps-show-stats-item span {
    display: block !important;
    color: #666666 !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1.25 !important;
}

/* X butonu: normalde sade, hover/focus/tıklamada gri yüzey. */
.post-card__stats-close,
.post-show-shell .ps-show-stats-close {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 8px !important;
    background: transparent !important;
    color: #111111 !important;
    box-shadow: none !important;
    transition: background-color .15s ease, color .15s ease !important;
}

.post-card__stats-close:hover,
.post-card__stats-close:focus-visible,
.post-show-shell .ps-show-stats-close:hover,
.post-show-shell .ps-show-stats-close:focus-visible {
    background: #e4e4e4 !important;
    color: #111111 !important;
    outline: none !important;
}

.post-card__stats-close:active,
.post-show-shell .ps-show-stats-close:active {
    background: #d4d4d4 !important;
    color: #111111 !important;
    transform: scale(.96) !important;
}

.post-card__stats-close iconify-icon,
.post-show-shell .ps-show-stats-close svg {
    pointer-events: none !important;
}

html.dark .post-show-shell .ps-show-stats-panel,
body.dark .post-show-shell .ps-show-stats-panel,
.dark .post-show-shell .ps-show-stats-panel,
[data-theme="dark"] .post-show-shell .ps-show-stats-panel {
    background: #17181b !important;
    color: #f4f4f5 !important;
}

html.dark .post-show-shell .ps-show-stats-title,
html.dark .post-show-shell .ps-show-stats-item strong,
body.dark .post-show-shell .ps-show-stats-title,
body.dark .post-show-shell .ps-show-stats-item strong,
.dark .post-show-shell .ps-show-stats-title,
.dark .post-show-shell .ps-show-stats-item strong,
[data-theme="dark"] .post-show-shell .ps-show-stats-title,
[data-theme="dark"] .post-show-shell .ps-show-stats-item strong {
    color: #f4f4f5 !important;
}

html.dark .post-show-shell .ps-show-stats-item span,
body.dark .post-show-shell .ps-show-stats-item span,
.dark .post-show-shell .ps-show-stats-item span,
[data-theme="dark"] .post-show-shell .ps-show-stats-item span {
    color: #a1a1aa !important;
}

html.dark .post-card__stats-close,
html.dark .post-show-shell .ps-show-stats-close,
.dark .post-card__stats-close,
.dark .post-show-shell .ps-show-stats-close {
    color: #f4f4f5 !important;
}

html.dark .post-card__stats-close:hover,
html.dark .post-card__stats-close:focus-visible,
html.dark .post-show-shell .ps-show-stats-close:hover,
html.dark .post-show-shell .ps-show-stats-close:focus-visible,
.dark .post-card__stats-close:hover,
.dark .post-card__stats-close:focus-visible,
.dark .post-show-shell .ps-show-stats-close:hover,
.dark .post-show-shell .ps-show-stats-close:focus-visible {
    background: #2a2d32 !important;
    color: #f4f4f5 !important;
}

@media (max-width: 520px) {
    .post-show-shell .ps-show-stats-modal:not([hidden]) {
        padding: 10px !important;
    }

    .post-show-shell .ps-show-stats-panel {
        width: min(356px, calc(100vw - 20px)) !important;
        min-height: 240px !important;
        padding: 18px 20px 24px !important;
        border-radius: 12px !important;
    }

    .post-show-shell .ps-show-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        column-gap: 24px !important;
        row-gap: 28px !important;
    }
}

@supports not (height: 100dvh) {
    .post-show-shell .ps-show-stats-modal:not([hidden]) {
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
