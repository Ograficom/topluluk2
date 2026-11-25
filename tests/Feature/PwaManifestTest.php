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
            $file = public_path(ltrim($image['src'], '/'));
            $this->assertFileExists($file);
            $info = getimagesize($file);
            $this->assertSame($image['sizes'], $info[0].'x'.$info[1]);
            $this->assertSame($image['type'], $info['mime']);
        }
        $this->assertSame(['any', 'any', 'maskable'], array_column($manifest['icons'], 'purpose'));
        $this->assertSame(['/posts/create', '/notifications', '/messenger', '/last-posts'], array_column($manifest['shortcuts'], 'url'));
        $this->assertSame('ografi-quick-access', $manifest['widgets'][0]['tag']);
        foreach (['ms_ac_template', 'data'] as $key) {
            $file = public_path(ltrim($manifest['widgets'][0][$key], '/'));
            $this->assertFileExists($file);
            $this->assertIsArray(json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR));
        }
        $preview = $manifest['widgets'][0]['screenshots'][0];
        $info = getimagesize(public_path(ltrim($preview['src'], '/')));
        $this->assertSame($preview['sizes'], $info[0].'x'.$info[1]);
        $this->assertSame($preview['type'], $info['mime']);
    }
}
