<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Author Information
    |--------------------------------------------------------------------------
    |
    | The details of the script author.
    |
     */

    'author' => [
        'name' => 'Alma Team',
        'support' => '',
        'website' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Item Information
    |--------------------------------------------------------------------------
    |
    | Information about the item.
    |
     */

    'item' => [
        'name' => 'Alma Pro',
        'type' => 'pro',
        'version' => 'Pro',
        'pro_features' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pro Features
    |--------------------------------------------------------------------------
    |
    | Enable or disable the Pro features.
    |
     */
    'pro' => [
        'direct_messages' => true,
        'kyc_verification' => true,
        'activity_logs' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo Mode
    |--------------------------------------------------------------------------
    |
    | Enable or disable the system demo mode.
    |
     */

    'demo_mode' => env('DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Progressive Web App
    |--------------------------------------------------------------------------
    |
    | Enable or disable the PWA.
    |
     */
    'pwa_active' => true,

    /*
    |--------------------------------------------------------------------------
    | Cookie Consent
    |--------------------------------------------------------------------------
    |
    | Enable or disable the Cookie.
    |
     */
    'cookie_active' => true,

    /*
    |--------------------------------------------------------------------------
    | Auto-approval of posts
    |--------------------------------------------------------------------------
    |
    | Enable or disable auto-approval of posts.
    |
     */
    'posts_auto_approval' => true,

    /*
    |--------------------------------------------------------------------------
    | Registeration in the system
    |--------------------------------------------------------------------------
    |
    | Enable or disable registeration.
    |
     */
    'registration' => false,

    /*
    |--------------------------------------------------------------------------
    | Appearance
    |--------------------------------------------------------------------------
    |
    | The appearance data configiration.
    |
     */
    'default_feed' => 'popular',
    'appearance' => [
        'default_font' => 'Roboto',
        'theme' => 'emerald',
        'header_color' => '#d9f0ff',
        'radius' => '0.5',
        'show_views_count' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode
    |--------------------------------------------------------------------------
    |
    | The system maintenance mode data.
    |
     */
    'maintenance' => [
        'title' => '',
        'message' => '',
        'secret' => 'fab750a1-2b0nd-4098-8d7b-9b132500a84a',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cronjob
    |--------------------------------------------------------------------------
    |
    | The cronjob data.
    |
     */

    'cronjob' => [
        'key' => '',
        'last_execution' => null,
    ],

    /*
   |--------------------------------------------------------------------------
   | Legacy
   |--------------------------------------------------------------------------
   |
   | The legacy data configiration.
   |
    */
    'common_images_directory_name' => env('COMMON_IMAGES', 'media'),

    'social_profile_links' => [
        'facebook' => '',
        'twitter_x' => '',
        'instagram' => '',
        'tiktok' => '',
        'twitch' => '',
        'vk' => '',
        'discord' => '',
        'telegram' => '',
    ],
];
