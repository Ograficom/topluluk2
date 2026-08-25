<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\ReactionType;
use App\Models\User;
use App\Services\AiCommunityActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiCommunityActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_transparent_bot_interactions_only_once_per_post(): void
    {
        $author = User::factory()->create();
        $post = Post::query()->create([
            'author_id' => $author->id,
            'title' => 'Toplulukta yeni fikirler',
            'slug' => 'toplulukta-yeni-fikirler',
            'content' => 'İçerik metni.',
            'is_published' => true,
            'published_at' => now()->subMinutes(20),
            'comments_disabled' => false,
        ]);
        ReactionType::query()->firstOrCreate(['short_code' => 'heart'], [
            'label' => 'Heart',
            'emoji' => 'heart',
            'is_active' => true,
        ]);

        $service = app(AiCommunityActivityService::class);
        $first = $service->engage(1);
        $second = $service->engage(1);

        $this->assertSame(1, $first['comments']);
        $this->assertSame(1, $first['reactions']);
        $this->assertSame(0, $second['comments']);
        $this->assertDatabaseCount('comments', 1);
        $this->assertDatabaseCount('reactions', 1);
        $this->assertDatabaseHas('users', ['email' => 'ai-community-duru@ografi.invalid']);
        $this->assertStringContainsString('AI destekli', (string) $post->comments()->firstOrFail()->user->bio);
    }
}
