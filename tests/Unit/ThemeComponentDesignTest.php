<?php

namespace Tests\Unit;

use App\Services\ThemeComponentDesign;
use PHPUnit\Framework\TestCase;

class ThemeComponentDesignTest extends TestCase
{
    public function test_normalizer_rejects_invalid_colors_and_clamps_dimensions(): void
    {
        $styles = ThemeComponentDesign::normalize([
            'post_card' => [
                'background' => 'red; display:none',
                'radius' => 500,
                'padding' => -10,
            ],
        ]);

        $this->assertSame('#ffffff', $styles['post_card']['background']);
        $this->assertSame(32, $styles['post_card']['radius']);
        $this->assertSame(0, $styles['post_card']['padding']);
    }

    public function test_renderer_targets_the_existing_public_components(): void
    {
        $css = (new ThemeComponentDesign())->render(ThemeComponentDesign::defaults());

        $this->assertStringContainsString('article.post-card[data-post-card-shell]', $css);
        $this->assertStringContainsString('.post-show-shell .ps-post-card', $css);
        $this->assertStringContainsString('.ogx-comments-panel[data-ogx-comments]', $css);
        $this->assertStringNotContainsString('<script', $css);
    }

    public function test_footer_urls_reject_unsafe_schemes(): void
    {
        $styles = ThemeComponentDesign::normalize([
            'footer' => [
                'link_url' => 'javascript:alert(1)',
                'visible' => true,
            ],
        ]);

        $this->assertSame('', $styles['footer']['link_url']);
        $this->assertTrue($styles['footer']['visible']);
    }

    public function test_renderer_explicitly_applies_header_and_footer_visibility(): void
    {
        $styles = ThemeComponentDesign::defaults();
        $styles['header']['visible'] = false;
        $styles['footer']['visible'] = false;

        $css = (new ThemeComponentDesign())->render($styles);

        $this->assertStringContainsString('html body.alma-app .site-header:not(#comments):not(#app), html body.alma-app .site-header-shell:not(#comments):not(#app) { display: none !important; }', $css);
        $this->assertStringContainsString('.ografi-site-footer { display: none !important;', $css);

        $styles['header']['visible'] = true;
        $styles['footer']['visible'] = true;
        $css = (new ThemeComponentDesign())->render($styles);

        $this->assertStringNotContainsString('html body.alma-app .site-header:not(#comments):not(#app), html body.alma-app .site-header-shell:not(#comments):not(#app) { display: none !important; }', $css);
        $this->assertStringContainsString('.ografi-site-footer { display: block !important;', $css);
    }
}
