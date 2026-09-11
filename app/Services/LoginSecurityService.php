<?php

namespace App\Services;

use App\Models\RecaptchaSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class LoginSecurityService
{
    private const TRUSTED_DEVICE_COOKIE = 'ografi_trusted_device';
    private const PENDING_DEVICE_SESSION = 'ografi_login_device_pending';

    private const TOR_LIST_URL = 'https://check.torproject.org/torbulkexitlist';
    private const VPN_LIST_URL = 'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv4.txt';

    private const PROXY_LIST_URLS = [
        'https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/http.txt',
        'https://raw.githubusercontent.com/TheSpeedX/SOCKS-List/master/socks4.txt',
        'https://raw.githubusercontent.com/TheSpeedX/SOCKS-List/master/socks5.txt',
    ];

    /**
     * Cloudflare publishes these networks at:
     * https://www.cloudflare.com/ips-v4/
     * https://www.cloudflare.com/ips-v6/
     *
     * They are used only to decide whether CF-Connecting-IP can be trusted.
     */
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

    public function assertRequestAllowed(Request $request, ?RecaptchaSetting $settings = null): void
    {
        $settings ??= RecaptchaSetting::currentOrNull();

        if (($settings?->bot_honeypot_enabled ?? true) && trim((string) $request->input('website', '')) !== '') {
            $this->block($request, 'bot_honeypot', 'Giriş doğrulanamadı.');
        }

        if (($settings?->bot_honeypot_enabled ?? true) && $this->looksAutomated($request)) {
            $this->block($request, 'automation', 'Otomatik giriş isteği engellendi.');
        }

        $ip = $this->clientIp($request);
        if (! $this->isPublicIp($ip)) {
            Log::warning('Login security could not resolve a public client IP.', [
                'request_ip' => (string) $request->ip(),
                'remote_addr' => (string) $request->server('REMOTE_ADDR', ''),
                'path' => $request->path(),
            ]);

            if ((bool) config('login-security.fail_closed', true)) {
                $this->block(
                    $request,
                    'client_ip_unresolved',
                    'Giriş güvenlik kontrolü tamamlanamadı. Lütfen tekrar dene.',
                    $ip,
                );
            }

            return;
        }

        try {
            if (($settings?->block_tor_logins ?? true) && $this->isTorExitNode($ip)) {
                $this->block($request, 'tor', 'Tor ağı üzerinden girişe izin verilmiyor.', $ip);
            }

            if (($settings?->block_vpn_logins ?? true) && $this->isVpnOrProxyAddress($ip)) {
                $this->block($request, 'vpn_proxy', 'VPN veya proxy üzerinden girişe izin verilmiyor.', $ip);
            }
        } catch (RuntimeException $exception) {
            Log::error('Login network security check failed.', [
                'ip' => $ip,
                'path' => $request->path(),
                'message' => $exception->getMessage(),
            ]);

            if ((bool) config('login-security.fail_closed', true)) {
                $this->block(
                    $request,
                    'risk_list_unavailable',
                    'Giriş güvenlik kontrolü şu anda doğrulanamıyor. Lütfen biraz sonra tekrar dene.',
                    $ip,
                );
            }
        }
    }

    public function verifyOrChallenge(User $user, Request $request, ?RecaptchaSetting $settings = null): void
    {
        $settings ??= RecaptchaSetting::currentOrNull();

        if (! ($settings?->verify_unknown_devices ?? true)) {
            return;
        }

        if ($this->isTrustedDevice($user, $request)) {
            return;
        }

        $pending = $request->session()->get(self::PENDING_DEVICE_SESSION);
        $submittedCode = preg_replace('/\D+/', '', (string) $request->input('device_verification_code', '')) ?? '';

        if (is_array($pending) && (int) ($pending['user_id'] ?? 0) === (int) $user->getKey()) {
            if ((int) ($pending['expires_at'] ?? 0) <= now()->timestamp) {
                $request->session()->forget(self::PENDING_DEVICE_SESSION);
                $pending = null;
            } elseif ($submittedCode !== '') {
                $attempts = (int) ($pending['attempts'] ?? 0) + 1;
                $pending['attempts'] = $attempts;
                $request->session()->put(self::PENDING_DEVICE_SESSION, $pending);

                if ($attempts > 5) {
                    $request->session()->forget(self::PENDING_DEVICE_SESSION);
                    $this->reject('Çok fazla hatalı doğrulama kodu girildi. Tekrar giriş yap.');
                }

                if (hash_equals((string) ($pending['code_hash'] ?? ''), hash('sha256', $submittedCode))) {
                    $request->session()->forget(self::PENDING_DEVICE_SESSION);
                    $this->trustCurrentDevice($user, $request, $settings);

                    return;
                }

                $this->reject('Cihaz doğrulama kodu hatalı.');
            } else {
                $this->reject('E-postana gönderilen 6 haneli cihaz doğrulama kodunu gir.');
            }
        }

        if (! filled($user->email)) {
            $this->reject('Bu cihaz doğrulanamadı. Hesabında geçerli bir e-posta adresi bulunmalı.');
        }

        $code = (string) random_int(100000, 999999);
        $request->session()->put(self::PENDING_DEVICE_SESSION, [
            'user_id' => (int) $user->getKey(),
            'code_hash' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
        ]);

        try {
            $device = $this->deviceLabel((string) $request->userAgent());
            $ip = $this->clientIp($request);
            $body = "Ografi hesabına bilinmeyen bir cihazdan giriş deneniyor.\n\n"
                . "Doğrulama kodun: {$code}\n\n"
                . "Cihaz: {$device}\n"
                . "IP: {$ip}\n\n"
                . 'Kod 10 dakika geçerlidir. Bu giriş sana ait değilse kodu kimseyle paylaşma.';

            Mail::raw($body, function ($message) use ($user) {
                $message->to((string) $user->email)
                    ->subject('Ografi yeni cihaz doğrulaması');
            });
        } catch (Throwable $exception) {
            $request->session()->forget(self::PENDING_DEVICE_SESSION);
            Log::error('Login device verification mail failed.', [
                'user_id' => $user->getKey(),
                'message' => $exception->getMessage(),
            ]);

            $this->reject('Cihaz doğrulama e-postası gönderilemedi. Lütfen daha sonra tekrar dene.');
        }

        $this->reject('Bu cihazı tanımıyoruz. E-postana gönderilen 6 haneli kodu gir.');
    }

    public function trustCurrentDevice(User $user, Request $request, ?RecaptchaSetting $settings = null): void
    {
        $settings ??= RecaptchaSetting::currentOrNull();
        $days = max(1, min(365, (int) ($settings?->trusted_device_days ?? 90)));

        $payload = base64_encode(json_encode([
            'uid' => (int) $user->getKey(),
            'ua' => $this->userAgentHash((string) $request->userAgent()),
            'exp' => now()->addDays($days)->timestamp,
        ], JSON_UNESCAPED_SLASHES));

        Cookie::queue(Cookie::make(
            self::TRUSTED_DEVICE_COOKIE,
            $payload,
            $days * 24 * 60,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'lax',
        ));
    }

    public function hasPendingDeviceChallenge(Request $request): bool
    {
        return $this->pendingDeviceUserId($request) !== null;
    }

    public function pendingDeviceUserId(Request $request): ?int
    {
        $pending = $request->session()->get(self::PENDING_DEVICE_SESSION);

        if (! is_array($pending)) {
            return null;
        }

        if ((int) ($pending['expires_at'] ?? 0) <= now()->timestamp) {
            $request->session()->forget(self::PENDING_DEVICE_SESSION);

            return null;
        }

        $userId = (int) ($pending['user_id'] ?? 0);

        return $userId > 0 ? $userId : null;
    }

    private function isTrustedDevice(User $user, Request $request): bool
    {
        $raw = (string) $request->cookie(self::TRUSTED_DEVICE_COOKIE, '');
        if ($raw === '') {
            return false;
        }

        $decoded = base64_decode($raw, true);
        if ($decoded === false) {
            return false;
        }

        $payload = json_decode($decoded, true);
        if (! is_array($payload)) {
            return false;
        }

        return (int) ($payload['uid'] ?? 0) === (int) $user->getKey()
            && (int) ($payload['exp'] ?? 0) > now()->timestamp
            && hash_equals((string) ($payload['ua'] ?? ''), $this->userAgentHash((string) $request->userAgent()));
    }

    private function looksAutomated(Request $request): bool
    {
        $ua = strtolower(trim((string) $request->userAgent()));
        if ($ua === '') {
            return true;
        }

        foreach ([
            'curl/',
            'wget/',
            'python-requests',
            'python-httpx',
            'aiohttp',
            'scrapy',
            'selenium',
            'phantomjs',
            'headlesschrome',
            'playwright',
            'puppeteer',
            'libwww-perl',
            'go-http-client',
            'java/',
            'okhttp/',
            'postmanruntime/',
            'insomnia/',
            'httpie/',
            'axios/',
            'node-fetch',
            'undici',
            'crawler',
            'spider',
            'scanner',
            'scraper',
            'bot/',
            ' bot',
        ] as $needle) {
            if (str_contains($ua, $needle)) {
                return true;
            }
        }

        if (! $request->isMethod('POST')) {
            return false;
        }

        $score = 0;

        if (trim((string) $request->header('Origin', '')) === ''
            && trim((string) $request->header('Referer', '')) === '') {
            $score += 2;
        }

        if (trim((string) $request->header('Accept', '')) === '') {
            $score++;
        }

        if (trim((string) $request->header('Accept-Language', '')) === '') {
            $score++;
        }

        if (! str_contains($ua, 'mozilla/')
            && ! str_contains($ua, 'applewebkit/')
            && ! str_contains($ua, 'gecko/')
            && ! str_contains($ua, 'chrome/')
            && ! str_contains($ua, 'safari/')
            && ! str_contains($ua, 'firefox/')
            && ! str_contains($ua, 'edg/')) {
            $score += 2;
        }

        return $score >= 3;
    }

    private function isTorExitNode(string $ip): bool
    {
        foreach ($this->remoteList(
            'login-security:tor-exits',
            self::TOR_LIST_URL,
            'network',
            20,
        ) as $entry) {
            if ($this->ipMatches($ip, $entry)) {
                return true;
            }
        }

        return false;
    }

    private function isVpnOrProxyAddress(string $ip): bool
    {
        foreach ($this->remoteList(
            'login-security:vpn-networks',
            self::VPN_LIST_URL,
            'network',
            100,
        ) as $entry) {
            if ($this->ipMatches($ip, $entry)) {
                return true;
            }
        }

        $loadedProxySource = false;
        $lastException = null;

        foreach (self::PROXY_LIST_URLS as $index => $url) {
            try {
                $entries = $this->remoteList(
                    'login-security:proxy-list:'.$index,
                    $url,
                    'proxy',
                    20,
                );
                $loadedProxySource = true;

                foreach ($entries as $entry) {
                    if ($this->ipMatches($ip, $entry)) {
                        return true;
                    }
                }
            } catch (RuntimeException $exception) {
                $lastException = $exception;
            }
        }

        if (! $loadedProxySource && $lastException) {
            throw $lastException;
        }

        return false;
    }

    private function remoteList(
        string $cacheKey,
        string $url,
        string $format = 'network',
        int $minimumEntries = 1,
    ): array {
        $freshKey = $cacheKey.':fresh';
        $staleKey = $cacheKey.':last-good';

        $cached = Cache::get($freshKey);
        if (is_array($cached) && count($cached) >= $minimumEntries) {
            return $cached;
        }

        try {
            $response = Http::connectTimeout(2)
                ->timeout(5)
                ->retry(2, 150)
                ->withHeaders([
                    'Accept' => 'text/plain,*/*',
                    'User-Agent' => 'Ografi-Login-Security/2.0',
                ])
                ->get($url);

            if (! $response->successful()) {
                throw new RuntimeException('Risk list HTTP '.$response->status().' for '.$url);
            }

            $entries = collect(preg_split('/\r\n|\r|\n/', $response->body()) ?: [])
                ->map(fn ($line) => $this->normalizeRiskListEntry((string) $line, $format))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (count($entries) < $minimumEntries) {
                throw new RuntimeException(
                    'Risk list returned too few valid entries ('.count($entries).') for '.$url,
                );
            }

            Cache::put($freshKey, $entries, now()->addHours(6));
            Cache::put($staleKey, $entries, now()->addDays(7));

            return $entries;
        } catch (Throwable $exception) {
            $stale = Cache::get($staleKey);

            if (is_array($stale) && count($stale) >= $minimumEntries) {
                Log::warning('Login security is using a stale risk list.', [
                    'url' => $url,
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

    private function normalizeRiskListEntry(string $line, string $format): string
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

        if (str_contains($line, '/')) {
            [$network, $prefix] = array_pad(explode('/', $line, 2), 2, null);

            if (filter_var($network, FILTER_VALIDATE_IP) !== false
                && filter_var($prefix, FILTER_VALIDATE_INT) !== false) {
                $bits = (int) $prefix;
                $max = str_contains($network, ':') ? 128 : 32;

                if ($bits >= 0 && $bits <= $max) {
                    return $network.'/'.$bits;
                }
            }
        }

        return '';
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

    private function userAgentHash(string $userAgent): string
    {
        return hash('sha256', strtolower(trim($userAgent)));
    }

    private function deviceLabel(string $userAgent): string
    {
        $ua = strtolower($userAgent);
        $device = match (true) {
            str_contains($ua, 'iphone') => 'iPhone',
            str_contains($ua, 'ipad') => 'iPad',
            str_contains($ua, 'android') => 'Android',
            str_contains($ua, 'windows') => 'Windows',
            str_contains($ua, 'macintosh'), str_contains($ua, 'mac os') => 'macOS',
            str_contains($ua, 'linux') => 'Linux',
            default => 'Bilinmeyen cihaz',
        };

        $browser = match (true) {
            str_contains($ua, 'edg/') => 'Edge',
            str_contains($ua, 'firefox/') => 'Firefox',
            str_contains($ua, 'chrome/') || str_contains($ua, 'crios/') => 'Chrome',
            str_contains($ua, 'safari/') => 'Safari',
            default => 'Tarayıcı',
        };

        return $device.' · '.$browser;
    }

    private function block(Request $request, string $reason, string $message, ?string $ip = null): never
    {
        Log::notice('Login security blocked a request.', [
            'reason' => $reason,
            'ip' => $ip ?: $this->clientIp($request),
            'path' => $request->path(),
            'method' => $request->method(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);

        $this->reject($message);
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages([
            'email' => $message,
        ]);
    }
}
