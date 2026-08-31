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
use Throwable;

class LoginSecurityService
{
    private const TRUSTED_DEVICE_COOKIE = 'ografi_trusted_device';
    private const PENDING_DEVICE_SESSION = 'ografi_login_device_pending';
    private const TOR_LIST_URL = 'https://check.torproject.org/torbulkexitlist';
    private const VPN_LIST_URL = 'https://raw.githubusercontent.com/X4BNet/lists_vpn/main/output/vpn/ipv4.txt';

    public function assertRequestAllowed(Request $request, ?RecaptchaSetting $settings = null): void
    {
        $settings ??= RecaptchaSetting::currentOrNull();

        if (($settings?->bot_honeypot_enabled ?? true) && trim((string) $request->input('website', '')) !== '') {
            $this->reject('Giriş doğrulanamadı.');
        }

        if (($settings?->bot_honeypot_enabled ?? true) && $this->looksAutomated($request)) {
            $this->reject('Otomatik giriş isteği engellendi.');
        }

        $ip = (string) $request->ip();
        if (! $this->isPublicIp($ip)) {
            return;
        }

        if (($settings?->block_tor_logins ?? true) && $this->isTorExitNode($ip)) {
            $this->reject('Tor ağı üzerinden girişe izin verilmiyor.');
        }

        if (($settings?->block_vpn_logins ?? true) && $this->isVpnAddress($ip)) {
            $this->reject('VPN veya proxy üzerinden girişe izin verilmiyor.');
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
            $ip = (string) $request->ip();
            $body = "Ografi hesabına bilinmeyen bir cihazdan giriş deneniyor.\n\n"
                . "Doğrulama kodun: {$code}\n\n"
                . "Cihaz: {$device}\n"
                . "IP: {$ip}\n\n"
                . "Kod 10 dakika geçerlidir. Bu giriş sana ait değilse kodu kimseyle paylaşma.";

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
        $pending = $request->session()->get(self::PENDING_DEVICE_SESSION);

        return is_array($pending) && (int) ($pending['expires_at'] ?? 0) > now()->timestamp;
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
            'scrapy',
            'selenium',
            'phantomjs',
            'headlesschrome',
            'playwright',
            'puppeteer',
            'libwww-perl',
            'go-http-client',
        ] as $needle) {
            if (str_contains($ua, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function isTorExitNode(string $ip): bool
    {
        foreach ($this->remoteList('login-security:tor-exits', self::TOR_LIST_URL) as $entry) {
            if ($this->ipMatches($ip, $entry)) {
                return true;
            }
        }

        return false;
    }

    private function isVpnAddress(string $ip): bool
    {
        foreach ($this->remoteList('login-security:vpn-networks', self::VPN_LIST_URL) as $entry) {
            if ($this->ipMatches($ip, $entry)) {
                return true;
            }
        }

        return false;
    }

    private function remoteList(string $cacheKey, string $url): array
    {
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        try {
            $response = Http::timeout(3)
                ->retry(1, 150)
                ->withHeaders(['User-Agent' => 'Ografi-Login-Security/1.0'])
                ->get($url);

            if (! $response->successful()) {
                throw new \RuntimeException('Risk list HTTP '.$response->status());
            }

            $entries = collect(preg_split('/\r\n|\r|\n/', $response->body()) ?: [])
                ->map(fn ($line) => trim((string) preg_replace('/\s+#.*$/', '', (string) $line)))
                ->filter(fn ($line) => $line !== '' && ! str_starts_with($line, '#'))
                ->unique()
                ->values()
                ->all();

            Cache::put($cacheKey, $entries, now()->addHours(6));

            return $entries;
        } catch (Throwable $exception) {
            Log::warning('Login network risk list could not be refreshed.', [
                'url' => $url,
                'message' => $exception->getMessage(),
            ]);

            Cache::put($cacheKey, [], now()->addMinutes(10));

            return [];
        }
    }

    private function ipMatches(string $ip, string $range): bool
    {
        $range = trim($range);
        if ($range === '') {
            return false;
        }

        if (! str_contains($range, '/')) {
            return hash_equals($range, $ip);
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

    private function reject(string $message): never
    {
        throw ValidationException::withMessages([
            'email' => $message,
        ]);
    }
}
