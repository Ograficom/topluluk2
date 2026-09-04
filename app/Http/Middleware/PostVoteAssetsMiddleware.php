<?php

namespace App\Http\Middleware;

use App\Models\Post;
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

        $hasPostSurface = str_contains($html, 'data-post-card-shell') || str_contains($html, 'post-show-shell');
        $hasComposer = str_contains($html, 'id="post-create-form"') || str_contains($html, 'id="post-edit-form"');

        if (! $hasPostSurface && ! $hasComposer) {
            return $response;
        }

        if (str_contains($html, 'data-og-post-votes-assets')) {
            return $response;
        }

        $votesEnabled = true;
        if ($request->session()->hasOldInput('votes_enabled')) {
            $votesEnabled = (bool) $request->old('votes_enabled');
        } elseif ($request->routeIs('blog.post.edit')) {
            $routePost = $request->route('post');
            if ($routePost instanceof Post) {
                $votesEnabled = (bool) $routePost->votes_enabled;
            } else {
                $slug = trim((string) $routePost);
                if ($slug !== '') {
                    $votesEnabled = (bool) (Post::withoutGlobalScopes()->where('slug', $slug)->value('votes_enabled') ?? true);
                }
            }
        }

        $cssPath = public_path('css/post-votes.css');
        $jsPath = public_path('js/post-votes.js');
        $cssVersion = is_file($cssPath) ? (string) filemtime($cssPath) : '1';
        $jsVersion = is_file($jsPath) ? (string) filemtime($jsPath) : '1';

        $cssUrl = asset('css/post-votes.css') . '?v=' . rawurlencode($cssVersion);
        $jsUrl = asset('js/post-votes.js') . '?v=' . rawurlencode($jsVersion);

        $headAsset = '<link data-og-post-votes-assets rel="stylesheet" href="' . e($cssUrl) . '">' . "\n"
            . '<meta name="ografi-post-votes-enabled" content="' . ($votesEnabled ? '1' : '0') . '">';
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
