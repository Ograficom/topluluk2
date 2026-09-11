<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureInstalled;
use App\Services\StrictLoginNetworkGuard;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GlobalNetworkBlockMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
            'app.cipher' => 'AES-256-CBC',
        ]);

        // The CI test database is intentionally uninstalled. Disable only the
        // installer redirect so the real global web middleware stack can run.
        $this->withoutMiddleware(EnsureInstalled::class);
    }

    public function test_web_request_is_forbidden_when_network_guard_rejects_it(): void
    {
        $guard = Mockery::mock(StrictLoginNetworkGuard::class);
        $guard->shouldReceive('assertAllowed')
            ->once()
            ->andThrow(ValidationException::withMessages([
                'email' => 'VPN veya proxy üzerinden girişe izin verilmiyor.',
            ]));

        $this->app->instance(StrictLoginNetworkGuard::class, $guard);

        Route::middleware('web')->get('/__network-guard-test', fn () => 'ok');

        $this->get('/__network-guard-test')
            ->assertStatus(403);
    }

    public function test_web_request_continues_when_network_guard_allows_it(): void
    {
        $guard = Mockery::mock(StrictLoginNetworkGuard::class);
        $guard->shouldReceive('assertAllowed')
            ->once()
            ->andReturnNull();

        $this->app->instance(StrictLoginNetworkGuard::class, $guard);

        Route::middleware('web')->get('/__network-guard-test-ok', fn () => 'ok');

        $this->get('/__network-guard-test-ok')
            ->assertOk()
            ->assertSee('ok');
    }
}
