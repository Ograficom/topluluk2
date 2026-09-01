<?php

namespace Tests\Unit;

use App\Models\Post;
use PHPUnit\Framework\TestCase;

class PostTitleSanitizationTest extends TestCase
{
    public function test_existing_html_tags_are_removed_when_title_is_read(): void
    {
        $post = new Post();
        $post->setRawAttributes([
            'title' => '&lt;b&gt;Philip Morris&lt;/b&gt;',
        ]);

        $this->assertSame('Philip Morris', $post->title);
    }

    public function test_html_tags_are_removed_before_title_is_stored(): void
    {
        $post = new Post([
            'title' => '<b>Philip Morris</b>',
        ]);

        $this->assertSame('Philip Morris', $post->title);
        $this->assertSame('Philip Morris', $post->getAttributes()['title']);
    }
}
