<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaManifestTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_has_real_png_icons_and_matching_screenshots(): void
    {
        $response = $this->get('/manifest.webmanifest')->assertOk();
        $this->assertStringStartsWith('application/manifest+json', $response->headers->get('Content-Type'));
        $manifest = $response->json();
        $this->assertSame('/', $manifest['id']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertCount(3, $manifest['icons']);

        foreach (array_merge($manifest['icons'], $manifest['screenshots']) as $image) {
            if (str_starts_with($image['src'], '/pwa/icon/')) {
                $response = $this->get($image['src'])->assertOk()->assertHeader('Content-Type', 'image/png');
                $info = getimagesizefromstring($response->getContent());
            } else {
                $file = public_path(ltrim(parse_url($image['src'], PHP_URL_PATH), '/'));
                $this->assertFileExists($file);
                $info = getimagesize($file);
            }
            $this->assertIsArray($info);
            $this->assertSame($image['sizes'], $info[0].'x'.$info[1]);
            $this->assertSame($image['type'], $info['mime']);
        }
        $this->assertSame(['any', 'any', 'maskable'], array_column($manifest['icons'], 'purpose'));
        $this->assertSame(['/posts/create', '/notifications', '/messenger', '/last-posts'], array_column($manifest['shortcuts'], 'url'));
        $this->assertContains('window-controls-overlay', $manifest['display_override']);
        $this->assertContains('tabbed', $manifest['display_override']);
        $this->assertSame('/posts/create', $manifest['share_target']['action']);
        $this->assertSame('/posts/create?open_files=1', $manifest['file_handlers'][0]['action']);
        $this->assertSame('web+ografi', $manifest['protocol_handlers'][0]['protocol']);
        $this->assertSame('navigate-existing', $manifest['launch_handler']['client_mode'][0]);
        $this->assertSame(480, $manifest['edge_side_panel']['preferred_width']);
        $this->assertSame('ografi-quick-access', $manifest['widgets'][0]['tag']);
        $file = public_path(ltrim($manifest['widgets'][0]['ms_ac_template'], '/'));
        $this->assertFileExists($file);
        $this->assertIsArray(json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR));
        $this->get($manifest['widgets'][0]['data'])->assertOk()->assertJsonStructure(['headline', 'description', 'latest']);
        $this->assertSame('com.ografi.twa', $manifest['related_applications'][0]['id']);
        $this->get($manifest['note_taking']['new_note_url'])->assertOk()->assertSee('Hızlı not');
        $this->assertArrayNotHasKey('iarc_rating_id', $manifest);
        $this->assertArrayNotHasKey('scope_extensions', $manifest);
        $preview = $manifest['widgets'][0]['screenshots'][0];
        $info = getimagesize(public_path(ltrim($preview['src'], '/')));
        $this->assertSame($preview['sizes'], $info[0].'x'.$info[1]);
        $this->assertSame($preview['type'], $info['mime']);
    }

    public function test_panel_icons_take_priority_over_site_branding_and_update_manifest_revision(): void
    {
        $disk = \Illuminate\Support\Facades\Storage::fake('public');
        $settings = \App\Models\SiteSetting::current() ?? new \App\Models\SiteSetting;
        $settings->id = 1;
        $settings->brand_logo_path = 'site-branding/logo.png';
        $disk->put('site-branding/logo.png', file_get_contents(public_path('assets/img/meta/pwa-icon-512.png')));
        $settings->save();

        $before = $this->get('/manifest.webmanifest')->assertOk()->json();
        $this->assertStringStartsWith('/assets/img/meta/pwa-icon-512.png?', $before['icons'][1]['src']);

        $custom = file_get_contents(public_path('assets/img/meta/pwa-icon-maskable-512.png'));
        $disk->put('pwa/icons/custom.png', $custom);
        $settings->pwa_icon_512_path = 'pwa/icons/custom.png';
        $settings->save();

        $after = $this->get('/manifest.webmanifest')->assertOk()->json();
        $this->assertNotSame($before['icons'][1]['src'], $after['icons'][1]['src']);
        $this->assertStringStartsWith('/pwa/icon/512?', $after['icons'][1]['src']);
        $response = $this->get($after['icons'][1]['src'])->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertSame($custom, $response->getContent());
    }

    public function test_advanced_manifest_uses_configured_metadata_and_desktop_preference(): void
    {
        $settings = \App\Models\SiteSetting::current() ?? new \App\Models\SiteSetting;
        $settings->id = 1;
        $settings->pwa_advanced = ['desktop_mode' => 'tabbed', 'iarc_rating_id' => 'test-only-cert',
            'scope_origins' => "https://help.ografi.com\nhttps://unrelated.example"];
        $settings->save();
        $manifest = $this->get('/manifest.webmanifest')->assertOk()->json();
        $this->assertSame('tabbed', $manifest['display_override'][0]);
        $this->assertSame('test-only-cert', $manifest['iarc_rating_id']);
        $this->assertSame([['type' => 'origin', 'origin' => 'https://help.ografi.com']], $manifest['scope_extensions']);
    }

    public function test_custom_protocol_only_opens_allowlisted_internal_pages(): void
    {
        $this->get('/pwa/open?uri='.urlencode('web+ografi://latest'))->assertRedirect('/last-posts');
        $this->get('/pwa/open?uri='.urlencode('https://example.com'))->assertStatus(400);
        $this->get('/pwa/open?uri='.urlencode('web+ografi://example.com'))->assertStatus(400);
    }
}
