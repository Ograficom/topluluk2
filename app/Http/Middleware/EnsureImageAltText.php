<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureImageAltText
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $contentType = strtolower((string) $response->headers->get('Content-Type'));

        if (!str_contains($contentType, 'text/html') || method_exists($response, 'getCallback')) {
            return $response;
        }

        $html = $response->getContent();

        if (!is_string($html) || $html === '' || stripos($html, '<img') === false) {
            return $response;
        }

        $pageTitle = $this->pageTitle($html);
        $updated = preg_replace_callback('/<img\b[^>]*>/i', function (array $match) use ($pageTitle): string {
            $tag = $match[0];

            if (preg_match('/\balt\s*=\s*(["\'])(.*?)\1/is', $tag, $altMatch) && trim(strip_tags(html_entity_decode($altMatch[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'))) !== '') {
                return $tag;
            }

            $alt = $this->imageAlt($tag, $pageTitle);
            $escaped = htmlspecialchars($alt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            if (preg_match('/\balt\s*=\s*(["\'])(.*?)\1/is', $tag)) {
                return preg_replace('/\balt\s*=\s*(["\'])(.*?)\1/is', 'alt="'.$escaped.'"', $tag, 1) ?? $tag;
            }

            if (preg_match('/\s*\/>$/', $tag)) {
                return preg_replace('/\s*\/>$/', ' alt="'.$escaped.'">', $tag, 1) ?? $tag;
            }

            return preg_replace('/>$/', ' alt="'.$escaped.'">', $tag, 1) ?? $tag;
        }, $html);

        if (is_string($updated) && $updated !== $html) {
            $response->setContent($updated);
            $response->headers->remove('Content-Length');
        }

        return $response;
    }

    private function pageTitle(string $html): string
    {
        if (preg_match('/<title\b[^>]*>(.*?)<\/title>/is', $html, $match)) {
            $title = $this->cleanText($match[1]);

            if ($title !== '') {
                return $title;
            }
        }

        return 'Ografi';
    }

    private function imageAlt(string $tag, string $pageTitle): string
    {
        foreach (['aria-label', 'title', 'data-alt', 'data-title'] as $attribute) {
            if (preg_match('/\b'.preg_quote($attribute, '/').'\s*=\s*(["\'])(.*?)\1/is', $tag, $match)) {
                $value = $this->cleanText($match[2]);

                if ($value !== '') {
                    return $this->withImageSuffix($value);
                }
            }
        }

        if (preg_match('/\b(?:src|data-src|data-original)\s*=\s*(["\'])(.*?)\1/is', $tag, $match)) {
            $path = (string) parse_url(html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'), PHP_URL_PATH);
            $filename = pathinfo($path, PATHINFO_FILENAME);

            if (preg_match('/(?:^|[-_])(logo|brand|favicon)(?:$|[-_])/i', $filename)) {
                return 'Ografi logosu';
            }

            $filename = preg_replace('/[-_]+/', ' ', $filename) ?? '';
            $filename = preg_replace('/\b(?:\d{2,}|img|image|photo|picture|thumb|thumbnail|preview|small|medium|large|webp|jpeg|jpg|png)\b/i', ' ', $filename) ?? '';
            $filename = trim(preg_replace('/\s+/', ' ', $filename) ?? '');

            if (mb_strlen($filename) >= 3 && !preg_match('/^[a-f0-9\s-]+$/i', $filename)) {
                return $this->withImageSuffix($filename);
            }
        }

        return $this->withImageSuffix($pageTitle);
    }

    private function cleanText(string $value): string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    private function withImageSuffix(string $value): string
    {
        $value = mb_substr($this->cleanText($value), 0, 140);

        if ($value === '') {
            return 'Ografi görseli';
        }

        return preg_match('/\b(?:görseli|fotoğrafı|logosu|avatarı|kapak resmi)$/iu', $value)
            ? $value
            : $value.' görseli';
    }
}
