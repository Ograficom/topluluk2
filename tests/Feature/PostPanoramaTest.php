<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostPanoramaTest extends TestCase
{
    use RefreshDatabase;

    public function test_panorama_is_optional_and_can_be_disabled(): void
    {
        Storage::fake('public');
        $author = User::factory()->create();
        $channel = Channel::create(['name' => 'Gezi', 'slug' => 'gezi', 'is_active' => true]);
        $fields = ['title' => 'Panoramik manzara', 'excerpt' => 'Gezi özeti.', 'body' => 'Geniş manzaranın açıklaması.', 'channel_id' => $channel->id];
        $this->actingAs($author)->post(route('posts.store'), $fields + ['is_panorama' => '1'])->assertSessionHasErrors('cover_image');
        $this->post(route('posts.store'), $fields + [
            'is_panorama' => '1', 'cover_image' => UploadedFile::fake()->image('panorama.jpg', 1200, 300),
        ])->assertRedirect();
        $post = Post::query()->where('title', $fields['title'])->firstOrFail();
        $this->assertTrue($post->is_panorama);
        $this->get(route('posts.show', $post))->assertOk()->assertSee('data-ografi-panorama-candidate="explicit"', false);
        $this->get(route('posts.edit', $post))->assertOk()->assertDontSee('id="post-is-panorama"', false)->assertSee('data-photo360-capture', false);
        $this->patch(route('posts.update', $post), $fields)->assertRedirect();
        $this->assertTrue($post->refresh()->is_panorama);
        $this->patch(route('posts.update', $post), $fields + ['is_panorama' => '0'])->assertRedirect();
        $this->assertFalse($post->refresh()->is_panorama);
        $this->get(route('posts.show', $post))->assertOk()->assertDontSee('data-ografi-panorama-candidate="explicit"', false)->assertSee('data-ografi-panorama-candidate="auto"', false);
    }

    public function test_gallery_panorama_can_be_enabled_without_cover(): void
    {
        Storage::fake('public');
        $author = User::factory()->create();
        $channel = Channel::create(['name' => 'Gezi', 'slug' => 'gezi', 'is_active' => true]);
        $fields = ['title' => 'Yazı içi panorama', 'excerpt' => 'Gezi özeti.', 'body' => 'Manzara açıklaması.', 'channel_id' => $channel->id];
        $this->actingAs($author)->post(route('posts.store'), $fields + [
            'is_panorama' => '1', 'media' => [UploadedFile::fake()->image('panorama.jpg', 1200, 300)],
        ])->assertRedirect();
        $post = Post::query()->where('title', $fields['title'])->firstOrFail();
        $this->assertTrue($post->is_panorama);
        $this->assertNull($post->cover_image);
        $this->get(route('posts.show', $post))->assertOk()->assertSee('data-ografi-panorama-candidate="explicit"', false);
        $this->patch(route('posts.update', $post), $fields + ['is_panorama' => '1'])->assertRedirect();
        $this->assertTrue($post->refresh()->is_panorama);
    }
}
