<?php

namespace Tests\Feature;

use App\ContentBlocks\CalloutBlock;
use App\ContentBlocks\ImageGalleryBlock;
use App\ContentBlocks\QuoteBlock;
use App\ContentBlocks\RelatedPostsBlock;
use App\ContentBlocks\SourceBoxBlock;
use App\ContentBlocks\VideoEmbedBlock;
use VanOns\FilamentContentBuilder\FilamentContentBuilder;
use Tests\TestCase;

class OgrafiContentBuilderTest extends TestCase
{
    public function test_ografi_blocks_are_registered_with_the_van_ons_builder(): void
    {
        $registered = collect(FilamentContentBuilder::getBlocks())
            ->map(fn ($block) => $block::class)
            ->all();

        $this->assertContains(CalloutBlock::class, $registered);
        $this->assertContains(ImageGalleryBlock::class, $registered);
        $this->assertContains(QuoteBlock::class, $registered);
        $this->assertContains(RelatedPostsBlock::class, $registered);
        $this->assertContains(SourceBoxBlock::class, $registered);
        $this->assertContains(VideoEmbedBlock::class, $registered);
    }

    public function test_custom_block_templates_escape_user_supplied_text(): void
    {
        $root = resource_path('views/blocks');
        $templates = [
            'callout-block.blade.php',
            'source-box-block.blade.php',
            'quote-block.blade.php',
        ];

        foreach ($templates as $template) {
            $this->assertFileExists($root . DIRECTORY_SEPARATOR . $template);
            $contents = file_get_contents($root . DIRECTORY_SEPARATOR . $template);
            $this->assertIsString($contents);
            $this->assertStringContainsString('{{', $contents);
        }
    }
}
