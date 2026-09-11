<?php

namespace App\Services;

use App\Models\RecaptchaSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class StrictLoginNetworkGuard
{
    private const TOR_LIST_URL = 'https://check.torproject.org/torbulkexitlist';

    private const IPV4_NETWORK_LISTS = [
        [
            'key' => 'strict-vpn-ipv4',
            'url' => 'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv4.txt',
            'minimum' => 100,
        ],
        [
            'key' => 'strict-datacenter-ipv4',
            'url' => 'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/datacenter/ipv4.txt',
            'minimum' => 100,
        ],
    ];

    private const IPV6_NETWORK_LISTS = [
        [
            'key' => 'strict-vpn-ipv6',
            'url' => 'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv6.txt',
            'minimum' => 10,
        ],
        [
            'key' => 'strict-datacenter-ipv6',
            'url' => 'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/datacenter/ipv6.txt',
            'minimum' => 10,
        ],
    ];

    private const PROXY_LISTS = [
        [
            'key' => 'strict-proxy-http',
            'url' => 'https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/http.txt',
            'minimum' => 20,
        ],
        [
            'key' => 'strict-proxy-socks4',
            'url' => 'https://raw.githubusercontent.com/TheSpeedX/SOCKS-List/master/socks4.txt',
            'minimum' => 20,
        ],
        [
            'key' => 'strict-proxy-socks5',
            'url' => 'https://raw.githubusercontent.com/TheSpeedX/SOCKS-List/master/socks5.txt',
            'minimum' => 20,
        ],
    ];

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

    public function assertAllowed(Request $request, ?RecaptchaSetting $settings = null): void
    {
        $settings ??= RecaptchaSetting::currentOrNull();

        $blockTor = (bool) ($settings?->block_tor_logins ?? true);
        $blockVpn = (bool) ($settings?->block_vpn_logins ?? true);

        if (! $blockTor && ! $blockVpn) {
            return;
        }

        $ip = $this->clientIp($request);

        if (! $this->isPublicIp($ip)) {
            if ((bool) config('login-security.fail_closed', true)) {
                $this->block($request, 'strict_client_ip_unresolved', 'Giriş güvenlik kontrolü tamamlanamadı.', $ip);
            }

            return;
        }

        try {
            if ($blockTor && $this->matchesTor($ip)) {
                $this->block($request, 'strict_tor', 'Tor ağı üzerinden girişe izin verilmiyor.', $ip);
            }

            if ($blockVpn && $this->matchesVpnDatacenterOrProxy($ip)) {
                $this->block($request, 'strict_vpn_proxy', 'VPN veya proxy üzerinden girişe izin verilmiyor.', $ip);
            }
        } catch (RuntimeException $exception) {
            Log::error('Strict login network guard failed.', [
                'ip' => $ip,
                'path' => $request->path(),
                'message' => $exception->getMessage(),
            ]);

            if ((bool) config('login-security.fail_closed', true)) {
                $this->block(
                    $request,
                    'strict_risk_list_unavailable',
                    'Giriş güvenlik kontrolü şu anda doğrulanamıyor. Lütfen tekrar dene.',
                    $ip,
                );
            }
        }
    }

    private function matchesTor(string $ip): bool
    {
        foreach ($this->remoteList('strict-tor', self::TOR_LIST_URL, 'network', 20) as $entry) {
            if ($this->ipMatches($ip, $entry)) {
                return true;
            }
        }

        return false;
    }

    private function matchesVpnDatacenterOrProxy(string $ip): bool
    {
        $networkLists = str_contains($ip, ':') ? self::IPV6_NETWORK_LISTS : self::IPV4_NETWORK_LISTS;
        $loadedNetworkList = false;
        $lastException = null;

        foreach ($networkLists as $source) {
            try {
                $entries = $this->remoteList(
                    'login-security:'.$source['key'],
                    $source['url'],
                    'network',
                    (int) $source['minimum'],
                );
                $loadedNetworkList = true;

                foreach ($entries as $entry) {
                    if ($this->ipMatches($ip, $entry)) {
                        return true;
                    }
                }
            } catch (RuntimeException $exception) {
                $lastException = $exception;
            }
        }

        if (! $loadedNetworkList && $lastException) {
            throw $lastException;
        }

        if (str_contains($ip, ':')) {
            return false;
        }

        $loadedProxyList = false;

        foreach (self::PROXY_LISTS as $source) {
            try {
                $entries = $this->remoteList(
                    'login-security:'.$source['key'],
                    $source['url'],
                    'proxy',
                    (int) $source['minimum'],
                );
                $loadedProxyList = true;

                foreach ($entries as $entry) {
                    if ($this->ipMatches($ip, $entry)) {
                        return true;
                    }
                }
            } catch (RuntimeException $exception) {
                $lastException = $exception;
            }
        }

        if (! $loadedProxyList && ! $loadedNetworkList && $lastException) {
            throw $lastException;
        }

        return false;
    }

    private function remoteList(
        string $cacheKey,
        string $url,
        string $format,
        int $minimumEntries,
    ): array {
        $freshKey = $cacheKey.':fresh';
        $staleKey = $cacheKey.':last-good';

        $fresh = Cache::get($freshKey);
        if (is_array($fresh) && count($fresh) >= $minimumEntries) {
            return $fresh;
        }

        try {
            $response = Http::connectTimeout(2)
                ->timeout(6)
                ->retry(2, 150)
                ->withHeaders([
                    'Accept' => 'text/plain,*/*',
                    'User-Agent' => 'Ografi-Strict-Login-Network-Guard/1.0',
                ])
                ->get($url);

            if (! $response->successful()) {
                throw new RuntimeException('Risk list HTTP '.$response->status().' for '.$url);
            }

            $entries = collect(preg_split('/\r\n|\r|\n/', $response->body()) ?: [])
                ->map(fn ($line) => $this->normalizeEntry((string) $line, $format))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (count($entries) < $minimumEntries) {
                throw new RuntimeException('Risk list returned too few entries for '.$url);
            }

            Cache::put($freshKey, $entries, now()->addHours(2));
            Cache::put($staleKey, $entries, now()->addDays(7));

            return $entries;
        } catch (Throwable $exception) {
            $stale = Cache::get($staleKey);

            if (is_array($stale) && count($stale) >= $minimumEntries) {
                return $stale;
            }

            throw new RuntimeException(
                'Risk list unavailable and no last-known-good cache exists for '.$url,
                previous: $exception,
            );
        }
    }

    private function normalizeEntry(string $line, string $format): string
    {
        $line = trim((string) preg_replace('/\s+#.*$/', '', $line));

        if ($line === '' || str_starts_with($line, '#')) {
            return '';
        }

        if ($format === 'proxy') {
            $line = preg_replace('#^[a-z][a-z0-9+.-]*://#i', '', $line) ?? $line;
            $line = preg_replace('/^[^@]+@/', '', $line) ?? $line;

            if (preg_match('/^\[([0-9a-f:]+)\]:(\d+)$/i', $line, $match)) {
                $line = $match[1];
            } elseif (preg_match('/^(\d{1,3}(?:\.\d{1,3}){3}):\d+$/', $line, $match)) {
                $line = $match[1];
            }
        }

        if (filter_var($line, FILTER_VALIDATE_IP)) {
            return $line;
        }

        if (! str_contains($line, '/')) {
            return '';
        }

        [$network, $prefix] = array_pad(explode('/', $line, 2), 2, null);

        if (filter_var($network, FILTER_VALIDATE_IP) === false
            || filter_var($prefix, FILTER_VALIDATE_INT) === false) {
            return '';
        }

        $bits = (int) $prefix;
        $max = str_contains($network, ':') ? 128 : 32;

        return $bits >= 0 && $bits <= $max ? $network.'/'.$bits : '';
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
        $range = trim($range);

        if ($range === '') {
            return false;
        }

        if (! str_contains($range, '/')) {
            return hash_equals(strtolower($range), strtolower($ip));
        }

        [$network, $prefix] = array_pad(explode('/', $range, 2), 2, null);
        $ipBytes = @inet_pton($ip);
        $networkBytes = @inet_pton((string) $network);
        $bits = filter_var($prefix, FILTER_VALIDATE_INT);

        if ($ipBytes === false
            || $networkBytes === false
            || strlen($ipBytes) !== strlen($networkBytes)
            || $bits === false) {
            return false;
        }

        $maxBits = strlen($ipBytes) * 8;

        if ($bits < 0 || $bits > $maxBits) {
            return false;
        }

        $fullBytes = intdiv($bits, 8);
        $remainingBits = $bits % 8;

        if ($fullBytes > 0
            && substr($ipBytes, 0, $fullBytes) !== substr($networkBytes, 0, $fullBytes)) {
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

    private function block(Request $request, string $reason, string $message, string $ip): never
    {
        Log::notice('Strict login network guard blocked a request.', [
            'reason' => $reason,
            'ip' => $ip,
            'path' => $request->path(),
            'method' => $request->method(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);

        throw ValidationException::withMessages([
            'email' => $message,
        ]);
    }
}
