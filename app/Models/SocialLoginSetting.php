<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLoginSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_enabled',
        'google_enabled',
        'google_client_id',
        'google_client_secret',
        'google_redirect_url',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'google_enabled' => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'is_enabled' => false,
            'google_enabled' => false,
        ]);
    }

    public function isProviderEnabled(string $provider): bool
    {
        if (!$this->is_enabled) {
            return false;
        }

        return match ($provider) {
            'google' => (bool) $this->google_enabled,
            default => false,
        };
    }

    public function providerConfig(string $provider): array
    {
        $provider = strtolower($provider);
        $config = (array) config("services.{$provider}");
        $defaultRedirect = route('social.callback', $provider);

        return match ($provider) {
            'google' => [
                'client_id' => $this->google_client_id ?: ($config['client_id'] ?? null),
                'client_secret' => $this->google_client_secret ?: ($config['client_secret'] ?? null),
                'redirect' => $this->resolveRedirectUrl(
                    $this->google_redirect_url ?: ($config['redirect'] ?? null),
                    $defaultRedirect
                ),
            ],
            default => [],
        };
    }

    private function resolveRedirectUrl(?string $configuredRedirect, string $defaultRedirect): string
    {
        if (blank($configuredRedirect)) {
            return $defaultRedirect;
        }

        if (!app()->environment('local')) {
            return $configuredRedirect;
        }

        $appUrl = (string) config('app.url');
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        $appScheme = parse_url($appUrl, PHP_URL_SCHEME);
        $configuredHost = parse_url($configuredRedirect, PHP_URL_HOST);
        $configuredScheme = parse_url($configuredRedirect, PHP_URL_SCHEME);

        if ($appHost && $configuredHost && ($appHost !== $configuredHost || $appScheme !== $configuredScheme)) {
            return $defaultRedirect;
        }

        return $configuredRedirect;
    }
}
