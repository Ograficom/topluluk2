<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\CommentBlockedWord;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocked_words_are_censored_when_creating_comment(): void
    {
        CommentBlockedWord::create([
            'word' => 'yasakli ifade',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $post = $this->createVisiblePost($user);

        $response = $this->actingAs($user)->post(route('blog.post.comment', $post), [
            'content' => 'Bu yorumda yasakli ifade var.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'content' => 'Bu yorumda ******* ***** var.',
        ]);
    }

    public function test_inactive_blocked_words_do_not_prevent_comment_creation(): void
    {
        CommentBlockedWord::create([
            'word' => 'pasif ifade',
            'is_active' => false,
        ]);

        $user = User::factory()->create();
        $post = $this->createVisiblePost($user);

        $response = $this->actingAs($user)->post(route('blog.post.comment', $post), [
            'content' => 'Bu yorumda pasif ifade var.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('comments', 1);
    }

    public function test_blocked_words_are_censored_when_updating_comment(): void
    {
        CommentBlockedWord::create([
            'word' => 'engelli',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $post = $this->createVisiblePost($user);
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'author_name' => $user->name,
            'author_email' => $user->email,
            'content' => 'Temiz yorum.',
            'is_approved' => true,
        ]);

        $response = $this->actingAs($user)->put(route('blog.comment.update', $comment), [
            'content' => 'Bu yorum engelli kelime iceriyor.',
        ]);

        $response->assertRedirect();
        $this->assertSame('Bu yorum ******* kelime iceriyor.', $comment->fresh()->content);
    }

    private function createVisiblePost(User $author): Post
    {
        return Post::create([
            'author_id' => $author->id,
            'title' => 'Gorunur gonderi',
            'slug' => 'gorunur-gonderi-' . $author->id,
            'content' => '<p>icerik</p>',
            'is_published' => true,
            'published_at' => now()->subMinute(),
            'comments_disabled' => false,
        ]);
    }
}
