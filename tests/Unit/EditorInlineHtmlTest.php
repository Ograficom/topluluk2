<?php

namespace Tests\Unit;

use App\Support\EditorInlineHtml;
use PHPUnit\Framework\TestCase;

class EditorInlineHtmlTest extends TestCase
{
    public function test_formats_editor_text_without_printing_markup(): void
    {
        $html = EditorInlineHtml::render('<b>Türkler</b> <i>italik</i><br><mark class="cdx-marker">vurgu</mark> &amp; &lt;3');
        $this->assertSame('<b>Türkler</b> <i>italik</i><br><mark class="cdx-marker">vurgu</mark> &amp; &lt;3', $html);
    }

    public function test_untrusted_attributes_and_active_elements_never_render(): void
    {
        $html = EditorInlineHtml::render('<b onclick="alert(1)">Kalın</b><script>alert(2)</script><img src=x onerror=alert(3)><a href="java&#x73;cript:alert(4)">Kötü</a><svg onload="alert(5)"></svg><a href="https://ografi.com" target="_blank">Site</a>');
        $this->assertSame('<b>Kalın</b>Kötü<a href="https://ografi.com" rel="nofollow ugc noopener noreferrer">Site</a>', $html);
        $this->assertSame('Unsafe', EditorInlineHtml::render('<a href="https://example.com\@evil.test">Unsafe</a>'));
    }

    public function test_code_examples_and_entity_text_are_not_executed(): void
    {
        $this->assertSame('<code class="inline-code">&lt;script&gt;</code>', EditorInlineHtml::render('<code>&lt;script&gt;</code>'));
        $this->assertSame('A<br>'."\n".'B', EditorInlineHtml::render("A\nB"));
    }
}
