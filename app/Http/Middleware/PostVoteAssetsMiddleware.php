<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostVoteAssetsMiddleware
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

        if (! str_contains($html, 'data-post-card-shell') && ! str_contains($html, 'post-show-shell')) {
            return $response;
        }

        if (str_contains($html, 'data-og-post-votes-assets')) {
            return $response;
        }

        $cssPath = public_path('css/post-votes.css');
        $jsPath = public_path('js/post-votes.js');
        $cssVersion = is_file($cssPath) ? (string) filemtime($cssPath) : '1';
        $jsVersion = is_file($jsPath) ? (string) filemtime($jsPath) : '1';

        $cssUrl = asset('css/post-votes.css') . '?v=' . rawurlencode($cssVersion);
        $jsUrl = asset('js/post-votes.js') . '?v=' . rawurlencode($jsVersion);

        $headAsset = '<link data-og-post-votes-assets rel="stylesheet" href="' . e($cssUrl) . '">';
        $bodyAsset = '<script data-og-post-votes-assets src="' . e($jsUrl) . '" defer></script>';

        if (stripos($html, '</head>') !== false) {
            $html = preg_replace('/<\/head>/i', $headAsset . "\n</head>", $html, 1) ?? $html;
        } else {
            $html = $headAsset . "\n" . $html;
        }

        if (stripos($html, '</body>') !== false) {
            $html = preg_replace('/<\/body>/i', $bodyAsset . "\n</body>", $html, 1) ?? $html;
        } else {
            $html .= "\n" . $bodyAsset;
        }

        $response->setContent($html);
        $response->headers->remove('Content-Length');

        return $response;
    }
}
