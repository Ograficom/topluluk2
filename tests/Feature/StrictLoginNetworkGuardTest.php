<?php

namespace Tests\Feature;

use App\Models\RecaptchaSetting;
use App\Services\StrictLoginNetworkGuard;
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
        config(['login-security.fail_closed' => true]);
    }

    public function test_it_blocks_ipv4_datacenter_networks_used_by_vpns(): void
    {
        Http::fake([
            'https://check.torproject.org/torbulkexitlist' => Http::response(
                $this->ipv4List(20, '11.0.0.'),
                200,
            ),
            'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv4.txt' => Http::response(
                $this->ipv4Networks(100, '12.0.'),
                200,
            ),
            'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/datacenter/ipv4.txt' => Http::response(
                $this->ipv4Networks(100, '13.0.', ['45.67.89.0/24']),
                200,
            ),
        ]);

        $this->assertBlocked($this->request('45.67.89.10'));
    }

    public function test_it_blocks_ipv6_vpn_networks(): void
    {
        Http::fake([
            'https://check.torproject.org/torbulkexitlist' => Http::response(
                $this->ipv4List(20, '11.0.1.'),
                200,
            ),
            'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv6.txt' => Http::response(
                $this->ipv6Networks(10, ['2001:4860:1234::/48']),
                200,
            ),
        ]);

        $this->assertBlocked($this->request('2001:4860:1234::10'));
    }

    private function assertBlocked(Request $request): void
    {
        try {
            app(StrictLoginNetworkGuard::class)->assertAllowed($request, $this->settings());
            $this->fail('Request should have been blocked.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'VPN veya proxy üzerinden girişe izin verilmiyor.',
                $exception->errors()['email'][0] ?? null,
            );
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

    private function request(string $remoteIp): Request
    {
        return Request::create('/login', 'POST', [], [], [], [
            'REMOTE_ADDR' => $remoteIp,
            'HTTP_USER_AGENT' => 'Mozilla/5.0 AppleWebKit/537.36 Chrome/140.0 Safari/537.36',
            'HTTP_ORIGIN' => 'https://ografi.com',
            'HTTP_REFERER' => 'https://ografi.com/login',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
            'HTTP_ACCEPT_LANGUAGE' => 'tr-TR,tr;q=0.9,en;q=0.8',
        ]);
    }

    private function ipv4List(int $count, string $prefix): string
    {
        $entries = [];

        for ($i = 1; $i <= $count; $i++) {
            $entries[] = $prefix.$i;
        }

        return implode("\n", $entries);
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
}
