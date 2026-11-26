<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function createPost(User $author): Post
    {
        return Post::create([
            'user_id' => $author->id, 'title' => 'Silinecek yazı',
            'slug' => 'silinecek-yazi', 'excerpt' => 'Özet', 'body' => 'İçerik',
            'is_published' => true, 'published_at' => now()->subMinute(),
        ]);
    }

    public function test_only_owner_or_admin_can_delete_post(): void
    {
        $author = User::factory()->create();
        $post = $this->createPost($author);
        $url = route('posts.destroy', $post);
        $this->delete($url)->assertRedirect();
        $this->actingAs(User::factory()->create())->delete($url)->assertForbidden();
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
        $this->actingAs($author)->delete($url)->assertRedirect(route('posts.index'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_admin_can_delete_another_users_post(): void
    {
        $post = $this->createPost(User::factory()->create());
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->delete(route('posts.destroy', $post))->assertRedirect(route('posts.index'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
