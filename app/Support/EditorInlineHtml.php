<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

final class EditorInlineHtml
{
    private const TAGS = [
        'b', 'strong', 'i', 'em', 'u', 's', 'strike', 'del', 'mark', 'code',
        'sub', 'sup', 'br', 'a',
    ];

    private const DROP_SUBTREE = [
        'script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template',
        'noscript', 'textarea', 'title', 'xmp', 'plaintext', 'head',
    ];

    public static function render(mixed $value): string
    {
        if (! is_string($value) || $value === '') return '';

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $loaded = $document->loadHTML(
                '<!doctype html><html><head><meta charset="UTF-8"></head><body>'.$value.'</body></html>',
                LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $body = $document->getElementsByTagName('body')->item(0);
        if (! $loaded || ! $body) return self::escape($value);

        return self::children($body);
    }

    private static function children(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMText) {
                $html .= nl2br(self::escape($child->nodeValue ?? ''), false);
                continue;
            }

            if (! $child instanceof DOMElement) continue;
            $tag = strtolower($child->tagName);
            if (in_array($tag, self::DROP_SUBTREE, true)) continue;
            if ($tag === 'br') {
                $html .= '<br>';
                continue;
            }

            $content = self::children($child);
            if (! in_array($tag, self::TAGS, true)) {
                $html .= $content;
                if (in_array($tag, ['p', 'div', 'li'], true)) $html .= '<br>';
                continue;
            }

            $attributes = '';
            if ($tag === 'a') {
                $href = trim($child->getAttribute('href'));
                if (! self::safeHref($href)) {
                    $html .= $content;
                    continue;
                }
                $attributes = ' href="'.self::escape($href).'" rel="nofollow ugc noopener noreferrer"';
            } elseif ($tag === 'mark') {
                $attributes = ' class="cdx-marker"';
            } elseif ($tag === 'code') {
                $attributes = ' class="inline-code"';
            }

            $html .= '<'.$tag.$attributes.'>'.$content.'</'.$tag.'>';
        }

        return $html;
    }

    private static function safeHref(string $href): bool
    {
        if ($href === '' || preg_match('/[\x00-\x20\x7f\\\\]/', $href)) return false;
        if (str_starts_with($href, '#')) return true;
        if (str_starts_with($href, '/') && ! str_starts_with($href, '//')) return true;

        $parts = parse_url($href);
        return is_array($parts)
            && in_array(strtolower($parts['scheme'] ?? ''), ['https', 'http'], true)
            && ! empty($parts['host']);
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
