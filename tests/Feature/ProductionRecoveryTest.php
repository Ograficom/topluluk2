<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_and_signed_in_pages_render_without_server_errors(): void
    {
        $author = User::factory()->create(['username' => null]);
        $viewer = User::factory()->create();
        $channel = Channel::create(['name' => 'Recovery', 'slug' => 'recovery', 'is_active' => true]);
        $post = Post::create([
            'user_id' => $author->id, 'channel_id' => $channel->id,
            'title' => 'Recovery post', 'slug' => 'recovery-post', 'body' => 'Recovery body',
            'is_published' => true, 'published_at' => now()->subMinute(),
        ]);

        $this->get('/')->assertOk()->assertSee('Recovery post');
        $this->get(route('posts.show', $post))->assertOk()->assertSee('data-post-statistics-open', false);
        $this->get(route('profiles.show', $author))->assertOk();
        $this->actingAs($viewer)->get('/')->assertOk()->assertSee('data-notification-dot', false);
        $this->get(route('posts.show', $post))->assertOk()->assertSee('id="notificationMenu"', false);
        $this->get('/settings')->assertOk();
        $this->getJson(route('notifications.feed'))->assertOk()->assertJsonPath('unread', 0);
    }
}
