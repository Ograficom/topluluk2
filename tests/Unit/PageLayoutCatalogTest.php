<?php

namespace Tests\Unit;

use App\Services\PageLayoutCatalog;
use PHPUnit\Framework\TestCase;

class PageLayoutCatalogTest extends TestCase
{

    public function test_universal_layout_preserves_known_selectors_and_rejects_unsafe_selector_text(): void
    {
        $layout = PageLayoutCatalog::normalizeUniversalLayout([
            'containers' => [
                'body > main.layout-main' => [
                    'order' => [
                        'body > main.layout-main > section.page-header',
                        'body > main.layout-main > section.post-feed',
                        'body > main.layout-main > section.post-feed',
                        'body, html',
                    ],
                    'hidden' => [
                        'body > main.layout-main > section.post-feed',
                        'body, html',
                    ],
                ],
                'body > div[data-x="value"]' => [
                    'order' => ['body > div[data-x="value"] > section'],
                ],
            ],
        ]);

        $this->assertSame([
            'containers' => [
                'body > main.layout-main' => [
                    'order' => [
                        'body > main.layout-main > section.page-header',
                        'body > main.layout-main > section.post-feed',
                    ],
                    'hidden' => ['body > main.layout-main > section.post-feed'],
                ],
            ],
            'custom_blocks' => [],
        ], $layout);
    }

    public function test_normalize_all_keeps_universal_layouts_for_any_named_blade_route(): void
    {
        $universal = [
            'containers' => [
                'body > main.layout-main' => [
                    'order' => [
                        'body > main.layout-main > section.page-header',
                        'body > main.layout-main > section.post-feed',
                    ],
                    'hidden' => [],
                ],
            ],
            'custom_blocks' => [],
        ];

        $normalized = PageLayoutCatalog::normalizeAll([
            'blade:home' => $universal,
            'blade:blog.post' => $universal,
            'not_a_route' => ['containers' => []],
        ]);

        $this->assertArrayHasKey('blade:home', $normalized);
        $this->assertArrayHasKey('blade:blog.post', $normalized);
        $this->assertArrayNotHasKey('not_a_route', $normalized);
        $this->assertSame($universal, $normalized['blade:home']);
    }

    public function test_catalog_lists_the_primary_existing_blade_pages(): void
    {
        $pages = PageLayoutCatalog::pageOptions();

        $this->assertArrayHasKey('home', $pages);
        $this->assertArrayNotHasKey('discover', $pages);
        $this->assertArrayNotHasKey('faq', $pages);
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

    public function test_each_page_has_unique_block_ids_and_categories_empty_state_is_separate(): void
    {
        foreach (PageLayoutCatalog::definitions() as $pageKey => $definition) {
            $ids = array_keys($definition['blocks']);
            $this->assertSame($ids, array_values(array_unique($ids)), $pageKey . ' has duplicate block IDs');
        }

        $categories = PageLayoutCatalog::definition('categories')['blocks'];

        $this->assertArrayHasKey('categories_count', $categories);
        $this->assertArrayHasKey('categories_empty', $categories);
        $this->assertNotSame($categories['categories_count']['selector'], $categories['categories_empty']['selector']);
    }

    public function test_custom_blocks_are_allow_listed_and_included_in_normalized_order(): void
    {
        $customBlock = PageLayoutCatalog::defaultCustomBlock('custom_12345678-abcd-1234-abcd-123456789abc', 'text');
        $customBlock['text'] = '<script>alert(1)</script>Merhaba';
        $customBlock['url'] = 'javascript:alert(1)';
        $customBlock['image_url'] = '/\\\\evil.example/image.png';

        $layout = PageLayoutCatalog::normalizeLayout('home', [
            'order' => ['post_feed', $customBlock['id']],
            'hidden' => [$customBlock['id']],
            'custom_blocks' => [$customBlock],
        ]);

        $this->assertSame(['post_feed', $customBlock['id'], 'feed_filters', 'read_filters'], $layout['order']);
        $this->assertSame([$customBlock['id']], $layout['hidden']);
        $this->assertCount(1, $layout['custom_blocks']);
        $this->assertSame('Merhaba', $layout['custom_blocks'][0]['text']);
        $this->assertSame('', $layout['custom_blocks'][0]['url']);
        $this->assertSame('', $layout['custom_blocks'][0]['image_url']);
    }

    public function test_custom_block_type_and_id_cannot_be_used_to_inject_arbitrary_markup(): void
    {
        $blocks = PageLayoutCatalog::normalizeCustomBlocks([
            [
                'id' => 'custom_12345678-abcd-1234-abcd-123456789abc',
                'type' => 'raw_html',
                'title' => '<script>unsafe</script>Title',
                'text' => '<b>Safe text</b>',
            ],
            [
                'id' => 'fixed_post_feed',
                'type' => 'text',
                'text' => 'Not allowed',
            ],
        ]);

        $this->assertSame([], $blocks);
    }

    public function test_universal_layout_normalization_rejects_unsafe_selectors_and_duplicate_order_entries(): void
    {
        $parent = 'body > main.layout-main';
        $first = 'body > main.layout-main > section:nth-of-type(1)';
        $second = 'body > main.layout-main > section:nth-of-type(2)';

        $layout = PageLayoutCatalog::normalizeUniversalLayout([
            'containers' => [
                $parent => [
                    'order' => [$first, $second, $first, 'body > main, body'],
                    'hidden' => [$second, 'body > main > aside'],
                ],
                'body > main;script' => [
                    'order' => [$first],
                    'hidden' => [],
                ],
            ],
        ]);

        $this->assertSame([
            'containers' => [
                $parent => [
                    'order' => [$first, $second],
                    'hidden' => [$second],
                ],
            ],
            'custom_blocks' => [],
        ], $layout);
    }

}
