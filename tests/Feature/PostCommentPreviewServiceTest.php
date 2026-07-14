<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Services\PostCommentPreviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCommentPreviewServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_attaches_three_recent_commenter_avatars_and_the_remaining_count(): void
    {
        $post = Post::query()->create([
            'title' => 'Avatar grubu',
            'slug' => 'avatar-grubu',
            'content' => 'Test',
            'is_published' => true,
        ]);

        $users = User::factory()->count(6)->create();

        foreach ($users as $index => $user) {
            Comment::query()->create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'content' => 'Yorum '.($index + 1),
                'is_approved' => true,
            ]);
        }

        app(PostCommentPreviewService::class)->attachToPosts(collect([$post]));

        $this->assertCount(3, $post->commenter_previews);
        $this->assertSame(3, $post->commenter_preview_extra_count);
        $this->assertSame($users->last()->id, $post->commenter_previews[0]['id']);
    }
}
