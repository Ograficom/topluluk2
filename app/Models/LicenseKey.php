<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseKey extends Model
{
    protected $table = 'license_keys';

    protected $guarded = [];

    protected $casts = [
        'activated_at' => 'datetime',
        'max_domains' => 'integer',
    ];

    /**
     * Check if a given license key (plaintext) is valid and active.
     */
    public static function isValid(string $plainKey): bool
    {
        $hash = hash('sha256', $plainKey);

        return self::where('key_hash', $hash)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Activate a license key for a domain and return activation data.
     * Returns ['success' => false, 'message' => ...] or ['success' => true, 'key' => LicenseKey].
     */
    public static function activate(string $plainKey, string $domain): array
    {
        $hash = hash('sha256', $plainKey);

        $license = self::where('key_hash', $hash)->first();

        if (! $license) {
            return ['success' => false, 'message' => 'Invalid license key — not found in the system.'];
        }

        if ($license->status !== 'active') {
            return ['success' => false, 'message' => "License key is {$license->status}."];
        }

        // Check if already activated for this domain
        if ($license->domain === $domain) {
            return [
                'success' => true,
                'key' => $license,
                'already_activated' => true,
            ];
        }

        // Check if already activated for a different domain
        if ($license->domain && $license->domain !== $domain && $license->max_domains <= 1) {
            return ['success' => false, 'message' => 'This license key is already activated on another domain.'];
        }

        $license->update([
            'activated_at' => now(),
            'domain' => $domain,
        ]);

        return [
            'success' => true,
            'key' => $license,
            'already_activated' => false,
        ];
    }

    /**
     * Create a new license key, return the plaintext key.
     */
    public static function generate(string $label = null, int $maxDomains = 1): string
    {
        $plainKey = 'ALMA-' . strtoupper(substr(bin2hex(random_bytes(12)), 0, 8))
            . '-' . strtoupper(substr(bin2hex(random_bytes(8)), 0, 8))
            . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        self::create([
            'key_hash' => hash('sha256', $plainKey),
            'label' => $label,
            'status' => 'active',
            'max_domains' => $maxDomains,
        ]);

        return $plainKey;
    }
}
