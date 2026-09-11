<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ProxyCheckNetworkGuard
{
    private const CLOUDFLARE_NETWORKS = [
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    public function assertAllowed(Request $request): void
    {
        if (! (bool) config('login-security.proxycheck.enabled', true)) {
            return;
        }

        $blockVpn = (bool) config('login-security.global_block_vpn', true);
        $blockTor = (bool) config('login-security.global_block_tor', true);
        $blockDatacenter = $blockVpn && (bool) config('login-security.block_datacenter', true);

        if (! $blockVpn && ! $blockTor) {
            return;
        }

        $ip = $this->clientIp($request);
        if (! $this->isPublicIp($ip)) {
            return;
        }

        $cacheKey = 'login-security:proxycheck:decision:'.hash('sha256', strtolower($ip));
        $decision = Cache::get($cacheKey);

        if (! is_array($decision)) {
            try {
                $decision = $this->query($ip);
                Cache::put(
                    $cacheKey,
                    $decision,
                    now()->addHours((int) config('login-security.decision_cache_hours', 12)),
                );
            } catch (Throwable $exception) {
                Log::warning('ProxyCheck live lookup failed; primary local/IPQS guard remains authoritative.', [
                    'ip' => $ip,
                    'path' => $request->path(),
                    'message' => $exception->getMessage(),
                ]);

                return;
            }
        }

        if ($blockTor && (bool) ($decision['tor'] ?? false)) {
            $this->block($request, 'proxycheck_tor', 'Tor ağı üzerinden erişime izin verilmiyor.', $ip, $decision);
        }

        if ($blockVpn && ((bool) ($decision['vpn'] ?? false) || (bool) ($decision['proxy'] ?? false))) {
            $this->block($request, 'proxycheck_vpn_proxy', 'VPN veya proxy üzerinden erişime izin verilmiyor.', $ip, $decision);
        }

        if ($blockDatacenter && (bool) ($decision['datacenter'] ?? false)) {
            $this->block($request, 'proxycheck_datacenter', 'Veri merkezi veya hosting ağı üzerinden erişime izin verilmiyor.', $ip, $decision);
        }
    }

    private function query(string $ip): array
    {
        $apiKey = trim((string) config('login-security.proxycheck.api_key', ''));
        $baseUrl = rtrim((string) config('login-security.proxycheck.base_url', 'https://proxycheck.io/v2'), '/');
        $timeout = (int) config('login-security.proxycheck.timeout_seconds', 6);

        if ($apiKey === '') {
            $this->consumeKeylessBudget();
        }

        $query = [
            'vpn' => 3,
            'asn' => 1,
            'risk' => 1,
            'tag' => 0,
        ];

        if ($apiKey !== '') {
            $query['key'] = $apiKey;
        }

        $response = Http::acceptJson()
            ->connectTimeout(2)
            ->timeout($timeout)
            ->retry(1, 200)
            ->withHeaders([
                'User-Agent' => 'Ografi-Network-Security/3.0',
            ])
            ->get($baseUrl.'/'.rawurlencode($ip), $query);

        if ($response->status() === 429) {
            throw new RuntimeException('ProxyCheck daily query limit reached.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('ProxyCheck returned HTTP '.$response->status().'.');
        }

        $payload = $response->json();
        if (! is_array($payload) || strtolower((string) ($payload['status'] ?? '')) !== 'ok') {
            throw new RuntimeException('ProxyCheck returned an invalid response.');
        }

        $record = $payload[$ip] ?? null;
        if (! is_array($record)) {
            throw new RuntimeException('ProxyCheck response does not contain the queried IP.');
        }

        $type = strtolower(trim((string) ($record['type'] ?? '')));
        $proxy = $this->yes($record['proxy'] ?? false);
        $vpn = $this->yes($record['vpn'] ?? false) || str_contains($type, 'vpn');
        $tor = str_contains($type, 'tor');
        $datacenter = in_array($type, ['hosting', 'datacenter', 'data center'], true)
            || str_contains($type, 'hosting')
            || str_contains($type, 'datacenter');

        return [
            'proxy' => $proxy,
            'vpn' => $vpn,
            'tor' => $tor,
            'datacenter' => $datacenter,
            'type' => (string) ($record['type'] ?? ''),
            'risk' => is_numeric($record['risk'] ?? null) ? (float) $record['risk'] : null,
            'provider' => (string) ($record['provider'] ?? ''),
            'source' => 'proxycheck',
        ];
    }

    private function consumeKeylessBudget(): void
    {
        $limit = (int) config('login-security.proxycheck.keyless_daily_limit', 90);
        $key = 'login-security:proxycheck:keyless:'.now()->format('Y-m-d');

        Cache::add($key, 0, now()->endOfDay());
        $used = (int) Cache::increment($key);

        if ($used > $limit) {
            throw new RuntimeException('ProxyCheck keyless daily safety budget exhausted.');
        }
    }

    private function yes(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['yes', 'true', '1'], true);
    }

    private function clientIp(Request $request): string
    {
        $direct = trim((string) $request->server('REMOTE_ADDR', ''));

        if ($this->isPublicIp($direct) && $this->ipMatchesAny($direct, self::CLOUDFLARE_NETWORKS)) {
            $cloudflareIp = trim((string) $request->header('CF-Connecting-IP', ''));
            if ($this->isPublicIp($cloudflareIp)) {
                return $cloudflareIp;
            }
        }

        if (! $this->isPublicIp($direct)) {
            foreach ([
                (string) $request->header('CF-Connecting-IP', ''),
                (string) $request->header('X-Real-IP', ''),
            ] as $forwardedIp) {
                $forwardedIp = trim($forwardedIp);
                if ($this->isPublicIp($forwardedIp)) {
                    return $forwardedIp;
                }
            }

            foreach (explode(',', (string) $request->header('X-Forwarded-For', '')) as $forwardedIp) {
                $forwardedIp = trim($forwardedIp);
                if ($this->isPublicIp($forwardedIp)) {
                    return $forwardedIp;
                }
            }
        }

        if ($this->isPublicIp($direct)) {
            return $direct;
        }

        $laravelIp = trim((string) $request->ip());

        return $this->isPublicIp($laravelIp) ? $laravelIp : '';
    }

    private function ipMatchesAny(string $ip, array $ranges): bool
    {
        foreach ($ranges as $range) {
            if ($this->ipMatches($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    private function ipMatches(string $ip, string $range): bool
    {
        if (! str_contains($range, '/')) {
            return hash_equals(strtolower($range), strtolower($ip));
        }

        [$network, $prefix] = array_pad(explode('/', $range, 2), 2, null);
        $ipBytes = @inet_pton($ip);
        $networkBytes = @inet_pton((string) $network);
        $bits = filter_var($prefix, FILTER_VALIDATE_INT);

        if ($ipBytes === false || $networkBytes === false || strlen($ipBytes) !== strlen($networkBytes) || $bits === false) {
            return false;
        }

        $maxBits = strlen($ipBytes) * 8;
        if ($bits < 0 || $bits > $maxBits) {
            return false;
        }

        $fullBytes = intdiv($bits, 8);
        $remainingBits = $bits % 8;

        if ($fullBytes > 0 && substr($ipBytes, 0, $fullBytes) !== substr($networkBytes, 0, $fullBytes)) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = (0xFF << (8 - $remainingBits)) & 0xFF;

        return (ord($ipBytes[$fullBytes]) & $mask) === (ord($networkBytes[$fullBytes]) & $mask);
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ) !== false;
    }

    private function block(Request $request, string $reason, string $message, string $ip, array $decision): never
    {
        Log::notice('ProxyCheck network guard blocked a request.', [
            'reason' => $reason,
            'ip' => $ip,
            'path' => $request->path(),
            'method' => $request->method(),
            'type' => $decision['type'] ?? null,
            'risk' => $decision['risk'] ?? null,
            'provider' => $decision['provider'] ?? null,
        ]);

        throw ValidationException::withMessages([
            'email' => $message,
        ]);
    }
}
