<?php

declare(strict_types=1);

return [
    'enabled' => env('FILAMENT_CONNECTION_BADGE_ENABLED', true),
    'render_hook' => env('FILAMENT_CONNECTION_BADGE_RENDER_HOOK', 'panels::user-menu.before'),
    'permission' => env('FILAMENT_CONNECTION_BADGE_PERMISSION'),
    'show_label' => env('FILAMENT_CONNECTION_BADGE_SHOW_LABEL', true),
    'show_overlay' => env('FILAMENT_CONNECTION_BADGE_SHOW_OVERLAY', false),

    'route' => [
        'prefix' => '_filament-connection-badge',
        'middleware' => ['web'],
        'throttle' => env('FILAMENT_CONNECTION_BADGE_THROTTLE'),
    ],

    'ping_url' => env('FILAMENT_CONNECTION_BADGE_PING_URL'),
    'ping_interval' => env('FILAMENT_CONNECTION_BADGE_PING_INTERVAL', 5000),

    'thresholds' => [
        'full' => env('FILAMENT_CONNECTION_BADGE_FULL_THRESHOLD', 350),
        'medium' => env('FILAMENT_CONNECTION_BADGE_MEDIUM_THRESHOLD', 1200),
    ],

    'max_samples' => env('FILAMENT_CONNECTION_BADGE_MAX_SAMPLES', 30),
];
