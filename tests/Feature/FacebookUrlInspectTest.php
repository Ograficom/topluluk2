<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FacebookUrlInspectTest extends TestCase
{
    public function test_it_queries_the_canonical_url_with_server_side_credentials(): void
    {
        config([
            'services.facebook.access_token' => 'test-token',
            'services.facebook.graph_version' => 'v27.0',
        ]);

        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['id' => 'https://ografi.com/posts/test', 'engagement' => ['share_count' => 3]]),
        ]);

        $this->artisan('facebook:url', ['post' => 'https://ografi.com/posts/test?utm_source=test'])
            ->assertExitCode(0);

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'GET'
                && str_starts_with($request->url(), 'https://graph.facebook.com/v27.0/')
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && $request['id'] === 'https://ografi.com/posts/test'
                && $request['fields'] === 'id,engagement'
                && ! str_contains($request->url(), 'test-token');
        });
    }

    public function test_it_rejects_external_urls_and_missing_credentials(): void
    {
        config(['services.facebook.access_token' => '']);
        Http::fake();

        $this->artisan('facebook:url', ['post' => 'https://example.com/posts/test'])->assertExitCode(1);
        $this->artisan('facebook:url', ['post' => 'https://ografi.com/posts/test'])->assertExitCode(1);

        Http::assertNothingSent();
    }

    public function test_api_errors_do_not_print_sensitive_response_messages(): void
    {
        config(['services.facebook.access_token' => 'test-token']);
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['error' => ['code' => 190, 'message' => 'test-token']], 400),
        ]);

        $this->artisan('facebook:url', ['post' => 'https://ografi.com/posts/test'])
            ->expectsOutput('Facebook Graph API sorgusu başarısız (HTTP 400, Meta kodu 190).')
            ->assertExitCode(1);
    }
}
