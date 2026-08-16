<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_about_url_redirects_permanently_to_canonical_url(): void
    {
        $this->get('/p/hakkinda')
            ->assertRedirect('/sayfa/hakkimizda')
            ->assertStatus(301);
    }

    public function test_about_page_exposes_canonical_and_organization_identity(): void
    {
        Page::query()->create([
            'title' => 'Hakkımızda',
            'slug' => 'hakkimizda',
            'content' => '<p>Ografi hakkında.</p>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->get('/sayfa/hakkimizda');

        $response->assertOk()
            ->assertSee('<link rel="canonical" href="http://localhost/sayfa/hakkimizda">', false)
            ->assertSee('Eabodur Medya Prodüksiyon Limited Şirketi')
            ->assertSee('2025-12-25');
    }
}
