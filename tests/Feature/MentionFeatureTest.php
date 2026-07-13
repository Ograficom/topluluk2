<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Notifications\UserMentionedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MentionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_post_mentions_send_a_notification(): void
    {
        Notification::fake();

        $author = User::factory()->create([
            'name' => 'Post Author',
            'username' => 'post-author',
        ]);

        $mentioned = User::factory()->create([
            'name' => 'Mentioned User',
            'username' => 'mentioned-user',
        ]);

        $response = $this->actingAs($author)->post(route('blog.store'), [
            'title' => 'Mention test post',
            'content' => '<p>Merhaba @mentioned-user</p>',
            'is_published' => 1,
            'comments_disabled' => 0,
            'is_nsfw' => 0,
        ]);

        $response->assertRedirect();

        Notification::assertSentTo($mentioned, UserMentionedNotification::class);
        Notification::assertNotSentTo($author, UserMentionedNotification::class);
    }

    public function test_comment_mentions_send_a_notification(): void
    {
        Notification::fake();

        $postAuthor = User::factory()->create([
            'name' => 'Writer',
            'username' => 'writer-user',
        ]);

        $commenter = User::factory()->create([
            'name' => 'Commenter',
            'username' => 'commenter-user',
        ]);

        $mentioned = User::factory()->create([
            'name' => 'Mention Target',
            'username' => 'target-user',
        ]);

        $post = Post::create([
            'author_id' => $postAuthor->id,
            'title' => 'Visible post',
            'slug' => 'visible-post',
            'content' => '<p>icerik</p>',
            'is_published' => true,
            'published_at' => now()->subMinute(),
        ]);

        $response = $this->actingAs($commenter)->post(route('blog.post.comment', $post), [
            'content' => 'Selam @target-user',
        ]);

        $response->assertRedirect();

        Notification::assertSentTo($mentioned, UserMentionedNotification::class);
        Notification::assertNotSentTo($commenter, UserMentionedNotification::class);
    }

    public function test_mentions_endpoint_returns_matching_users(): void
    {
        $viewer = User::factory()->create([
            'name' => 'Viewer',
            'username' => 'viewer-user',
        ]);

        User::factory()->create([
            'name' => 'Alpha Person',
            'username' => 'alpha-one',
        ]);

        User::factory()->create([
            'name' => 'Beta Person',
            'username' => 'beta-two',
        ]);

        $response = $this->actingAs($viewer)->getJson(route('mentions.users', ['q' => 'alp']));

        $response
            ->assertOk()
            ->assertJsonPath('data.0.username', 'alpha-one');
    }
}
