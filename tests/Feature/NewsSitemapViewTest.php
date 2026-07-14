<?php

namespace Tests\Feature;

use DOMDocument;
use Tests\TestCase;

class NewsSitemapViewTest extends TestCase
{
    public function test_news_sitemap_renders_valid_xml_with_escaped_titles(): void
    {
        $xml = view('xml.news-sitemap', ['items' => [[
            'loc' => 'https://ografi.com/haber',
            'publication_name' => 'Ografi',
            'language' => 'tr',
            'publication_date' => now()->toAtomString(),
            'title' => 'Örnek & haber',
        ]]])->render();

        $document = new DOMDocument();
        $this->assertTrue($document->loadXML($xml));
        $this->assertStringContainsString('<news:name>Ografi</news:name>', $xml);
        $this->assertStringContainsString('Örnek &amp; haber', $xml);
    }
}
