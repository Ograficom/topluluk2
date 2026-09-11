<?php

namespace Tests\Feature;

use App\Services\ProxyCheckNetworkGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProxyCheckNetworkGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config([
            'login-security.global_block_vpn' => true,
            'login-security.global_block_tor' => true,
            'login-security.block_datacenter' => true,
            'login-security.decision_cache_hours' => 12,
            'login-security.proxycheck.enabled' => true,
            'login-security.proxycheck.api_key' => '',
            'login-security.proxycheck.base_url' => 'https://proxycheck.io/v2',
            'login-security.proxycheck.timeout_seconds' => 6,
            'login-security.proxycheck.keyless_daily_limit' => 90,
        ]);
    }

    public function test_it_blocks_a_live_vpn_detection(): void
    {
        Http::fake([
            'https://proxycheck.io/v2/*' => Http::response([
                'status' => 'ok',
                '45.67.89.10' => [
                    'proxy' => 'yes',
                    'type' => 'VPN',
                    'risk' => '99',
                    'provider' => 'Example VPN',
                ],
            ], 200),
        ]);

        try {
            app(ProxyCheckNetworkGuard::class)->assertAllowed($this->request('45.67.89.10'));
            $this->fail('VPN should have been blocked.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'VPN veya proxy üzerinden erişime izin verilmiyor.',
                $exception->errors()['email'][0] ?? null,
            );
        }
    }

    public function test_it_uses_the_cached_live_decision_for_repeated_requests(): void
    {
        Http::fake([
            'https://proxycheck.io/v2/*' => Http::response([
                'status' => 'ok',
                '45.67.89.11' => [
                    'proxy' => 'yes',
                    'type' => 'VPN',
                ],
            ], 200),
        ]);

        foreach ([1, 2] as $attempt) {
            try {
                app(ProxyCheckNetworkGuard::class)->assertAllowed($this->request('45.67.89.11'));
                $this->fail('VPN should have been blocked on attempt '.$attempt.'.');
            } catch (ValidationException) {
                // Expected.
            }
        }

        Http::assertSentCount(1);
    }

    public function test_it_allows_a_clean_live_result(): void
    {
        Http::fake([
            'https://proxycheck.io/v2/*' => Http::response([
                'status' => 'ok',
                '8.8.8.8' => [
                    'proxy' => 'no',
                    'type' => 'Business',
                    'risk' => '0',
                ],
            ], 200),
        ]);

        app(ProxyCheckNetworkGuard::class)->assertAllowed($this->request('8.8.8.8'));

        $this->assertTrue(true);
    }

    public function test_it_uses_cloudflare_connecting_ip_for_live_check(): void
    {
        Http::fake([
            'https://proxycheck.io/v2/45.67.89.12*' => Http::response([
                'status' => 'ok',
                '45.67.89.12' => [
                    'proxy' => 'yes',
                    'type' => 'VPN',
                ],
            ], 200),
        ]);

        $request = Request::create('/', 'GET', [], [], [], [
            'REMOTE_ADDR' => '104.16.10.10',
            'HTTP_CF_CONNECTING_IP' => '45.67.89.12',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);

        $this->expectException(ValidationException::class);
        app(ProxyCheckNetworkGuard::class)->assertAllowed($request);
    }

    private function request(string $remoteIp): Request
    {
        return Request::create('/', 'GET', [], [], [], [
            'REMOTE_ADDR' => $remoteIp,
            'HTTP_USER_AGENT' => 'Mozilla/5.0 AppleWebKit/537.36 Chrome/140.0 Safari/537.36',
            'HTTP_ACCEPT_LANGUAGE' => 'tr-TR,tr;q=0.9,en;q=0.8',
        ]);
    }
}
