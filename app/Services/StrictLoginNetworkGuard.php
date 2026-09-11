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

    private const VPN_IPV4_NETWORK_LISTS = [
        [
            'key' => 'strict-vpn-ipv4',
            'url' => 'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv4.txt',
            'minimum' => 100,
        ],
    ];

    private const VPN_IPV6_NETWORK_LISTS = [
        [
            'key' => 'strict-vpn-ipv6',
            'url' => 'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv6.txt',
            'minimum' => 10,
        ],
    ];

    private const DATACENTER_IPV4_NETWORK_LISTS = [
        [
            'key' => 'strict-datacenter-ipv4',
            'url' => 'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/datacenter/ipv4.txt',
            'minimum' => 100,
        ],
    ];

    private const DATACENTER_IPV6_NETWORK_LISTS = [
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
        $blockDatacenter = $blockVpn && (bool) config('login-security.block_datacenter', true);

        if (! $blockTor && ! $blockVpn) {
            return;
        }

        $ip = $this->clientIp($request);

        if (! $this->isPublicIp($ip)) {
            if ((bool) config('login-security.fail_closed', true)) {
                $this->block($request, 'client_ip_unresolved', 'Ağ güvenlik kontrolü tamamlanamadı.', $ip);
            }

            return;
        }

        try {
            $decision = $this->riskDecision($request, $ip, $blockTor, $blockVpn, $blockDatacenter);

            if ($blockTor && ($decision['tor'] || $decision['active_tor'])) {
                $this->block($request, 'tor', 'Tor ağı üzerinden erişime izin verilmiyor.', $ip, $decision);
            }

            $anonymousProxy = $decision['proxy']
                && ! $decision['tor']
                && ! $decision['active_tor'];

            if ($blockVpn && ($decision['vpn'] || $decision['active_vpn'] || $anonymousProxy)) {
                $this->block($request, 'vpn_proxy', 'VPN veya proxy üzerinden erişime izin verilmiyor.', $ip, $decision);
            }

            if ($blockDatacenter && $decision['datacenter']) {
                $this->block($request, 'datacenter', 'Veri merkezi veya hosting ağı üzerinden erişime izin verilmiyor.', $ip, $decision);
            }
        } catch (RuntimeException $exception) {
            Log::error('Network security guard could not verify request.', [
                'ip' => $ip,
                'path' => $request->path(),
                'message' => $exception->getMessage(),
            ]);

            if ((bool) config('login-security.fail_closed', true)) {
                $this->block(
                    $request,
                    'risk_intelligence_unavailable',
                    'Ağ güvenlik kontrolü şu anda doğrulanamıyor. Lütfen tekrar dene.',
                    $ip,
                );
            }
        }
    }

    public function refreshThreatLists(): array
    {
        $sources = [
            [
                'key' => 'strict-tor',
                'url' => self::TOR_LIST_URL,
                'format' => 'network',
                'minimum' => 20,
            ],
            ...array_map(fn (array $source) => [...$source, 'format' => 'network'], self::VPN_IPV4_NETWORK_LISTS),
            ...array_map(fn (array $source) => [...$source, 'format' => 'network'], self::VPN_IPV6_NETWORK_LISTS),
            ...array_map(fn (array $source) => [...$source, 'format' => 'network'], self::DATACENTER_IPV4_NETWORK_LISTS),
            ...array_map(fn (array $source) => [...$source, 'format' => 'network'], self::DATACENTER_IPV6_NETWORK_LISTS),
            ...array_map(fn (array $source) => [...$source, 'format' => 'proxy'], self::PROXY_LISTS),
        ];

        $refreshed = 0;
        $failed = 0;
        $results = [];

        foreach ($sources as $source) {
            try {
                $entries = $this->remoteList(
                    'login-security:'.$source['key'],
                    $source['url'],
                    $source['format'],
                    (int) $source['minimum'],
                    true,
                );

                $refreshed++;
                $results[] = [
                    'key' => $source['key'],
                    'ok' => true,
                    'count' => count($entries),
                ];
            } catch (Throwable $exception) {
                $failed++;
                $results[] = [
                    'key' => $source['key'],
                    'ok' => false,
                    'error' => $exception->getMessage(),
                ];
            }
        }

        return [
            'refreshed' => $refreshed,
            'failed' => $failed,
            'sources' => $results,
        ];
    }

    private function riskDecision(
        Request $request,
        string $ip,
        bool $blockTor,
        bool $blockVpn,
        bool $blockDatacenter,
    ): array {
        $cacheKey = 'login-security:decision:'.hash('sha256', strtolower($ip));
        $cached = Cache::get($cacheKey);

        if ($this->validDecision($cached)) {
            return $cached;
        }

        $decision = null;
        $apiError = null;

        if ((bool) config('login-security.ipqs.enabled', true)
            && trim((string) config('login-security.ipqs.api_key', '')) !== '') {
            try {
                $decision = $this->queryIpqs($request, $ip);
            } catch (Throwable $exception) {
                $apiError = $exception;
                Log::warning('IPQS network lookup failed; using local threat lists.', [
                    'ip' => $ip,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        if ($decision === null) {
            $decision = $this->localDecision($ip, $blockTor, $blockVpn, $blockDatacenter);
            $decision['source'] = $apiError ? 'local_fallback_after_ipqs_error' : 'local_fallback_no_ipqs_key';
        } elseif ($blockTor && $this->matchesTor($ip)) {
            // Defense in depth: supplement IPQS with the official Tor exit list.
            $decision['tor'] = true;
            $decision['active_tor'] = true;
            $decision['source'] = 'ipqs+official_tor';
        }

        Cache::put(
            $cacheKey,
            $decision,
            now()->addHours((int) config('login-security.decision_cache_hours', 12)),
        );

        return $decision;
    }

    private function queryIpqs(Request $request, string $ip): array
    {
        $apiKey = trim((string) config('login-security.ipqs.api_key', ''));
        $baseUrl = rtrim((string) config('login-security.ipqs.base_url'), '/');
        $timeout = (int) config('login-security.ipqs.timeout_seconds', 6);

        if ($apiKey === '') {
            throw new RuntimeException('IPQS API key is missing.');
        }

        $response = Http::acceptJson()
            ->connectTimeout(2)
            ->timeout($timeout)
            ->retry(2, 200)
            ->withHeaders([
                'User-Agent' => 'Ografi-Network-Security/2.0',
            ])
            ->get($baseUrl.'/'.rawurlencode($apiKey).'/'.rawurlencode($ip), [
                'strictness' => (int) config('login-security.ipqs.strictness', 1),
                'allow_public_access_points' => (bool) config('login-security.ipqs.allow_public_access_points', true) ? 'true' : 'false',
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
                'user_language' => mb_substr((string) $request->header('Accept-Language', ''), 0, 255),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('IPQS returned HTTP '.$response->status().'.');
        }

        $payload = $response->json();

        if (! is_array($payload) || ($payload['success'] ?? false) !== true) {
            $message = is_array($payload) ? (string) ($payload['message'] ?? 'Unknown IPQS error') : 'Invalid IPQS JSON response';
            throw new RuntimeException('IPQS lookup failed: '.$message);
        }

        $connectionType = strtolower(trim((string) ($payload['connection_type'] ?? '')));

        return [
            'proxy' => (bool) ($payload['proxy'] ?? false),
            'vpn' => (bool) ($payload['vpn'] ?? false),
            'tor' => (bool) ($payload['tor'] ?? false),
            'active_vpn' => (bool) ($payload['active_vpn'] ?? false),
            'active_tor' => (bool) ($payload['active_tor'] ?? false),
            'datacenter' => in_array($connectionType, ['data center', 'datacenter', 'hosting'], true),
            'bot_status' => (bool) ($payload['bot_status'] ?? false),
            'fraud_score' => is_numeric($payload['fraud_score'] ?? null) ? (float) $payload['fraud_score'] : null,
            'connection_type' => (string) ($payload['connection_type'] ?? ''),
            'source' => 'ipqs',
        ];
    }

    private function localDecision(
        string $ip,
        bool $blockTor,
        bool $blockVpn,
        bool $blockDatacenter,
    ): array {
        $decision = $this->emptyDecision();

        if ($blockTor) {
            $decision['tor'] = $this->matchesTor($ip);
            $decision['active_tor'] = $decision['tor'];
        }

        if ($blockVpn) {
            $decision['vpn'] = $this->matchesVpn($ip);
            $decision['active_vpn'] = $decision['vpn'];
            $decision['proxy'] = $this->matchesProxy($ip);
        }

        if ($blockDatacenter) {
            $decision['datacenter'] = $this->matchesDatacenter($ip);
        }

        return $decision;
    }

    private function emptyDecision(): array
    {
        return [
            'proxy' => false,
            'vpn' => false,
            'tor' => false,
            'active_vpn' => false,
            'active_tor' => false,
            'datacenter' => false,
            'bot_status' => false,
            'fraud_score' => null,
            'connection_type' => '',
            'source' => 'local',
        ];
    }

    private function validDecision(mixed $decision): bool
    {
        if (! is_array($decision)) {
            return false;
        }

        foreach (['proxy', 'vpn', 'tor', 'active_vpn', 'active_tor', 'datacenter'] as $field) {
            if (! array_key_exists($field, $decision)) {
                return false;
            }
        }

        return true;
    }

    private function matchesTor(string $ip): bool
    {
        foreach ($this->remoteList('login-security:strict-tor', self::TOR_LIST_URL, 'network', 20) as $entry) {
            if ($this->ipMatches($ip, $entry)) {
                return true;
            }
        }

        return false;
    }

    private function matchesVpn(string $ip): bool
    {
        return $this->matchesNetworkSources(
            $ip,
            str_contains($ip, ':') ? self::VPN_IPV6_NETWORK_LISTS : self::VPN_IPV4_NETWORK_LISTS,
        );
    }

    private function matchesDatacenter(string $ip): bool
    {
        return $this->matchesNetworkSources(
            $ip,
            str_contains($ip, ':') ? self::DATACENTER_IPV6_NETWORK_LISTS : self::DATACENTER_IPV4_NETWORK_LISTS,
        );
    }

    private function matchesProxy(string $ip): bool
    {
        $loaded = false;
        $lastException = null;

        foreach (self::PROXY_LISTS as $source) {
            try {
                $entries = $this->remoteList(
                    'login-security:'.$source['key'],
                    $source['url'],
                    'proxy',
                    (int) $source['minimum'],
                );
                $loaded = true;

                foreach ($entries as $entry) {
                    if ($this->ipMatches($ip, $entry)) {
                        return true;
                    }
                }
            } catch (RuntimeException $exception) {
                $lastException = $exception;
            }
        }

        if (! $loaded && $lastException) {
            throw $lastException;
        }

        return false;
    }

    private function matchesNetworkSources(string $ip, array $sources): bool
    {
        $loaded = false;
        $lastException = null;

        foreach ($sources as $source) {
            try {
                $entries = $this->remoteList(
                    'login-security:'.$source['key'],
                    $source['url'],
                    'network',
                    (int) $source['minimum'],
                );
                $loaded = true;

                foreach ($entries as $entry) {
                    if ($this->ipMatches($ip, $entry)) {
                        return true;
                    }
                }
            } catch (RuntimeException $exception) {
                $lastException = $exception;
            }
        }

        if (! $loaded && $lastException) {
            throw $lastException;
        }

        return false;
    }

    private function remoteList(
        string $cacheKey,
        string $url,
        string $format,
        int $minimumEntries,
        bool $forceRefresh = false,
    ): array {
        $freshKey = $cacheKey.':fresh';
        $staleKey = $cacheKey.':last-good';

        if (! $forceRefresh) {
            $fresh = Cache::get($freshKey);
            if (is_array($fresh) && count($fresh) >= $minimumEntries) {
                return $fresh;
            }
        }

        try {
            $response = Http::connectTimeout(2)
                ->timeout(8)
                ->retry(2, 200)
                ->withHeaders([
                    'Accept' => 'text/plain,*/*',
                    'User-Agent' => 'Ografi-Network-Security/2.0',
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

            Cache::put(
                $freshKey,
                $entries,
                now()->addHours((int) config('login-security.list_fresh_hours', 2)),
            );
            Cache::put(
                $staleKey,
                $entries,
                now()->addDays((int) config('login-security.list_stale_days', 7)),
            );

            return $entries;
        } catch (Throwable $exception) {
            $stale = Cache::get($staleKey);

            if (is_array($stale) && count($stale) >= $minimumEntries) {
                Log::warning('Using last-known-good network threat list.', [
                    'url' => $url,
                    'entries' => count($stale),
                    'message' => $exception->getMessage(),
                ]);

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

    private function block(
        Request $request,
        string $reason,
        string $message,
        string $ip,
        array $decision = [],
    ): never {
        Log::notice('Network security guard blocked a request.', [
            'reason' => $reason,
            'ip' => $ip,
            'path' => $request->path(),
            'method' => $request->method(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
            'risk_source' => $decision['source'] ?? null,
            'fraud_score' => $decision['fraud_score'] ?? null,
            'connection_type' => $decision['connection_type'] ?? null,
        ]);

        throw ValidationException::withMessages([
            'email' => $message,
        ]);
    }
}
