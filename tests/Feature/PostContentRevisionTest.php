<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostContentRevision;
use App\Services\O grafiContentRevisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostContentRevisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_edits_create_a_revision_of_the_previous_state(): void
    {
        $post = Post::factory()->create([
            'title' => 'İlk başlık',
            'content' => '<p>İlk içerik</p>',
            'content_json' => ['blocks' => []],
        ]);

        $post->title = 'Yeni başlık';
        $post->content = '<p>Yeni içerik</p>';
        $post->save();

        $revision = PostContentRevision::query()->where('post_id', $post->id)->latest('id')->first();

        $this->assertNotNull($revision);
        $this->assertSame('İlk başlık', $revision->title);
        $this->assertSame('<p>İlk içerik</p>', $revision->content);
    }

    public function test_revision_cannot_be_restored_to_a_different_post(): void
    {
        $first = Post::factory()->create();
        $second = Post::factory()->create();
        $revision = PostContentRevision::query()->create([
            'post_id' => $first->id,
            'revision_type' => 'content',
            'title' => 'Önceki başlık',
            'created_at' => now(),
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        app(\App\Services\OgrafiContentRevisionService::class)->restore($second, $revision);
    }
}
