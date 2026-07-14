<?php

namespace Tests\Unit;

use App\Support\PostSeoText;
use PHPUnit\Framework\TestCase;

class PostSeoTextTest extends TestCase
{
    public function test_it_builds_plain_bounded_metadata_from_html(): void
    {
        $title = PostSeoText::title(str_repeat('Uzun başlık ', 10));
        $description = PostSeoText::description('<p>Haberin <strong>açıklaması</strong> burada.</p>');

        $this->assertLessThanOrEqual(65, mb_strlen($title));
        $this->assertSame('Haberin açıklaması burada.', $description);
    }

    public function test_it_uses_the_first_non_empty_description_candidate(): void
    {
        $this->assertSame('İçerik metni', PostSeoText::description(' ', '<p>İçerik metni</p>', 'Başlık'));
    }
}
