<?php

return [
    'blocks' => [
        \VanOns\FilamentContentBuilder\Blocks\CtaBlock::class,
        \VanOns\FilamentContentBuilder\Blocks\ListBlock::class,
        \VanOns\FilamentContentBuilder\Blocks\TextBlock::class,
        \VanOns\FilamentContentBuilder\Blocks\EmbedBlock::class,
        \VanOns\FilamentContentBuilder\Blocks\Container::class,
        \App\ContentBlocks\CalloutBlock::class,
        \App\ContentBlocks\SourceBoxBlock::class,
        \App\ContentBlocks\QuoteBlock::class,
        \App\ContentBlocks\ImageGalleryBlock::class,
        \App\ContentBlocks\VideoEmbedBlock::class,
        \App\ContentBlocks\RelatedPostsBlock::class,
    ],

    'container-blocks' => [
        \VanOns\FilamentContentBuilder\Blocks\TextBlock::class,
        \App\ContentBlocks\CalloutBlock::class,
        \App\ContentBlocks\QuoteBlock::class,
    ],

    'defer_loading' => true,

    'embeddable_services' => [
        \BenSampo\Embed\Services\YouTube::class,
        \BenSampo\Embed\Services\Vimeo::class,
    ],

    'allowed_link_schemes' => ['http', 'https', 'mailto', 'tel'],

    'template_directories' => [
        app_path('/View/Templates'),
    ],

    'copy_paste' => true,

    'usage' => [
        'enabled' => false,
        'permission' => null,
        'cache' => 300,
        'sources' => [
            \App\Models\Post::class => [
                'columns' => ['builder_content'],
                'title_attribute' => 'title',
            ],
        ],
    ],
];
