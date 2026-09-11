<?php

namespace Tests\Feature;

use App\Services\StrictLoginNetworkGuard;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GlobalNetworkBlockMiddlewareTest extends TestCase
{
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
