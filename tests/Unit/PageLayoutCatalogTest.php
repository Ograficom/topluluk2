<?php

namespace Tests\Unit;

use App\Services\PageLayoutCatalog;
use PHPUnit\Framework\TestCase;

class PageLayoutCatalogTest extends TestCase
{
    public function test_catalog_lists_the_primary_existing_blade_pages(): void
    {
        $pages = PageLayoutCatalog::pageOptions();

        $this->assertArrayHasKey('home', $pages);
        $this->assertArrayHasKey('discover', $pages);
        $this->assertArrayHasKey('categories', $pages);
        $this->assertArrayHasKey('tags', $pages);
        $this->assertArrayHasKey('post_show', $pages);
        $this->assertArrayHasKey('profile', $pages);
    }

    public function test_layout_normalization_allows_only_known_unique_blocks(): void
    {
        $layout = PageLayoutCatalog::normalizeLayout('home', [
            'order' => ['post_feed', 'unknown_block', 'post_feed', 'read_filters'],
            'hidden' => ['unknown_block', 'feed_filters', 'feed_filters'],
        ]);

        $this->assertSame(['post_feed', 'read_filters', 'feed_filters'], $layout['order']);
        $this->assertSame(['feed_filters'], $layout['hidden']);
    }

    public function test_import_normalization_discards_unknown_page_keys(): void
    {
        $layouts = PageLayoutCatalog::normalizeAll([
            'home' => ['order' => ['post_feed']],
            'not_a_real_page' => ['order' => ['run_code']],
        ]);

        $this->assertSame(['home'], array_keys($layouts));
        $this->assertSame(['post_feed', 'feed_filters', 'read_filters'], $layouts['home']['order']);
    }

    public function test_all_catalog_blocks_have_a_server_defined_selector(): void
    {
        foreach (PageLayoutCatalog::definitions() as $pageKey => $definition) {
            $this->assertNotEmpty($definition['container'], $pageKey . ' should declare a container selector');

            foreach ($definition['blocks'] as $blockId => $block) {
                $this->assertNotEmpty($block['selector'], $pageKey . '.' . $blockId . ' should declare a block selector');
                $this->assertStringContainsString('data-ografi-layout-section', $block['selector']);
            }
        }
    }
}
