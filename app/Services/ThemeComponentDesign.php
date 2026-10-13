<?php

namespace App\Services;

/**
 * Validates component design data and renders the small, allow-listed set of
 * CSS rules used by the public theme. Arbitrary CSS is deliberately not accepted
 * here; unrestricted CSS remains in the existing administrator-only theme field.
 */
final class ThemeComponentDesign
{
    public static function defaults(): array
    {
        return [
            'header' => [
                'visible' => true,
                'background' => '#ffffff',
                'text_color' => '#111827',
                'border_color' => '#e5e7eb',
                'height' => 64,
                'padding_x' => 20,
            ],
            'home' => [
                'max_width' => 1280,
                'gap' => 16,
                'show_sidebars' => true,
            ],
            'post_card' => [
                'background' => '#ffffff',
                'text_color' => '#111827',
                'border_color' => '#e5e7eb',
                'radius' => 12,
                'padding' => 16,
                'title_size' => 18,
                'body_size' => 14,
            ],
            'post_show' => [
                'background' => '#ffffff',
                'text_color' => '#1f2937',
                'border_color' => '#e5e7eb',
                'radius' => 12,
                'padding' => 20,
                'max_width' => 820,
                'title_size' => 28,
            ],
            'comments' => [
                'background' => '#ffffff',
                'text_color' => '#1f2937',
                'border_color' => '#e5e7eb',
                'radius' => 12,
                'padding' => 12,
            ],
            'footer' => [
                'visible' => false,
                'background' => '#ffffff',
                'text_color' => '#6b7280',
                'border_color' => '#e5e7eb',
                'padding' => 20,
                'text' => '',
                'link_label' => '',
                'link_url' => '',
            ],
        ];
    }

    public static function normalize(mixed $value): array
    {
        $input = is_array($value) ? $value : [];
        $normalized = self::defaults();

        foreach ($normalized as $section => $fields) {
            $sectionInput = is_array($input[$section] ?? null) ? $input[$section] : [];

            foreach ($fields as $key => $default) {
                if (!array_key_exists($key, $sectionInput)) {
                    continue;
                }

                $candidate = $sectionInput[$key];

                if (is_bool($default)) {
                    $normalized[$section][$key] = self::boolean($candidate, $default);
                    continue;
                }

                if (is_int($default)) {
                    $bounds = self::bounds($section, $key);
                    $number = is_numeric($candidate) ? (int) $candidate : $default;
                    $normalized[$section][$key] = max($bounds[0], min($bounds[1], $number));
                    continue;
                }

                if ($key === 'background' || str_ends_with($key, '_color')) {
                    $normalized[$section][$key] = self::color($candidate, $default);
                    continue;
                }

                if ($key === 'link_url') {
                    $normalized[$section][$key] = self::safeUrl($candidate);
                    continue;
                }

                if (is_string($default)) {
                    $text = trim(strip_tags(is_scalar($candidate) ? (string) $candidate : ''));
                    $normalized[$section][$key] = mb_substr($text, 0, $key === 'text' ? 180 : 100);
                }
            }
        }

        return $normalized;
    }

    public function render(mixed $value): string
    {
        $styles = self::normalize($value);
        $css = [];
        $header = $styles['header'];
        $home = $styles['home'];
        $card = $styles['post_card'];
        $post = $styles['post_show'];
        $comments = $styles['comments'];
        $footer = $styles['footer'];

        if (!$header['visible']) {
            $css[] = 'html body.alma-app .site-header:not(#comments):not(#app), html body.alma-app .site-header-shell:not(#comments):not(#app) { display: none !important; }';
        } else {
            $css[] = sprintf(
                'html body.alma-app .site-header:not(#comments):not(#app), html body.alma-app .site-header-shell:not(#comments):not(#app) { background-color: %s !important; color: %s !important; } html body.alma-app .site-header-shell:not(#comments):not(#app) { min-height: %dpx !important; padding-inline: %dpx !important; } html body.alma-app .site-header:not(#comments):not(#app) { border-bottom: 1px solid %s !important; }',
                $header['background'],
                $header['text_color'],
                $header['height'],
                $header['padding_x'],
                $header['border_color'],
            );
        }

        $css[] = sprintf(
            'html body.alma-app.route-home .main-grid, html body.alma-app.route-discover .main-grid { max-width: %dpx !important; column-gap: %dpx !important; }',
            $home['max_width'],
            $home['gap'],
        );

        if (!$home['show_sidebars']) {
            $css[] = 'html body.alma-app.route-home .main-grid > .layout-side, html body.alma-app.route-discover .main-grid > .layout-side { display: none !important; } html body.alma-app.route-home .main-grid, html body.alma-app.route-discover .main-grid { grid-template-columns: minmax(0, 1fr) !important; }';
        }

        $css[] = sprintf(
            'html body.alma-app article.post-card[data-post-card-shell]:not(#comments):not(#app) { box-sizing: border-box !important; background-color: %s !important; color: %s !important; border: 1px solid %s !important; border-radius: %dpx !important; padding: %dpx !important; } html body.alma-app article.post-card[data-post-card-shell]:not(#comments):not(#app) :is(h1,h2,h3,[data-post-card-title]) { color: %s !important; font-size: %dpx !important; } html body.alma-app article.post-card[data-post-card-shell]:not(#comments):not(#app) :is(p,[data-post-card-excerpt]) { color: %s !important; font-size: %dpx !important; }',
            $card['background'],
            $card['text_color'],
            $card['border_color'],
            $card['radius'],
            $card['padding'],
            $card['text_color'],
            $card['title_size'],
            $card['text_color'],
            $card['body_size'],
        );

        $css[] = sprintf(
            'html body.alma-app .post-show-shell .ps-post-card:not(#comments):not(#app) { background-color: %s !important; color: %s !important; border: 1px solid %s !important; border-radius: %dpx !important; max-width: %dpx !important; margin-inline: auto !important; } html body.alma-app .post-show-shell .ps-post-card-inner { padding: %dpx !important; } html body.alma-app .post-show-shell .ps-post-title { color: %s !important; font-size: %dpx !important; } html body.alma-app .post-show-shell .ps-post-body { color: %s !important; }',
            $post['background'],
            $post['text_color'],
            $post['border_color'],
            $post['radius'],
            $post['max_width'],
            $post['padding'],
            $post['text_color'],
            $post['title_size'],
            $post['text_color'],
        );

        $css[] = sprintf(
            'html body.alma-app .post-show-shell .ogx-comments-panel[data-ogx-comments], html body.alma-app .post-show-shell .ogx-comment[data-ogx-comment] { background-color: %s !important; color: %s !important; border: 1px solid %s !important; border-radius: %dpx !important; } html body.alma-app .post-show-shell .ogx-comments-panel[data-ogx-comments] { padding: %dpx !important; } html body.alma-app .post-show-shell .ogx-comments-panel[data-ogx-comments] :is(.ogx-comment-text,.ogx-username,.ogx-comment-count) { color: %s !important; }
            $comments['background'],
            $comments['text_color'],
            $comments['border_color'],
            $comments['radius'],
            $comments['padding'],
            $comments['text_color'],
        );

        if ($footer['visible']) {
            $css[] = sprintf(
                'html body.alma-app .ografi-site-footer { display: block !important; background-color: %s !important; color: %s !important; border-top: 1px solid %s !important; padding: %dpx 20px !important; } html body.alma-app .ografi-site-footer a { color: inherit !important; text-decoration: underline; text-underline-offset: 3px; }',
                $footer['background'],
                $footer['text_color'],
                $footer['border_color'],
                $footer['padding'],
            );
        }

        return implode("\n", $css);
    }

    private static function bounds(string $section, string $key): array
    {
        return match ($section . '.' . $key) {
            'header.height' => [48, 112],
            'header.padding_x' => [0, 48],
            'home.max_width' => [960, 1920],
            'home.gap' => [0, 48],
            'post_card.radius' => [0, 32],
            'post_card.padding' => [0, 40],
            'post_card.title_size' => [14, 32],
            'post_card.body_size' => [12, 20],
            'post_show.radius' => [0, 32],
            'post_show.padding' => [0, 48],
            'post_show.max_width' => [640, 1200],
            'post_show.title_size' => [18, 48],
            'comments.radius' => [0, 28],
            'comments.padding' => [4, 32],
            'footer.padding' => [8, 64],
            default => [0, 100],
        };
    }

    private static function color(mixed $value, string $fallback): string
    {
        $candidate = trim(is_scalar($value) ? (string) $value : '');

        return preg_match('/^#[0-9a-fA-F]{6}$/', $candidate) === 1
            ? strtolower($candidate)
            : $fallback;
    }

    private static function boolean(mixed $value, bool $fallback): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === 1 || $value === '1' || $value === 'true') {
            return true;
        }

        if ($value === 0 || $value === '0' || $value === 'false') {
            return false;
        }

        return $fallback;
    }

    private static function safeUrl(mixed $value): string
    {
        $url = trim(is_scalar($value) ? (string) $value : '');

        if ($url === '' || str_starts_with($url, '#')) {
            return $url;
        }

        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);

        return strtolower((string) $scheme) === 'https' && is_string($host) && $host !== ''
            ? $url
            : '';
    }
}
