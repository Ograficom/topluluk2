<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use InvalidArgumentException;

class SvgSanitizer
{
    private const ALLOWED_ELEMENTS = [
        'svg',
        'g',
        'path',
        'rect',
        'circle',
        'ellipse',
        'line',
        'polyline',
        'polygon',
        'image',
        'symbol',
        'use',
        'defs',
        'linearGradient',
        'radialGradient',
        'stop',
        'clipPath',
        'mask',
        'title',
    ];

    private const DRAWING_ELEMENTS = [
        'path',
        'rect',
        'circle',
        'ellipse',
        'line',
        'polyline',
        'polygon',
        'image',
        'use',
    ];

    private const ALLOWED_ATTRIBUTES = [
        'xmlns',
        'viewBox',
        'width',
        'height',
        'preserveAspectRatio',
        'id',
        'href',
        'fill',
        'fill-rule',
        'fill-opacity',
        'stroke',
        'stroke-width',
        'stroke-linecap',
        'stroke-linejoin',
        'stroke-opacity',
        'opacity',
        'd',
        'x',
        'y',
        'x1',
        'y1',
        'x2',
        'y2',
        'cx',
        'cy',
        'r',
        'rx',
        'ry',
        'points',
        'transform',
        'clip-rule',
        'clip-path',
        'mask',
        'gradientUnits',
        'gradientTransform',
        'offset',
        'stop-color',
        'stop-opacity',
    ];

    public function sanitize(string $svg): string
    {
        if (strlen($svg) > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('SVG logo exceeds the maximum size.');
        }

        if (preg_match('/<!\s*(?:DOCTYPE|ENTITY)\b/i', $svg)) {
            throw new InvalidArgumentException('SVG logos cannot contain document type or entity declarations.');
        }

        $document = new DOMDocument;
        $document->resolveExternals = false;
        $document->substituteEntities = false;

        $previousErrorSetting = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadXML($svg, LIBXML_NONET | LIBXML_NOBLANKS);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorSetting);
        }

        if (
            ! $loaded
            || ! $document->documentElement
            || $document->documentElement->localName !== 'svg'
            || ! in_array($document->documentElement->namespaceURI, [null, 'http://www.w3.org/2000/svg'], true)
        ) {
            throw new InvalidArgumentException('The uploaded file is not a valid SVG.');
        }

        $this->sanitizeAttributes($document->documentElement);
        $this->sanitizeNode($document->documentElement);

        if (! $this->hasVisibleArtwork($document->documentElement)) {
            throw new InvalidArgumentException('SVG logo does not contain supported visible artwork.');
        }

        $document->documentElement->setAttribute('xmlns', 'http://www.w3.org/2000/svg');

        return $document->saveXML($document->documentElement) ?: throw new InvalidArgumentException('Unable to process the SVG logo.');
    }

    private function sanitizeNode(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                if (
                    ! in_array($child->localName, self::ALLOWED_ELEMENTS, true)
                    || ! in_array($child->namespaceURI, [null, 'http://www.w3.org/2000/svg'], true)
                ) {
                    $node->removeChild($child);

                    continue;
                }

                $this->sanitizeAttributes($child);

                if (
                    in_array($child->localName, ['image', 'use'], true)
                    && blank($child->getAttribute('href'))
                ) {
                    $node->removeChild($child);

                    continue;
                }

                $this->sanitizeNode($child);

                continue;
            }

            if (! $child instanceof DOMNode || $child->nodeType !== XML_TEXT_NODE) {
                $node->removeChild($child);
            }
        }
    }

    private function sanitizeAttributes(DOMElement $element): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = $attribute->localName;
            $value = trim($attribute->value);
            $isXlinkHref = $name === 'href'
                && $attribute->namespaceURI === 'http://www.w3.org/1999/xlink'
                && in_array($element->localName, ['image', 'use'], true);

            if (
                ($attribute->namespaceURI !== null && ! $isXlinkHref)
                || (! $isXlinkHref && ! in_array($name, self::ALLOWED_ATTRIBUTES, true))
                || preg_match('/[\x00-\x1F<>]/', $value)
                || (str_contains(strtolower($value), 'url(') && ! preg_match('/^url\(\s*#[A-Za-z][A-Za-z0-9_.:-]*\s*\)$/', $value))
                || in_array($name, ['clip-path', 'mask'], true) && ! preg_match('/^url\(\s*#[A-Za-z][A-Za-z0-9_.:-]*\s*\)$/', $value)
            ) {
                $element->removeAttributeNode($attribute);

                continue;
            }

            if ($name === 'href') {
                $isLocalReference = $element->localName === 'use'
                    && preg_match('/^#[A-Za-z][A-Za-z0-9_.:-]*$/', $value);
                $isEmbeddedRaster = $element->localName === 'image'
                    && preg_match('/^data:image\/(?:png|jpeg|webp);base64,[A-Za-z0-9+\/]+={0,2}$/', $value);

                if (! $isLocalReference && ! $isEmbeddedRaster) {
                    $element->removeAttributeNode($attribute);

                    continue;
                }

                if ($isXlinkHref) {
                    $element->removeAttributeNode($attribute);
                    $element->setAttribute('href', $value);
                }
            }

            if ($name === 'xmlns' && $value !== 'http://www.w3.org/2000/svg') {
                $element->removeAttributeNode($attribute);

                continue;
            }

            if ($name === 'id' && ! preg_match('/^[A-Za-z][A-Za-z0-9_.:-]*$/', $value)) {
                $element->removeAttributeNode($attribute);
            }
        }
    }

    private function hasVisibleArtwork(DOMElement $element): bool
    {
        foreach ($element->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            if (in_array($child->localName, ['defs', 'symbol', 'clipPath', 'mask'], true)) {
                continue;
            }

            if (in_array($child->localName, self::DRAWING_ELEMENTS, true)) {
                if ($child->localName === 'use' && ! preg_match('/^#[A-Za-z][A-Za-z0-9_.:-]*$/', $child->getAttribute('href'))) {
                    continue;
                }

                if ($child->localName === 'image' && ! preg_match('/^data:image\/(?:png|jpeg|webp);base64,[A-Za-z0-9+\/]+={0,2}$/', $child->getAttribute('href'))) {
                    continue;
                }

                return true;
            }

            if ($this->hasVisibleArtwork($child)) {
                return true;
            }
        }

        return false;
    }
}
