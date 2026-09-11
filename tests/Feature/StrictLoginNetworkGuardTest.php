<?php

namespace Tests\Feature;

use App\Models\RecaptchaSetting;
use App\Services\StrictLoginNetworkGuard;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StrictLoginNetworkGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config([
            'login-security.fail_closed' => true,
            'login-security.ipqs.enabled' => true,
            'login-security.ipqs.api_key' => 'test-key',
            'login-security.ipqs.base_url' => 'https://ipqualityscore.com/api/json/ip',
            'login-security.ipqs.strictness' => 1,
            'login-security.ipqs.allow_public_access_points' => true,
            'login-security.decision_cache_hours' => 12,
            'login-security.block_datacenter' => true,
        ]);
    }

    public function test_it_blocks_vpn_reported_by_ipqs_using_cloudflare_real_ip(): void
    {
        Http::fake([
            'https://ipqualityscore.com/api/json/ip/test-key/*' => Http::response($this->ipqs([
                'proxy' => true,
                'vpn' => true,
                'active_vpn' => true,
            ]), 200),
        ]);

        $request = $this->request('104.16.10.20', [
            'HTTP_CF_CONNECTING_IP' => '45.67.89.10',
        ]);

        $this->assertBlocked(
            $request,
            $this->settings(blockVpn: true, blockTor: false),
            'VPN veya proxy üzerinden erişime izin verilmiyor.',
        );

        Http::assertSent(function (HttpRequest $request) {
            return str_contains($request->url(), '/test-key/45.67.89.10')
                && (int) $request['strictness'] === 1
                && $request['allow_public_access_points'] === 'true';
        });
    }

    public function test_it_blocks_tor_reported_by_ipqs(): void
    {
        Http::fake([
            'https://ipqualityscore.com/api/json/ip/test-key/*' => Http::response($this->ipqs([
                'proxy' => true,
                'tor' => true,
                'active_tor' => true,
            ]), 200),
            'https://check.torproject.org/torbulkexitlist' => Http::response(
                $this->ipv4List(20, '11.0.0.'),
                200,
            ),
        ]);

        $this->assertBlocked(
            $this->request('45.67.89.11'),
            $this->settings(blockVpn: false, blockTor: true),
            'Tor ağı üzerinden erişime izin verilmiyor.',
        );
    }

    public function test_official_tor_list_supplements_a_safe_ipqs_response(): void
    {
        Http::fake([
            'https://ipqualityscore.com/api/json/ip/test-key/*' => Http::response($this->ipqs(), 200),
            'https://check.torproject.org/torbulkexitlist' => Http::response(
                $this->ipv4List(20, '11.0.1.', ['45.67.89.12']),
                200,
            ),
        ]);

        $this->assertBlocked(
            $this->request('45.67.89.12'),
            $this->settings(blockVpn: false, blockTor: true),
            'Tor ağı üzerinden erişime izin verilmiyor.',
        );
    }

    public function test_it_blocks_datacenter_connection_type_from_ipqs(): void
    {
        Http::fake([
            'https://ipqualityscore.com/api/json/ip/test-key/*' => Http::response($this->ipqs([
                'connection_type' => 'Data Center',
            ]), 200),
        ]);

        $this->assertBlocked(
            $this->request('45.67.89.13'),
            $this->settings(blockVpn: true, blockTor: false),
            'Veri merkezi veya hosting ağı üzerinden erişime izin verilmiyor.',
        );
    }

    public function test_ipqs_decision_is_cached_per_ip(): void
    {
        Http::fake([
            'https://ipqualityscore.com/api/json/ip/test-key/*' => Http::response($this->ipqs(), 200),
        ]);

        $guard = app(StrictLoginNetworkGuard::class);
        $settings = $this->settings(blockVpn: true, blockTor: false);
        $request = $this->request('45.67.89.14');

        $guard->assertAllowed($request, $settings);
        $guard->assertAllowed($request, $settings);

        Http::assertSentCount(1);
    }

    public function test_ipqs_failure_falls_back_to_local_vpn_and_proxy_lists(): void
    {
        config(['login-security.block_datacenter' => false]);

        Http::fake([
            'https://ipqualityscore.com/api/json/ip/test-key/*' => Http::response(['success' => false], 503),
            'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv4.txt' => Http::response(
                $this->ipv4Networks(100, '12.0.', ['45.67.89.0/24']),
                200,
            ),
            'https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/http.txt' => Http::response(
                $this->proxyList(20, '21.0.0.'),
                200,
            ),
            'https://raw.githubusercontent.com/TheSpeedX/SOCKS-List/master/socks4.txt' => Http::response(
                $this->proxyList(20, '22.0.0.'),
                200,
            ),
            'https://raw.githubusercontent.com/TheSpeedX/SOCKS-List/master/socks5.txt' => Http::response(
                $this->proxyList(20, '23.0.0.'),
                200,
            ),
        ]);

        $this->assertBlocked(
            $this->request('45.67.89.15'),
            $this->settings(blockVpn: true, blockTor: false),
            'VPN veya proxy üzerinden erişime izin verilmiyor.',
        );
    }

    public function test_missing_ipqs_key_uses_local_fallback_instead_of_bypassing_security(): void
    {
        config([
            'login-security.ipqs.api_key' => '',
            'login-security.block_datacenter' => false,
        ]);

        Http::fake([
            'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv4.txt' => Http::response(
                $this->ipv4Networks(100, '12.0.', ['45.67.90.0/24']),
                200,
            ),
            'https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/http.txt' => Http::response(
                $this->proxyList(20, '31.0.0.'),
                200,
            ),
            'https://raw.githubusercontent.com/TheSpeedX/SOCKS-List/master/socks4.txt' => Http::response(
                $this->proxyList(20, '32.0.0.'),
                200,
            ),
            'https://raw.githubusercontent.com/TheSpeedX/SOCKS-List/master/socks5.txt' => Http::response(
                $this->proxyList(20, '33.0.0.'),
                200,
            ),
        ]);

        $this->assertBlocked(
            $this->request('45.67.90.10'),
            $this->settings(blockVpn: true, blockTor: false),
            'VPN veya proxy üzerinden erişime izin verilmiyor.',
        );

        Http::assertNotSent(fn (HttpRequest $request) => str_contains($request->url(), 'ipqualityscore.com'));
    }

    public function test_threat_lists_can_be_force_refreshed_for_scheduler(): void
    {
        Http::fake([
            'https://check.torproject.org/torbulkexitlist' => Http::response(
                $this->ipv4List(20, '41.0.0.'),
                200,
            ),
            'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv4.txt' => Http::response(
                $this->ipv4Networks(100, '42.0.'),
                200,
            ),
            'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv6.txt' => Http::response(
                $this->ipv6Networks(10),
                200,
            ),
            'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/datacenter/ipv4.txt' => Http::response(
                $this->ipv4Networks(100, '43.0.'),
                200,
            ),
            'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/datacenter/ipv6.txt' => Http::response(
                $this->ipv6Networks(10, ['2001:4860:1234::/48']),
                200,
            ),
            'https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/http.txt' => Http::response(
                $this->proxyList(20, '44.0.0.'),
                200,
            ),
            'https://raw.githubusercontent.com/TheSpeedX/SOCKS-List/master/socks4.txt' => Http::response(
                $this->proxyList(20, '45.0.0.'),
                200,
            ),
            'https://raw.githubusercontent.com/TheSpeedX/SOCKS-List/master/socks5.txt' => Http::response(
                $this->proxyList(20, '46.0.0.'),
                200,
            ),
        ]);

        $result = app(StrictLoginNetworkGuard::class)->refreshThreatLists();

        $this->assertSame(8, $result['refreshed']);
        $this->assertSame(0, $result['failed']);
        $this->assertCount(8, $result['sources']);
    }

    private function assertBlocked(Request $request, RecaptchaSetting $settings, string $message): void
    {
        try {
            app(StrictLoginNetworkGuard::class)->assertAllowed($request, $settings);
            $this->fail('Request should have been blocked.');
        } catch (ValidationException $exception) {
            $this->assertSame($message, $exception->errors()['email'][0] ?? null);
        }
    }

    private function settings(bool $blockVpn = true, bool $blockTor = true): RecaptchaSetting
    {
        return new RecaptchaSetting([
            'block_vpn_logins' => $blockVpn,
            'block_tor_logins' => $blockTor,
            'bot_honeypot_enabled' => true,
            'verify_unknown_devices' => true,
        ]);
    }

    private function request(string $remoteIp, array $server = []): Request
    {
        return Request::create('/login', 'POST', [], [], [], array_merge([
            'REMOTE_ADDR' => $remoteIp,
            'HTTP_USER_AGENT' => 'Mozilla/5.0 AppleWebKit/537.36 Chrome/140.0 Safari/537.36',
            'HTTP_ORIGIN' => 'https://ografi.com',
            'HTTP_REFERER' => 'https://ografi.com/login',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
            'HTTP_ACCEPT_LANGUAGE' => 'tr-TR,tr;q=0.9,en;q=0.8',
        ], $server));
    }

    private function ipqs(array $overrides = []): array
    {
        return array_merge([
            'success' => true,
            'message' => 'Success.',
            'proxy' => false,
            'vpn' => false,
            'tor' => false,
            'active_vpn' => false,
            'active_tor' => false,
            'bot_status' => false,
            'fraud_score' => 0,
            'connection_type' => 'Residential',
        ], $overrides);
    }

    private function ipv4List(int $count, string $prefix, array $prepend = []): string
    {
        $entries = $prepend;

        for ($i = 1; $i <= $count; $i++) {
            $entries[] = $prefix.$i;
        }

        return implode("\n", array_values(array_unique($entries)));
    }

    private function ipv4Networks(int $count, string $prefix, array $prepend = []): string
    {
        $entries = $prepend;

        for ($i = 0; $i < $count; $i++) {
            $third = intdiv($i, 256);
            $fourth = $i % 256;
            $entries[] = $prefix.$third.'.'.$fourth.'/32';
        }

        return implode("\n", array_values(array_unique($entries)));
    }

    private function ipv6Networks(int $count, array $prepend = []): string
    {
        $entries = $prepend;

        for ($i = 1; $i <= $count; $i++) {
            $entries[] = '2001:db8:'.dechex($i).'::/48';
        }

        return implode("\n", array_values(array_unique($entries)));
    }

    private function proxyList(int $count, string $prefix): string
    {
        $entries = [];

        for ($i = 1; $i <= $count; $i++) {
            $entries[] = $prefix.$i.':8080';
        }

        return implode("\n", $entries);
    }
}
