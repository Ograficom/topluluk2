<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class RecaptchaSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_enabled',
        'login_enabled',
        'register_enabled',
        'comment_enabled',
        'minimum_score',
        'verify_action',
        'site_key',
        'secret_key',
        'allowed_hostnames',
        'block_vpn_logins',
        'block_tor_logins',
        'verify_unknown_devices',
        'trusted_device_days',
        'bot_honeypot_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'login_enabled' => 'boolean',
        'register_enabled' => 'boolean',
        'comment_enabled' => 'boolean',
        'verify_action' => 'boolean',
        'minimum_score' => 'decimal:2',
        'block_vpn_logins' => 'boolean',
        'block_tor_logins' => 'boolean',
        'verify_unknown_devices' => 'boolean',
        'trusted_device_days' => 'integer',
        'bot_honeypot_enabled' => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'is_enabled' => false,
            'login_enabled' => true,
            'register_enabled' => true,
            'comment_enabled' => true,
            'minimum_score' => 0.50,
            'verify_action' => true,
            'block_vpn_logins' => true,
            'block_tor_logins' => true,
            'verify_unknown_devices' => true,
            'trusted_device_days' => 90,
            'bot_honeypot_enabled' => true,
        ]);
    }

    public static function currentOrNull(): ?self
    {
        if (!Schema::hasTable('recaptcha_settings')) {
            return null;
        }

        return static::current();
    }

    public function resolvedSiteKey(): ?string
    {
        return $this->site_key ?: (string) (config('services.recaptcha.site_key') ?? null);
    }

    public function resolvedSecretKey(): ?string
    {
        return $this->secret_key ?: (string) (config('services.recaptcha.secret_key') ?? null);
    }

    public function isReady(): bool
    {
        return $this->is_enabled && !empty($this->resolvedSiteKey()) && !empty($this->resolvedSecretKey());
    }

    public function isEnabledFor(string $context): bool
    {
        if (!$this->isReady()) {
            return false;
        }

        return match ($context) {
            'login' => (bool) $this->login_enabled,
            'register' => (bool) $this->register_enabled,
            'comment' => (bool) $this->comment_enabled,
            default => false,
        };
    }

    public function allowedHostnamesList(): array
    {
        $value = trim((string) ($this->allowed_hostnames ?? ''));
        if ($value === '') {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
