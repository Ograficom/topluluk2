<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;


    public function test_reaction_actions_require_authentication(): void
    {
        $userId = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
            'name' => 'Reaction Test User',
            'username' => 'reaction-test-user',
            'email' => 'reaction-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $postId = \Illuminate\Support\Facades\DB::table('posts')->insertGetId([
            'author_id' => $userId,
            'title' => 'Reaction Test Post',
            'slug' => 'reaction-test-' . uniqid(),
            'content' => '<p>Test</p>',
            'is_published' => true,
            'published_at' => now()->subMinute(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $commentId = \Illuminate\Support\Facades\DB::table('comments')->insertGetId([
            'post_id' => $postId,
            'user_id' => $userId,
            'content' => 'Test comment',
            'is_approved' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $post = \App\Models\Post::query()->findOrFail($postId);
        $comment = \App\Models\Comment::query()->findOrFail($commentId);

        $this->post(route('blog.post.reaction', $post), [
            'short_code' => 'like',
        ])->assertRedirect(route('login'));

        $this->post(route('blog.comment.like', $comment))
            ->assertRedirect(route('login'));

        $this->post(route('blog.comment.dislike', $comment))
            ->assertRedirect(route('login'));
    }

    public function test_editor_upload_endpoints_require_authentication(): void
    {
        $this->post(route('blog.editorjs.image'))->assertRedirect(route('login'));
        $this->post(route('blog.editorjs.video'))->assertRedirect(route('login'));
        $this->post(route('blog.editorjs.subtitle'))->assertRedirect(route('login'));
        $this->get(route('blog.editorjs.link', ['url' => 'https://example.com']))
            ->assertRedirect(route('login'));

        $this->post(route('api.blog.editorjs.video.init'), [
            'name' => 'video.mp4',
            'mime' => 'video/mp4',
            'size' => 1024,
        ])->assertRedirect(route('login'));
    }

    public function test_home_page_can_be_rendered(): void
    {
        $this->get('/')
            ->assertOk();
    }

    public function test_sss_page_can_be_rendered(): void
    {
        $response = $this->get('/p/sss');

        $response
            ->assertOk()
            ->assertSee('Sıkça Sorulan Sorular');
    }

    public function test_admin_login_page_can_be_rendered(): void
    {
        $this->get('/admin/login')
            ->assertOk();
    }
}
