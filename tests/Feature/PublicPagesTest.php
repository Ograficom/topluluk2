<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;


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
