<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Network security failure mode
    |--------------------------------------------------------------------------
    |
    | true: if live intelligence and the local fallback lists cannot be
    | verified (with no last-known-good cache), block the request instead of
    | silently allowing an unverifiable network.
    |
    */
    'fail_closed' => (bool) env('LOGIN_SECURITY_FAIL_CLOSED', true),

    /*
    |--------------------------------------------------------------------------
    | Site-wide enforcement
    |--------------------------------------------------------------------------
    |
    | These switches are intentionally independent from the old login/admin
    | security toggles. The global middleware uses these values for every web
    | request, so disabling a login-specific toggle cannot bypass the site-wide
    | VPN/Tor policy.
    |
    */
    'global_block_vpn' => (bool) env('LOGIN_SECURITY_GLOBAL_BLOCK_VPN', true),
    'global_block_tor' => (bool) env('LOGIN_SECURITY_GLOBAL_BLOCK_TOR', true),

    /*
    |--------------------------------------------------------------------------
    | IPQualityScore Proxy & VPN Detection API
    |--------------------------------------------------------------------------
    */
    'ipqs' => [
        'enabled' => (bool) env('LOGIN_SECURITY_IPQS_ENABLED', true),
        'api_key' => (string) env('IPQS_API_KEY', ''),
        'base_url' => rtrim((string) env('IPQS_BASE_URL', 'https://ipqualityscore.com/api/json/ip'), '/'),
        'strictness' => max(0, min(3, (int) env('LOGIN_SECURITY_IPQS_STRICTNESS', 1))),
        'allow_public_access_points' => (bool) env('LOGIN_SECURITY_IPQS_ALLOW_PUBLIC_ACCESS_POINTS', true),
        'timeout_seconds' => max(2, min(15, (int) env('LOGIN_SECURITY_IPQS_TIMEOUT', 6))),
    ],

    /*
    |--------------------------------------------------------------------------
    | ProxyCheck live secondary intelligence
    |--------------------------------------------------------------------------
    |
    | ProxyCheck can work without an API key with a small daily allowance.
    | When IPQS has no key, this provides a live second opinion after the local
    | threat lists. A free ProxyCheck key raises the daily allowance further.
    |
    */
    'proxycheck' => [
        'enabled' => (bool) env('LOGIN_SECURITY_PROXYCHECK_ENABLED', true),
        'api_key' => (string) env('PROXYCHECK_API_KEY', ''),
        'base_url' => rtrim((string) env('PROXYCHECK_BASE_URL', 'https://proxycheck.io/v2'), '/'),
        'timeout_seconds' => max(2, min(15, (int) env('LOGIN_SECURITY_PROXYCHECK_TIMEOUT', 6))),
        'keyless_daily_limit' => max(1, min(100, (int) env('LOGIN_SECURITY_PROXYCHECK_KEYLESS_DAILY_LIMIT', 90))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-IP decision cache
    |--------------------------------------------------------------------------
    |
    | Cache IP intelligence decisions so every page view does not consume an
    | external API request. Twelve hours is inside the desired 6-24 hour range.
    |
    */
    'decision_cache_hours' => max(6, min(24, (int) env('LOGIN_SECURITY_DECISION_CACHE_HOURS', 12))),

    /*
    |--------------------------------------------------------------------------
    | Data center / hosting policy
    |--------------------------------------------------------------------------
    */
    'block_datacenter' => (bool) env('LOGIN_SECURITY_BLOCK_DATACENTER', true),

    /*
    |--------------------------------------------------------------------------
    | Local threat-list cache
    |--------------------------------------------------------------------------
    */
    'list_fresh_hours' => max(1, min(12, (int) env('LOGIN_SECURITY_LIST_FRESH_HOURS', 2))),
    'list_stale_days' => max(1, min(30, (int) env('LOGIN_SECURITY_LIST_STALE_DAYS', 7))),
];
