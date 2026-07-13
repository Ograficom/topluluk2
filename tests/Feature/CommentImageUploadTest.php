<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommentImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_can_be_created_with_only_an_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $post = $this->createVisiblePost($user);

        $this
            ->actingAs($user)
            ->post(route('blog.post.comment', $post), [
                'image' => UploadedFile::fake()->image('comment.jpg'),
            ])
            ->assertRedirect();

        $comment = Comment::query()->sole();
        $path = $this->imagePathFrom($comment);

        $this->assertStringStartsWith('comment-images/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_reply_image_is_stored_with_the_reply(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $post = $this->createVisiblePost($user);
        $parent = $post->comments()->create([
            'user_id' => $user->id,
            'content' => 'Ana yorum',
        ]);

        $this
            ->actingAs($user)
            ->post(route('blog.post.comment', $post), [
                'parent_id' => $parent->id,
                'content' => 'Resimli cevap',
                'image' => UploadedFile::fake()->image('reply.png'),
            ])
            ->assertRedirect();

        $reply = Comment::query()->where('parent_id', $parent->id)->sole();
        Storage::disk('public')->assertExists($this->imagePathFrom($reply));
    }

    public function test_comment_image_is_deleted_when_comment_is_deleted(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $post = $this->createVisiblePost($user);

        $this
            ->actingAs($user)
            ->post(route('blog.post.comment', $post), [
                'content' => 'Silinecek resim',
                'image' => UploadedFile::fake()->image('delete-me.jpg'),
            ])
            ->assertRedirect();

        $comment = Comment::query()->sole();
        $path = $this->imagePathFrom($comment);

        $this
            ->actingAs($user)
            ->delete(route('blog.comment.delete', $comment))
            ->assertRedirect();

        Storage::disk('public')->assertMissing($path);
    }

    public function test_removed_comment_image_is_deleted_when_comment_is_updated(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $post = $this->createVisiblePost($user);

        $this
            ->actingAs($user)
            ->post(route('blog.post.comment', $post), [
                'content' => 'Resimli yorum',
                'image' => UploadedFile::fake()->image('remove-on-edit.jpg'),
            ])
            ->assertRedirect();

        $comment = Comment::query()->sole();
        $path = $this->imagePathFrom($comment);

        $this
            ->actingAs($user)
            ->put(route('blog.comment.update', $comment), [
                'content' => 'Artik resimsiz yorum',
            ])
            ->assertRedirect();

        Storage::disk('public')->assertMissing($path);
    }

    private function imagePathFrom(Comment $comment): string
    {
        preg_match('/\[img:([^\]\s]+)\]/', (string) $comment->content, $matches);

        return (string) ($matches[1] ?? '');
    }

    private function createVisiblePost(User $author): Post
    {
        return Post::create([
            'author_id' => $author->id,
            'title' => 'Resimli yorum gonderisi',
            'slug' => 'resimli-yorum-gonderisi-' . $author->id,
            'content' => '<p>icerik</p>',
            'is_published' => true,
            'published_at' => now()->subMinute(),
            'comments_disabled' => false,
        ]);
    }
}
