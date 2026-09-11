<?php

namespace Tests\Feature;

use App\Models\RecaptchaSetting;
use App\Services\LoginSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginSecurityServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config(['login-security.fail_closed' => true]);
    }

    public function test_it_blocks_tor_exit_nodes(): void
    {
        Http::fake([
            'https://check.torproject.org/torbulkexitlist' => Http::response(
                $this->ipList(20, '11.0.0.', ['45.67.89.10']),
                200,
            ),
        ]);

        $this->assertBlocked(
            $this->request('45.67.89.10'),
            'Tor ağı üzerinden girişe izin verilmiyor.',
        );
    }

    public function test_it_uses_cloudflare_client_ip_and_blocks_vpn_networks(): void
    {
        Http::fake([
            'https://check.torproject.org/torbulkexitlist' => Http::response(
                $this->ipList(20, '11.0.1.'),
                200,
            ),
            'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv4.txt' => Http::response(
                $this->networkList(100, '12.0.', ['45.67.89.0/24']),
                200,
            ),
        ]);

        $request = $this->request(
            remoteIp: '104.16.10.20',
            extraServer: ['HTTP_CF_CONNECTING_IP' => '45.67.89.10'],
        );

        $this->assertBlocked(
            $request,
            'VPN veya proxy üzerinden girişe izin verilmiyor.',
        );
    }

    public function test_it_blocks_public_http_proxy_addresses(): void
    {
        Http::fake([
            'https://check.torproject.org/torbulkexitlist' => Http::response(
                $this->ipList(20, '11.0.2.'),
                200,
            ),
            'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv4.txt' => Http::response(
                $this->networkList(100, '12.1.'),
                200,
            ),
            'https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/http.txt' => Http::response(
                $this->proxyList(20, '13.0.0.', ['45.67.89.10:8080']),
                200,
            ),
        ]);

        $this->assertBlocked(
            $this->request('45.67.89.10'),
            'VPN veya proxy üzerinden girişe izin verilmiyor.',
        );
    }

    public function test_it_blocks_known_automation_clients_before_network_checks(): void
    {
        Http::preventStrayRequests();

        $request = $this->request(
            remoteIp: '45.67.89.10',
            userAgent: 'curl/8.12.1',
        );

        $this->assertBlocked(
            $request,
            'Otomatik giriş isteği engellendi.',
        );

        Http::assertNothingSent();
    }

    private function assertBlocked(Request $request, string $message): void
    {
        try {
            app(LoginSecurityService::class)->assertRequestAllowed($request, $this->settings());
            $this->fail('Request should have been blocked.');
        } catch (ValidationException $exception) {
            $this->assertSame($message, $exception->errors()['email'][0] ?? null);
        }
    }

    private function settings(): RecaptchaSetting
    {
        return new RecaptchaSetting([
            'block_vpn_logins' => true,
            'block_tor_logins' => true,
            'bot_honeypot_enabled' => true,
            'verify_unknown_devices' => true,
        ]);
    }

    private function request(
        string $remoteIp,
        string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/140.0 Safari/537.36',
        array $extraServer = [],
    ): Request {
        return Request::create('/login', 'POST', [], [], [], array_merge([
            'REMOTE_ADDR' => $remoteIp,
            'HTTP_USER_AGENT' => $userAgent,
            'HTTP_ORIGIN' => 'https://example.test',
            'HTTP_REFERER' => 'https://example.test/login',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
            'HTTP_ACCEPT_LANGUAGE' => 'tr-TR,tr;q=0.9,en;q=0.8',
        ], $extraServer));
    }

    private function ipList(int $count, string $prefix, array $prepend = []): string
    {
        $entries = $prepend;

        for ($i = 1; $i <= $count; $i++) {
            $entries[] = $prefix.$i;
        }

        return implode("\n", array_values(array_unique($entries)));
    }

    private function networkList(int $count, string $prefix, array $prepend = []): string
    {
        $entries = $prepend;

        for ($i = 0; $i < $count; $i++) {
            $third = intdiv($i, 256);
            $fourth = $i % 256;
            $entries[] = $prefix.$third.'.'.$fourth.'/32';
        }

        return implode("\n", array_values(array_unique($entries)));
    }

    private function proxyList(int $count, string $prefix, array $prepend = []): string
    {
        $entries = $prepend;

        for ($i = 1; $i <= $count; $i++) {
            $entries[] = $prefix.$i.':8080';
        }

        return implode("\n", array_values(array_unique($entries)));
    }
}
