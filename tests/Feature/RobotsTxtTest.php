<?php

namespace Tests\Feature;

use Tests\TestCase;

class RobotsTxtTest extends TestCase
{
    public function test_robots_txt_contains_only_supported_directives(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $content = (string) $response->getContent();
        $expectedSitemapUrl = url('/sitemap.xml');

        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Allow: /', $content);
        $this->assertStringContainsString('Sitemap: ' . $expectedSitemapUrl, $content);
        $this->assertSupportedDirectivesOnly($content);
    }

    public function test_public_robots_txt_file_contains_only_supported_directives(): void
    {
        $path = public_path('robots.txt');

        $this->assertFileExists($path);

        $content = (string) file_get_contents($path);

        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Allow: /', $content);
        $this->assertSupportedDirectivesOnly($content);
    }

    private function assertSupportedDirectivesOnly(string $content): void
    {
        $supportedDirectives = ['User-agent', 'Allow', 'Disallow', 'Sitemap'];

        foreach (preg_split('/\R/', $content) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $directive = strtok($line, ':');

            $this->assertContains($directive, $supportedDirectives, sprintf('Unsupported robots.txt directive found: %s', $line));
        }

        $this->assertStringNotContainsString('Content-Signal:', $content);
        $this->assertStringNotContainsString('ai-train=', $content);
    }
}
