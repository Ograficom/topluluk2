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
    }
}
