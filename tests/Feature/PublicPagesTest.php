<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_sss_page_can_be_rendered(): void
    {
        $response = $this->get('/p/sss');

        $response
            ->assertOk()
            ->assertSee('S&#305;k&#231;a Sorulan Sorular', false);
    }

    public function test_admin_login_page_can_be_rendered(): void
    {
        $this->get('/admin/login')
            ->assertOk();
    }
}
