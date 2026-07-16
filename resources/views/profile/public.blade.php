@extends('layouts.app')

@section('title', ($user->name ?? __('site.profile_page.title_suffix')) . ' - ' . __('site.profile_page.title_suffix'))
@section('meta_description', __('site.profile_page.meta_description'))
@section('hide_feed_header')
@endsection
@section('no_container_padding')
@endsection

@php
    $profilePageUrl = route('users.show', $user);
    $profileEntityId = $profilePageUrl . '#profile';
    $profileName = trim((string) ($user->name ?? ''));
    $profileAlternate = trim((string) ($user->username ?? ''));
    if ($profileName === '' && $profileAlternate !== '') {
        $profileName = $profileAlternate;
    }
    if ($profileName === '') {
        $profileName = __('site.profile_page.fallback_name');
    }
    $profileType = strtolower((string) ($user->profile_type ?? 'person'));
    if (!in_array($profileType, ['person', 'organization'], true)) {
        $profileType = 'person';
    }
    $profileEntityType = $profileType === 'organization' ? 'Organization' : 'Person';

    $profileCreated = $user->joined_at ?? $user->created_at ?? null;
    $profileModified = $user->updated_at ?? null;

    $profileImageForSchema = null;
    if (!empty($user->profile_photo_path)) {
        $profileImageForSchema = $user->profile_photo_url;
    } elseif (!empty($user->profile_photo_url) && !str_contains($user->profile_photo_url, 'placehold.co')) {
        $profileImageForSchema = $user->profile_photo_url;
    }
    $profileDescription = trim((string) ($user->bio ?? ''));
    if ($profileDescription === '') {
        $profileDescription = __('site.profile_page.meta_description');
    }
    if ($profileImageForSchema) {
        $profileImageForSchema = [
            '@type' => 'ImageObject',
            'url' => $profileImageForSchema,
            'caption' => $profileName . ' profil fotoğrafı',
        ];
    }

    $sameAs = [];
    $addUrl = function (?string $value) use (&$sameAs) {
        $value = trim((string) $value);
        if ($value === '') {
            return;
        }
        if (!str_starts_with($value, 'http://') && !str_starts_with($value, 'https://')) {
            $value = 'https://' . ltrim($value, '/');
        }
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $sameAs[] = $value;
        }
    };

    $addUrl($user->website_url ?? null);
    $addUrl($user->social_facebook ?? null);
    $addUrl($user->social_instagram ?? null);
    $addUrl($user->social_x ?? null);
    $addUrl($user->social_tiktok ?? null);
    $addUrl($user->social_whatsapp ?? null);
    $sameAs = array_values(array_unique($sameAs));

    $interactionStats = [];
    if (isset($user->followers_count)) {
        $interactionStats[] = [
            '@type' => 'InteractionCounter',
            'interactionType' => 'https://schema.org/FollowAction',
            'userInteractionCount' => (int) $user->followers_count,
        ];
    }
    if (isset($postsCount)) {
        $interactionStats[] = [
            '@type' => 'InteractionCounter',
            'interactionType' => 'https://schema.org/WriteAction',
            'userInteractionCount' => (int) $postsCount,
        ];
    }

    $agentStats = [];
    if (isset($user->followings_count)) {
        $agentStats[] = [
            '@type' => 'InteractionCounter',
            'interactionType' => 'https://schema.org/FollowAction',
            'userInteractionCount' => (int) $user->followings_count,
        ];
    }

    $postsCollection = $posts ?? collect();
    if (is_object($postsCollection) && method_exists($postsCollection, 'getCollection')) {
        $postsCollection = $postsCollection->getCollection();
    }

    $recentParts = collect($postsCollection)->take(5)->map(function ($post) use ($profileEntityId) {
        $postUrl = null;
        if (!empty($post->slug)) {
            $postUrl = route('blog.post', $post);
        }

        $publishedAt = $post->published_at ?? $post->created_at ?? null;
        $publishedIso = null;
        if ($publishedAt) {
            try {
                $publishedIso = \Illuminate\Support\Carbon::parse($publishedAt)->toIso8601String();
            } catch (\Throwable $e) {
                $publishedIso = null;
            }
        }

        $headline = trim((string) ($post->title ?? ''));
        if ($postUrl === null || $headline === '') {
            return null;
        }

        $data = [
            '@type' => 'Article',
            'headline' => $headline,
            'url' => $postUrl,
            'author' => [
                '@id' => $profileEntityId,
            ],
        ];

        if ($publishedIso) {
            $data['datePublished'] = $publishedIso;
        }

        return $data;
    })->filter()->values();

    $profileEntity = [
        '@id' => $profileEntityId,
        '@type' => $profileEntityType,
        'name' => $profileName,
        'url' => $profilePageUrl,
    ];

    if ($profileAlternate !== '' && $profileAlternate !== $profileName) {
        $profileEntity['alternateName'] = $profileAlternate;
    }
    if (!empty($user->id)) {
        $profileEntity['identifier'] = (string) $user->id;
    }
    if (!empty($user->bio)) {
        $profileEntity['description'] = (string) $user->bio;
    }
    if ($profileImageForSchema) {
        if ($profileEntityType === 'Organization') {
            $profileEntity['logo'] = $profileImageForSchema;
        } else {
            $profileEntity['image'] = $profileImageForSchema;
        }
    }
    if (!empty($sameAs)) {
        $profileEntity['sameAs'] = $sameAs;
    }
    if (!empty($interactionStats)) {
        $profileEntity['interactionStatistic'] = $interactionStats;
    }
    if (!empty($agentStats)) {
        $profileEntity['agentInteractionStatistic'] = $agentStats;
    }

    $profileSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'ProfilePage',
        'mainEntity' => $profileEntity,
    ];
    if ($profileCreated) {
        $profileSchema['dateCreated'] = $profileCreated->toIso8601String();
    }
    if ($profileModified) {
        $profileSchema['dateModified'] = $profileModified->toIso8601String();
    }
    if ($recentParts->isNotEmpty()) {
        $profileSchema['hasPart'] = $recentParts->all();
    }

    $profileItemList = null;
    if ($postsCollection && collect($postsCollection)->isNotEmpty()) {
        $postItems = collect($postsCollection)->map(function ($post, $index) use ($profileEntityId) {
            $title = trim((string) ($post->title ?? ''));
            $slug = $post->slug ?? null;
            if ($title === '' || !$slug) {
                return null;
            }
            return [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'item' => [
                    '@type' => 'BlogPosting',
                    'headline' => $title,
                    'url' => route('blog.post', $post),
                    'author' => [
                        '@id' => $profileEntityId,
                    ],
                ],
            ];
        })->filter()->values();

        if ($postItems->isNotEmpty()) {
            $profileItemList = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'itemListElement' => $postItems->all(),
            ];
        }
    }
@endphp

@push('head')
<meta property="og:type" content="profile">
<meta property="og:title" content="{{ e($profileName) }} - {{ e(config('app.name', 'Ografi')) }}">
<meta property="og:description" content="{{ e($profileDescription) }}">
<meta property="og:url" content="{{ e($profilePageUrl) }}">
@if($profileImageForSchema)
    <meta property="og:image" content="{{ e($profileImageForSchema['url']) }}">
    <meta property="og:image:alt" content="{{ e($profileName . ' profil fotoğrafı') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ e($profileImageForSchema['url']) }}">
    <meta name="twitter:image:alt" content="{{ e($profileName . ' profil fotoğrafı') }}">
@endif
<meta name="twitter:title" content="{{ e($profileName) }} - {{ e(config('app.name', 'Ografi')) }}">
<meta name="twitter:description" content="{{ e($profileDescription) }}">
<script type="application/ld+json">
{!! json_encode($profileSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@if($profileItemList)
<script type="application/ld+json">
{!! json_encode($profileItemList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif

<style>
    /* =========================================================
       OGrafi profil sayfası - LinkedIn profil düzenine yakın temiz sürüm
       Önceki çakışan profil CSS blokları tek sürüme indirildi.
       ========================================================= */

    body:has(.og-profile-page) {
        --og-profile-width: 782px;
        --og-li-bg: #f3f2ef;
        --og-li-card: #ffffff;
        --og-li-text: rgba(0, 0, 0, 0.90);
        --og-li-muted: rgba(0, 0, 0, 0.60);
        --og-li-soft: rgba(0, 0, 0, 0.08);
        --og-li-line: #e0dfdc;
        --og-li-blue: #0a66c2;
        --og-li-blue-hover: #004182;
        --og-li-pill-hover: rgba(10, 102, 194, 0.10);
        --og-li-hover: rgba(0, 0, 0, 0.04);
        background: var(--og-li-bg) !important;
    }

    body:has(.og-profile-page) .main-grid {
        max-width: 1188px !important;
        padding-top: 24px !important;
        column-gap: 24px !important;
        align-items: flex-start !important;
        grid-template-columns: var(--layout-left-width, 220px) minmax(0, var(--og-profile-width)) var(--layout-right-width, 304px) !important;
    }

    body:has(.og-profile-page) .layout-main {
        width: 100% !important;
        max-width: var(--og-profile-width) !important;
    }

    .og-profile-page,
    .og-profile-page * {
        box-sizing: border-box !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important;
    }

    .og-profile-page {
        width: 100% !important;
        padding: 0 0 40px !important;
        color: var(--og-li-text) !important;
    }

    .og-profile-wrap {
        width: 100% !important;
        max-width: var(--og-profile-width) !important;
        margin: 0 auto !important;
    }

    .og-card,
    .og-tabs-card,
    .og-list-card,
    .og-empty,
    .og-post-wrapper [data-post-card-shell] {
        border: 1px solid var(--og-li-line) !important;
        border-radius: 8px !important;
        background: var(--og-li-card) !important;
        box-shadow: none !important;
    }

    .og-card {
        overflow: hidden !important;
    }

    .og-cover {
        position: relative !important;
        height: 196px !important;
        overflow: hidden !important;
        border-radius: 8px 8px 0 0 !important;
        background:
            radial-gradient(circle at 18% 28%, rgba(255, 255, 255, 0.50), transparent 22%),
            linear-gradient(135deg, #66a8df 0%, #2777b8 43%, #b4d2e9 100%) !important;
    }

    .og-cover::after {
        content: "" !important;
        position: absolute !important;
        inset: 0 !important;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.02), rgba(0, 0, 0, 0.07)) !important;
        pointer-events: none !important;
    }

    .og-cover img {
        display: block !important;
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        object-position: center !important;
    }

    .og-body {
        position: relative !important;
        padding: 0 24px 24px !important;
        background: var(--og-li-card) !important;
    }

    .og-topline {
        display: flex !important;
        align-items: flex-start !important;
        justify-content: space-between !important;
        gap: 16px !important;
        min-height: 78px !important;
    }

    .og-avatar-button {
        display: inline-flex !important;
        margin-top: -76px !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 999px !important;
        background: transparent !important;
        cursor: pointer !important;
        flex: 0 0 auto !important;
    }

    .og-avatar {
        display: inline-flex !important;
        width: 152px !important;
        height: 152px !important;
        align-items: center !important;
        justify-content: center !important;
        overflow: hidden !important;
        border: 4px solid var(--og-li-card) !important;
        border-radius: 999px !important;
        background: #d9e5ef !important;
        color: #0a66c2 !important;
        font-size: 54px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        box-shadow: none !important;
    }

    .og-avatar img {
        display: block !important;
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    .og-actions {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
        padding-top: 16px !important;
        min-width: 0 !important;
    }

    .og-actions form {
        display: inline-flex !important;
        margin: 0 !important;
    }

    .og-btn,
    .og-icon-btn,
    .og-menu > summary,
    .og-chip,
    .og-social,
    .og-tab,
    .og-sort > summary,
    .og-sort-option,
    .og-menu-item,
    .og-list-link,
    .og-sheet-close,
    .og-sheet-action {
        transition: background-color 0.12s ease, color 0.12s ease, border-color 0.12s ease !important;
    }

    .og-btn,
    .og-icon-btn,
    .og-menu > summary {
        display: inline-flex !important;
        height: 32px !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        border-radius: 999px !important;
        padding: 0 16px !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        white-space: nowrap !important;
        text-decoration: none !important;
        cursor: pointer !important;
    }

    .og-btn {
        min-width: 88px !important;
        border: 1px solid var(--og-li-blue) !important;
        background: var(--og-li-blue) !important;
        color: #ffffff !important;
    }

    .og-btn:hover,
    .og-btn:focus-visible {
        background: var(--og-li-blue-hover) !important;
        border-color: var(--og-li-blue-hover) !important;
        color: #ffffff !important;
    }

    .og-icon-btn,
    .og-menu > summary {
        min-width: 0 !important;
        width: auto !important;
        border: 1px solid var(--og-li-muted) !important;
        background: transparent !important;
        color: var(--og-li-muted) !important;
    }

    .og-icon-btn:hover,
    .og-icon-btn:focus-visible,
    .og-menu > summary:hover,
    .og-menu > summary:focus-visible,
    .og-menu[open] > summary {
        border-color: rgba(0, 0, 0, 0.75) !important;
        background: var(--og-li-hover) !important;
        color: rgba(0, 0, 0, 0.75) !important;
    }

    .og-icon-btn svg,
    .og-menu > summary svg,
    .og-menu-item svg,
    .og-chip svg,
    .og-sheet-action svg {
        display: block !important;
        width: 18px !important;
        height: 18px !important;
        flex: 0 0 auto !important;
    }

    .og-menu {
        position: relative !important;
        display: inline-flex !important;
    }

    .og-menu > summary {
        list-style: none !important;
    }

    .og-menu > summary::-webkit-details-marker {
        display: none !important;
    }

    .og-menu-panel {
        position: absolute !important;
        top: calc(100% + 8px) !important;
        right: 0 !important;
        z-index: 70 !important;
        width: 264px !important;
        border: 1px solid var(--og-li-line) !important;
        border-radius: 8px !important;
        background: var(--og-li-card) !important;
        padding: 4px 0 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.16) !important;
    }

    .og-menu-panel form,
    .og-menu-item {
        margin: 0 !important;
    }

    .og-menu-item {
        display: flex !important;
        width: 100% !important;
        min-height: 40px !important;
        align-items: center !important;
        gap: 12px !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: var(--og-li-text) !important;
        padding: 0 16px !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        line-height: 1.25 !important;
        text-align: left !important;
        text-decoration: none !important;
        cursor: pointer !important;
    }

    .og-menu-item:hover,
    .og-menu-item:focus-visible {
        background: var(--og-li-hover) !important;
        color: var(--og-li-text) !important;
    }

    .og-identity {
        min-width: 0 !important;
        max-width: 100% !important;
        padding-top: 0 !important;
    }

    .og-name-row {
        display: flex !important;
        align-items: center !important;
        gap: 7px !important;
        min-width: 0 !important;
    }

    .og-name {
        margin: 0 !important;
        color: var(--og-li-text) !important;
        font-size: 24px !important;
        font-weight: 600 !important;
        line-height: 1.25 !important;
        letter-spacing: 0 !important;
    }

    .og-username {
        margin: 2px 0 0 !important;
        color: var(--og-li-muted) !important;
        font-size: 14px !important;
        font-weight: 400 !important;
        line-height: 1.35 !important;
    }

    .og-bio {
        max-width: 620px !important;
        margin: 6px 0 0 !important;
        color: var(--og-li-text) !important;
        font-size: 16px !important;
        font-weight: 400 !important;
        line-height: 1.38 !important;
    }

    .og-meta {
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 4px 8px !important;
        margin: 8px 0 0 !important;
        color: var(--og-li-muted) !important;
        font-size: 14px !important;
        font-weight: 400 !important;
        line-height: 1.35 !important;
    }

    .og-points {
        color: var(--og-li-blue) !important;
        font-weight: 600 !important;
    }

    .og-dot {
        width: 3px !important;
        height: 3px !important;
        border-radius: 999px !important;
        background: var(--og-li-muted) !important;
        opacity: 0.55 !important;
    }

    .og-stats {
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 4px 12px !important;
        margin-top: 8px !important;
    }

    .og-stat {
        color: var(--og-li-blue) !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        line-height: 1.35 !important;
        text-decoration: none !important;
    }

    .og-stat strong {
        color: inherit !important;
        font-weight: 600 !important;
    }

    .og-stat:hover,
    .og-stat:focus-visible {
        color: var(--og-li-blue-hover) !important;
        text-decoration: underline !important;
    }

    .og-chips,
    .og-links {
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 4px 10px !important;
        margin-top: 8px !important;
    }

    .og-chip {
        display: inline-flex !important;
        min-height: 0 !important;
        align-items: center !important;
        gap: 5px !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: var(--og-li-muted) !important;
        padding: 0 !important;
        font-size: 14px !important;
        font-weight: 400 !important;
        line-height: 1.35 !important;
        text-decoration: none !important;
    }

    .og-chip svg {
        width: 16px !important;
        height: 16px !important;
        color: var(--og-li-muted) !important;
    }

    a.og-chip,
    .og-chip--link {
        color: var(--og-li-blue) !important;
        font-weight: 600 !important;
    }

    a.og-chip:hover,
    a.og-chip:focus-visible {
        color: var(--og-li-blue-hover) !important;
        text-decoration: underline !important;
    }

    .og-social {
        display: inline-flex !important;
        width: auto !important;
        min-width: 30px !important;
        height: 30px !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1px solid var(--og-li-line) !important;
        border-radius: 999px !important;
        background: transparent !important;
        color: var(--og-li-muted) !important;
        padding: 0 9px !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        text-transform: uppercase !important;
    }

    .og-social:hover,
    .og-social:focus-visible {
        border-color: var(--og-li-blue) !important;
        background: var(--og-li-pill-hover) !important;
        color: var(--og-li-blue) !important;
    }

    .og-badges {
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
        margin-top: 16px !important;
    }

    .og-badge {
        --badge-color: #8fb8d8;
        --badge-rotate-x: 0deg;
        --badge-rotate-y: 0deg;
        --badge-front-scale: 1;
        --badge-back-scale: 1;
        --badge-front-opacity: 1;
        --badge-back-opacity: 0;
        --badge-light-left: 30%;
        --badge-light-opacity: .14;
        --badge-shadow-shift: 0px;
        --badge-highlight-scale: 1;
        position: relative !important;
        display: inline-flex !important;
        width: 44px !important;
        height: 44px !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1px solid rgba(0, 0, 0, 0.10) !important;
        border-radius: 999px !important;
        background:
            radial-gradient(circle at 30% 22%, rgba(255,255,255,.96), rgba(255,255,255,.14) 28%, transparent 42%),
            radial-gradient(circle at 50% 48%, var(--badge-color), color-mix(in srgb, var(--badge-color) 62%, #111827 38%)) !important;
        color: #ffffff !important;
        padding: 0 !important;
        cursor: grab !important;
        overflow: visible !important;
        isolation: isolate !important;
        user-select: none !important;
        touch-action: none !important;
        transform: perspective(900px) rotateX(var(--badge-rotate-x)) rotateY(var(--badge-rotate-y)) !important;
        transform-style: preserve-3d !important;
        box-shadow: none !important;
    }

    .og-badge::before {
        content: "" !important;
        position: absolute !important;
        inset: 3px !important;
        border-radius: 999px !important;
        background:
            linear-gradient(95deg, transparent 0%, rgba(255,255,255,var(--badge-light-opacity)) var(--badge-light-left), transparent 58%),
            radial-gradient(circle at 35% 26%, rgba(255,255,255,.55), transparent 28%) !important;
        transform: translateZ(10px) scale(var(--badge-highlight-scale)) !important;
        pointer-events: none !important;
        z-index: 3 !important;
    }

    .og-badge::after {
        content: "" !important;
        position: absolute !important;
        left: 6px !important;
        right: 6px !important;
        bottom: -6px !important;
        height: 8px !important;
        border-radius: 999px !important;
        background: rgba(0, 0, 0, 0.18) !important;
        filter: blur(5px) !important;
        transform: translateX(var(--badge-shadow-shift)) !important;
        z-index: -1 !important;
    }

    .og-badge:active,
    .og-badge.is-dragging {
        cursor: grabbing !important;
    }

    .og-badge:hover,
    .og-badge:focus-visible {
        outline: none !important;
        transform: perspective(900px) rotateX(var(--badge-rotate-x)) rotateY(var(--badge-rotate-y)) scale(1.04) !important;
    }

    .og-badge__face {
        position: relative !important;
        z-index: 2 !important;
        display: inline-flex !important;
        width: 78% !important;
        height: 78% !important;
        align-items: center !important;
        justify-content: center !important;
        overflow: hidden !important;
        border-radius: 999px !important;
        transform: translateZ(18px) scale(var(--badge-front-scale)) !important;
        opacity: var(--badge-front-opacity) !important;
        backface-visibility: hidden !important;
        background: rgba(255,255,255,.08) !important;
    }

    .og-badge__media,
    .og-badge img {
        position: relative !important;
        z-index: 4 !important;
        display: block !important;
        width: 78% !important;
        height: 78% !important;
        max-width: 78% !important;
        max-height: 78% !important;
        object-fit: contain !important;
        opacity: 1 !important;
        visibility: visible !important;
        mix-blend-mode: normal !important;
        filter: drop-shadow(0 2px 3px rgba(0,0,0,.18)) !important;
        background: transparent !important;
    }

    .og-badge__fallback {
        position: relative !important;
        z-index: 4 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 74% !important;
        height: 74% !important;
        border-radius: 999px !important;
        color: #ffffff !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        text-shadow: 0 1px 2px rgba(0,0,0,.24) !important;
    }

    .og-badge__fallback--backup {
        display: none !important;
    }

    .og-badge.has-image-error .og-badge__media {
        display: none !important;
    }

    .og-badge.has-image-error .og-badge__fallback--backup {
        display: inline-flex !important;
    }

    .og-tabs-card {
        position: relative !important;
        display: flex !important;
        min-height: 52px !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        margin-top: 8px !important;
        padding: 0 16px !important;
        overflow: visible !important;
    }

    .og-tabs {
        display: flex !important;
        align-items: center !important;
        gap: 4px !important;
        min-width: 0 !important;
        overflow-x: auto !important;
        scrollbar-width: none !important;
    }

    .og-tabs::-webkit-scrollbar {
        display: none !important;
    }

    .og-tab {
        display: inline-flex !important;
        height: 52px !important;
        align-items: center !important;
        justify-content: center !important;
        border-bottom: 2px solid transparent !important;
        color: var(--og-li-muted) !important;
        padding: 0 12px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        white-space: nowrap !important;
        text-decoration: none !important;
    }

    .og-tab:hover,
    .og-tab:focus-visible {
        background: var(--og-li-hover) !important;
        color: var(--og-li-text) !important;
    }

    .og-tab[aria-current="page"] {
        border-bottom-color: #057642 !important;
        color: #057642 !important;
    }

    .og-sort {
        position: relative !important;
        flex: 0 0 auto !important;
    }

    .og-sort > summary {
        display: inline-flex !important;
        height: 32px !important;
        align-items: center !important;
        gap: 6px !important;
        border-radius: 4px !important;
        color: var(--og-li-muted) !important;
        padding: 0 8px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        list-style: none !important;
        cursor: pointer !important;
    }

    .og-sort > summary::-webkit-details-marker {
        display: none !important;
    }

    .og-sort > summary:hover,
    .og-sort[open] > summary {
        background: var(--og-li-hover) !important;
        color: var(--og-li-text) !important;
    }

    .og-sort-panel {
        position: absolute !important;
        top: calc(100% + 8px) !important;
        right: 0 !important;
        z-index: 50 !important;
        min-width: 164px !important;
        border: 1px solid var(--og-li-line) !important;
        border-radius: 8px !important;
        background: var(--og-li-card) !important;
        padding: 4px 0 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.16) !important;
    }

    .og-sort-option {
        display: block !important;
        border-radius: 0 !important;
        color: var(--og-li-text) !important;
        padding: 10px 14px !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        text-decoration: none !important;
    }

    .og-sort-option:hover,
    .og-sort-option:focus-visible,
    .og-sort-option[aria-current="true"] {
        background: var(--og-li-hover) !important;
        color: var(--og-li-blue) !important;
    }

    .og-content {
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        margin-top: 8px !important;
    }

    .og-post-wrapper {
        width: 100% !important;
    }

    .og-post-wrapper [data-post-card-shell] {
        overflow: hidden !important;
    }

    .og-empty {
        padding: 32px 22px !important;
        color: var(--og-li-muted) !important;
        font-size: 14px !important;
        line-height: 1.45 !important;
        text-align: center !important;
    }

    .og-list-card {
        overflow: hidden !important;
    }

    .og-list-head {
        padding: 16px 20px !important;
        border-bottom: 1px solid var(--og-li-line) !important;
    }

    .og-list-title {
        margin: 0 !important;
        color: var(--og-li-text) !important;
        font-size: 20px !important;
        font-weight: 600 !important;
        line-height: 1.25 !important;
    }

    .og-list-desc {
        margin: 3px 0 0 !important;
        color: var(--og-li-muted) !important;
        font-size: 14px !important;
        line-height: 1.35 !important;
    }

    .og-list-link {
        display: flex !important;
        align-items: flex-start !important;
        gap: 12px !important;
        border-bottom: 1px solid var(--og-li-line) !important;
        color: inherit !important;
        padding: 12px 20px !important;
        text-decoration: none !important;
    }

    .og-list-link:last-child {
        border-bottom: 0 !important;
    }

    .og-list-link:hover,
    .og-list-link:focus-visible {
        background: var(--og-li-hover) !important;
    }

    .og-list-avatar {
        display: flex !important;
        width: 48px !important;
        height: 48px !important;
        flex: 0 0 48px !important;
        align-items: center !important;
        justify-content: center !important;
        overflow: hidden !important;
        border-radius: 999px !important;
        background: #d9e5ef !important;
        color: var(--og-li-blue) !important;
        font-size: 16px !important;
        font-weight: 600 !important;
    }

    .og-list-avatar img {
        display: block !important;
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    .og-list-main {
        min-width: 0 !important;
        flex: 1 1 auto !important;
    }

    .og-list-name {
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 5px !important;
        min-width: 0 !important;
        color: var(--og-li-text) !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        line-height: 1.25 !important;
    }

    .og-list-muted,
    .og-comment-post {
        color: var(--og-li-muted) !important;
        font-size: 12px !important;
        line-height: 1.35 !important;
    }

    .og-comment-text {
        display: block !important;
        margin: 5px 0 0 !important;
        color: var(--og-li-text) !important;
        font-size: 14px !important;
        line-height: 1.45 !important;
    }

    .alma-ad-slot {
        width: 100% !important;
    }

    .og-sheet {
        position: fixed !important;
        inset: 0 !important;
        z-index: 1000 !important;
        pointer-events: none !important;
    }

    .og-sheet[aria-hidden="false"] {
        pointer-events: auto !important;
    }

    .og-sheet-backdrop {
        position: absolute !important;
        inset: 0 !important;
        background: rgba(0, 0, 0, 0.55) !important;
        opacity: 0 !important;
        transition: opacity 0.16s ease !important;
    }

    .og-sheet[aria-hidden="false"] .og-sheet-backdrop {
        opacity: 1 !important;
    }

    .og-sheet-panel {
        position: absolute !important;
        left: 50% !important;
        top: 50% !important;
        right: auto !important;
        bottom: auto !important;
        width: min(92vw, 520px) !important;
        max-height: min(86vh, 720px) !important;
        overflow: auto !important;
        border: 1px solid var(--og-li-line) !important;
        border-radius: 8px !important;
        background: var(--og-li-card) !important;
        padding: 20px !important;
        transform: translate(-50%, -48%) scale(.98) !important;
        opacity: 0 !important;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.22) !important;
        transition: transform 0.16s ease, opacity 0.16s ease !important;
    }

    .og-sheet[aria-hidden="false"] .og-sheet-panel {
        transform: translate(-50%, -50%) scale(1) !important;
        opacity: 1 !important;
    }

    .og-sheet-handle {
        display: none !important;
    }

    .og-sheet-title {
        margin: 0 0 12px !important;
        color: var(--og-li-text) !important;
        font-size: 20px !important;
        font-weight: 600 !important;
        line-height: 1.25 !important;
        text-align: left !important;
    }

    .og-avatar-preview {
        overflow: hidden !important;
        border-radius: 8px !important;
        background: #d9e5ef !important;
    }

    .og-avatar-preview img,
    .og-avatar-preview span {
        display: flex !important;
        width: 100% !important;
        max-height: 70vh !important;
        aspect-ratio: 1 / 1 !important;
        align-items: center !important;
        justify-content: center !important;
        object-fit: cover !important;
        color: var(--og-li-blue) !important;
        font-size: 64px !important;
        font-weight: 600 !important;
    }

    .og-sheet-action,
    .og-sheet-close {
        display: flex !important;
        width: 100% !important;
        min-height: 40px !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 10px !important;
        border: 0 !important;
        border-radius: 4px !important;
        background: transparent !important;
        color: var(--og-li-text) !important;
        padding: 0 12px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        text-align: left !important;
        text-decoration: none !important;
        cursor: pointer !important;
    }

    .og-sheet-action:hover,
    .og-sheet-action:focus-visible,
    .og-sheet-close:hover,
    .og-sheet-close:focus-visible {
        background: var(--og-li-hover) !important;
    }

    .og-sheet-close {
        justify-content: center !important;
        margin-top: 12px !important;
        border: 1px solid var(--og-li-muted) !important;
        border-radius: 999px !important;
        color: var(--og-li-muted) !important;
    }

    .og-badge-sheet-panel {
        width: min(92vw, 560px) !important;
        padding: 18px 20px 20px !important;
    }

    .og-badge-sheet-close-icon {
        position: absolute !important;
        top: 10px !important;
        right: 10px !important;
        display: inline-flex !important;
        width: 36px !important;
        height: 36px !important;
        align-items: center !important;
        justify-content: center !important;
        border: 0 !important;
        border-radius: 999px !important;
        background: transparent !important;
        color: var(--og-li-muted) !important;
        cursor: pointer !important;
        z-index: 5 !important;
    }

    .og-badge-sheet-close-icon:hover,
    .og-badge-sheet-close-icon:focus-visible {
        background: var(--og-li-hover) !important;
        color: var(--og-li-text) !important;
    }

    .og-badge-sheet-close-icon svg {
        width: 20px !important;
        height: 20px !important;
    }

    .og-badge-sheet-media {
        position: relative !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        min-height: 270px !important;
        padding-top: 18px !important;
    }

    .og-badge-sheet-subtitle {
        margin: -4px 0 14px !important;
        color: var(--og-li-muted) !important;
        font-size: 14px !important;
        line-height: 1.45 !important;
    }

    .og-badge-sheet-meta {
        margin-top: 0 !important;
    }

    .og-badge-sheet-meta .og-chip {
        border: 1px solid var(--og-li-line) !important;
        border-radius: 999px !important;
        padding: 6px 10px !important;
        line-height: 1 !important;
    }

    .og-ref-badge-stage {
        --badge-preview-color: #8fb8d8;
        --badge-rotate-x: 0deg;
        --badge-rotate-y: 0deg;
        --badge-front-scale: 1;
        --badge-back-scale: 1;
        --badge-front-opacity: 1;
        --badge-back-opacity: 0;
        --badge-light-left: 30%;
        --badge-light-opacity: .18;
        --badge-shadow-shift: 0px;
        --badge-highlight-scale: 1;
        position: relative !important;
        width: 222px !important;
        height: 222px !important;
        margin: 0 auto !important;
        perspective: 1200px !important;
        cursor: grab !important;
        user-select: none !important;
        touch-action: none !important;
    }

    .og-ref-badge-stage.is-dragging {
        cursor: grabbing !important;
    }

    .og-ref-badge-rotor {
        position: relative !important;
        width: 100% !important;
        height: 100% !important;
        border-radius: 999px !important;
        transform: rotateX(var(--badge-rotate-x)) rotateY(var(--badge-rotate-y)) !important;
        transform-style: preserve-3d !important;
        transition: transform .08s ease !important;
    }

    .og-ref-badge-stage.is-dragging .og-ref-badge-rotor {
        transition: none !important;
    }

    .og-ref-badge-layer {
        position: absolute !important;
        inset: 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        overflow: hidden !important;
        border-radius: 999px !important;
        backface-visibility: hidden !important;
        transform-style: preserve-3d !important;
        border: 1px solid rgba(0, 0, 0, 0.16) !important;
    }

    .og-ref-badge-layer--front {
        background:
            linear-gradient(95deg, transparent 0%, rgba(255,255,255,var(--badge-light-opacity)) var(--badge-light-left), transparent 58%),
            radial-gradient(circle at 30% 22%, rgba(255,255,255,.96), rgba(255,255,255,.16) 28%, transparent 42%),
            radial-gradient(circle at 50% 48%, var(--badge-preview-color), color-mix(in srgb, var(--badge-preview-color) 58%, #111827 42%)) !important;
        transform: translateZ(14px) scale(var(--badge-front-scale)) !important;
        opacity: var(--badge-front-opacity) !important;
    }

    .og-ref-badge-layer--back {
        background:
            radial-gradient(circle at 30% 20%, rgba(255,255,255,.22), transparent 30%),
            radial-gradient(circle at 50% 48%, color-mix(in srgb, var(--badge-preview-color) 88%, #ffffff 12%), color-mix(in srgb, var(--badge-preview-color) 58%, #111827 42%)) !important;
        transform: rotateY(180deg) translateZ(14px) scale(var(--badge-back-scale)) !important;
        opacity: var(--badge-back-opacity) !important;
    }

    .og-ref-badge-layer--front::after,
    .og-ref-badge-layer--back::after {
        content: "" !important;
        position: absolute !important;
        inset: 14px !important;
        border-radius: 999px !important;
        border: 1px solid rgba(255, 255, 255, 0.22) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.42), inset 0 -10px 18px rgba(0,0,0,.16) !important;
        pointer-events: none !important;
    }

    .og-ref-badge-glow {
        position: absolute !important;
        inset: 0 !important;
        border-radius: inherit !important;
        background: radial-gradient(circle at 36% 24%, rgba(255,255,255,.44), transparent 36%) !important;
        transform: scale(var(--badge-highlight-scale)) !important;
        pointer-events: none !important;
    }

    .og-ref-badge-content {
        position: relative !important;
        z-index: 2 !important;
        display: flex !important;
        width: 72% !important;
        height: 72% !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 999px !important;
        transform: translateZ(22px) !important;
    }

    .og-ref-badge-media {
        display: block !important;
        width: 78% !important;
        height: 78% !important;
        object-fit: contain !important;
        filter: drop-shadow(0 6px 8px rgba(0,0,0,.18)) !important;
    }

    .og-ref-badge-fallback {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 74% !important;
        height: 74% !important;
        border-radius: 999px !important;
        color: #ffffff !important;
        font-size: 82px !important;
        font-weight: 700 !important;
        text-shadow: 0 4px 8px rgba(0,0,0,.24) !important;
    }

    .og-ref-badge-back-shape {
        position: relative !important;
        display: flex !important;
        width: 74% !important;
        height: 74% !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 999px !important;
        background-image: var(--badge-back-icon-url) !important;
        background-repeat: no-repeat !important;
        background-position: center !important;
        background-size: 60% !important;
        transform: translateZ(22px) !important;
    }

    .og-ref-badge-back-shape::before {
        content: "" !important;
        position: absolute !important;
        inset: -14px !important;
        border-radius: 999px !important;
        background: rgba(0,0,0,.16) !important;
        z-index: -1 !important;
    }

    .og-ref-badge-back-copy {
        position: absolute !important;
        inset: auto 10px 16px !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        text-align: center !important;
    }

    .og-ref-badge-back-name {
        max-width: 100% !important;
        overflow: hidden !important;
        color: rgba(255,255,255,.92) !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        line-height: 1.1 !important;
        letter-spacing: .08em !important;
        text-overflow: ellipsis !important;
        text-transform: uppercase !important;
        white-space: nowrap !important;
        text-shadow: 0 1px 2px rgba(0,0,0,.22) !important;
    }

    .og-ref-badge-back-brand {
        color: rgba(255,255,255,.92) !important;
        font-size: 18px !important;
        font-weight: 800 !important;
        line-height: 1 !important;
        letter-spacing: .06em !important;
        text-shadow: 0 1px 2px rgba(0,0,0,.22) !important;
    }

    .og-ref-badge-shadow {
        width: 142px !important;
        height: 16px !important;
        margin: -8px auto 0 !important;
        border-radius: 999px !important;
        background: rgba(0, 0, 0, 0.18) !important;
        filter: blur(8px) !important;
        transform: translateX(var(--badge-shadow-shift, 0px)) !important;
        pointer-events: none !important;
    }

    @media (max-width: 1180px) {
        body:has(.og-profile-page) .main-grid {
            grid-template-columns: minmax(0, var(--og-profile-width)) var(--layout-right-width, 304px) !important;
            max-width: calc(var(--og-profile-width) + var(--layout-right-width, 304px) + 24px) !important;
        }
    }

    @media (max-width: 960px) {
        body:has(.og-profile-page) .main-grid {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        body:has(.og-profile-page) .layout-main {
            max-width: 100% !important;
            padding: 0 !important;
        }

        body:has(.og-profile-page) .layout-side--left,
        body:has(.og-profile-page) .layout-side--right {
            display: none !important;
        }

        .og-profile-page {
            padding: 0 10px 28px !important;
        }

        .og-profile-wrap {
            max-width: 100% !important;
        }
    }

    @media (max-width: 640px) {
        .og-profile-page {
            padding: 0 0 28px !important;
        }

        .og-card,
        .og-tabs-card,
        .og-list-card,
        .og-empty,
        .og-post-wrapper [data-post-card-shell] {
            border-right: 0 !important;
            border-left: 0 !important;
            border-radius: 0 !important;
        }

        .og-cover {
            height: 122px !important;
            border-radius: 0 !important;
        }

        .og-body {
            padding: 0 16px 20px !important;
        }

        .og-topline {
            min-height: 58px !important;
            gap: 10px !important;
        }

        .og-avatar-button {
            margin-top: -52px !important;
        }

        .og-avatar {
            width: 104px !important;
            height: 104px !important;
            border-width: 3px !important;
            font-size: 38px !important;
        }

        .og-actions {
            gap: 6px !important;
            padding-top: 10px !important;
        }

        .og-btn,
        .og-icon-btn,
        .og-menu > summary {
            height: 30px !important;
            padding: 0 12px !important;
            font-size: 14px !important;
        }

        .og-icon-btn span,
        .og-menu > summary span {
            display: none !important;
        }

        .og-icon-btn,
        .og-menu > summary {
            width: 34px !important;
            padding: 0 !important;
        }

        .og-name {
            font-size: 22px !important;
        }

        .og-bio {
            font-size: 15px !important;
        }

        .og-username,
        .og-meta,
        .og-stat,
        .og-chip {
            font-size: 13px !important;
        }

        .og-stats {
            gap: 4px 10px !important;
        }

        .og-badges {
            gap: 7px !important;
        }

        .og-badge {
            width: 40px !important;
            height: 40px !important;
        }

        .og-tabs-card {
            min-height: 48px !important;
            margin-top: 8px !important;
            padding: 0 8px !important;
        }

        .og-tab {
            height: 48px !important;
            padding: 0 10px !important;
            font-size: 13px !important;
        }

        .og-sort > summary span {
            display: none !important;
        }

        .og-menu-panel {
            position: fixed !important;
            top: auto !important;
            right: 10px !important;
            bottom: calc(10px + env(safe-area-inset-bottom)) !important;
            left: 10px !important;
            width: auto !important;
            border-radius: 12px !important;
            padding: 8px 0 !important;
        }

        .og-menu-item {
            min-height: 46px !important;
        }

        .og-sheet-panel {
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            top: auto !important;
            width: 100% !important;
            max-height: 88vh !important;
            border-right: 0 !important;
            border-bottom: 0 !important;
            border-left: 0 !important;
            border-radius: 16px 16px 0 0 !important;
            transform: translateY(110%) !important;
            opacity: 1 !important;
            padding: 16px !important;
        }

        .og-sheet[aria-hidden="false"] .og-sheet-panel {
            transform: translateY(0) !important;
        }

        .og-sheet-handle {
            display: block !important;
            width: 42px !important;
            height: 4px !important;
            margin: 0 auto 14px !important;
            border-radius: 999px !important;
            background: var(--og-li-line) !important;
        }

        .og-badge-sheet-media {
            min-height: 226px !important;
        }

        .og-ref-badge-stage {
            width: 190px !important;
            height: 190px !important;
        }

        .og-ref-badge-fallback {
            font-size: 66px !important;
        }

        .og-ref-badge-shadow {
            width: 118px !important;
        }
    }

    body.dark:has(.og-profile-page),
    .dark body:has(.og-profile-page) {
        --og-li-bg: #000000;
        --og-li-card: #1b1f23;
        --og-li-text: rgba(255, 255, 255, 0.92);
        --og-li-muted: rgba(255, 255, 255, 0.68);
        --og-li-soft: rgba(255, 255, 255, 0.10);
        --og-li-line: #38434f;
        --og-li-blue: #70b5f9;
        --og-li-blue-hover: #a8d4ff;
        --og-li-pill-hover: rgba(112, 181, 249, 0.14);
        --og-li-hover: rgba(255, 255, 255, 0.08);
        background: var(--og-li-bg) !important;
    }

    body.dark .og-body,
    .dark .og-body,
    body.dark .og-card,
    .dark .og-card,
    body.dark .og-tabs-card,
    .dark .og-tabs-card,
    body.dark .og-list-card,
    .dark .og-list-card,
    body.dark .og-empty,
    .dark .og-empty,
    body.dark .og-menu-panel,
    .dark .og-menu-panel,
    body.dark .og-sort-panel,
    .dark .og-sort-panel,
    body.dark .og-sheet-panel,
    .dark .og-sheet-panel {
        background: var(--og-li-card) !important;
        border-color: var(--og-li-line) !important;
    }

    body.dark .og-avatar,
    .dark .og-avatar {
        border-color: var(--og-li-card) !important;
        background: #28313a !important;
        color: var(--og-li-blue) !important;
    }

    body.dark .og-btn,
    .dark .og-btn {
        background: var(--og-li-blue) !important;
        border-color: var(--og-li-blue) !important;
        color: #102235 !important;
    }

    body.dark .og-btn:hover,
    .dark .og-btn:hover,
    body.dark .og-btn:focus-visible,
    .dark .og-btn:focus-visible {
        background: var(--og-li-blue-hover) !important;
        border-color: var(--og-li-blue-hover) !important;
        color: #102235 !important;
    }

    body.dark .og-icon-btn,
    .dark .og-icon-btn,
    body.dark .og-menu > summary,
    .dark .og-menu > summary,
    body.dark .og-sheet-close,
    .dark .og-sheet-close {
        border-color: var(--og-li-muted) !important;
        color: var(--og-li-muted) !important;
    }

    body.dark .og-icon-btn:hover,
    .dark .og-icon-btn:hover,
    body.dark .og-menu > summary:hover,
    .dark .og-menu > summary:hover,
    body.dark .og-menu[open] > summary,
    .dark .og-menu[open] > summary {
        border-color: var(--og-li-text) !important;
        color: var(--og-li-text) !important;
        background: var(--og-li-hover) !important;
    }

    body.dark .og-name,
    .dark .og-name,
    body.dark .og-bio,
    .dark .og-bio,
    body.dark .og-list-title,
    .dark .og-list-title,
    body.dark .og-list-name,
    .dark .og-list-name,
    body.dark .og-comment-text,
    .dark .og-comment-text,
    body.dark .og-menu-item,
    .dark .og-menu-item,
    body.dark .og-sort-option,
    .dark .og-sort-option,
    body.dark .og-sheet-title,
    .dark .og-sheet-title,
    body.dark .og-sheet-action,
    .dark .og-sheet-action {
        color: var(--og-li-text) !important;
    }

    body.dark .og-username,
    .dark .og-username,
    body.dark .og-meta,
    .dark .og-meta,
    body.dark .og-chip,
    .dark .og-chip,
    body.dark .og-list-desc,
    .dark .og-list-desc,
    body.dark .og-list-muted,
    .dark .og-list-muted,
    body.dark .og-comment-post,
    .dark .og-comment-post,
    body.dark .og-social,
    .dark .og-social,
    body.dark .og-tab,
    .dark .og-tab,
    body.dark .og-sort > summary,
    .dark .og-sort > summary {
        color: var(--og-li-muted) !important;
    }

    body.dark .og-stat,
    .dark .og-stat,
    body.dark a.og-chip,
    .dark a.og-chip,
    body.dark .og-chip--link,
    .dark .og-chip--link {
        color: var(--og-li-blue) !important;
    }

    body.dark .og-stat:hover,
    .dark .og-stat:hover,
    body.dark a.og-chip:hover,
    .dark a.og-chip:hover {
        color: var(--og-li-blue-hover) !important;
    }

    body.dark .og-tab:hover,
    .dark .og-tab:hover,
    body.dark .og-tab:focus-visible,
    .dark .og-tab:focus-visible {
        color: var(--og-li-text) !important;
        background: var(--og-li-hover) !important;
    }

    body.dark .og-tab[aria-current="page"],
    .dark .og-tab[aria-current="page"] {
        color: #7bd88f !important;
        border-bottom-color: #7bd88f !important;
    }

    body.dark .og-list-head,
    .dark .og-list-head,
    body.dark .og-list-link,
    .dark .og-list-link {
        border-color: var(--og-li-line) !important;
    }

    body.dark .og-list-link:hover,
    .dark .og-list-link:hover,
    body.dark .og-menu-item:hover,
    .dark .og-menu-item:hover,
    body.dark .og-sort-option:hover,
    .dark .og-sort-option:hover,
    body.dark .og-sheet-action:hover,
    .dark .og-sheet-action:hover,
    body.dark .og-sheet-close:hover,
    .dark .og-sheet-close:hover {
        background: var(--og-li-hover) !important;
    }

    @media (prefers-reduced-motion: reduce) {
        .og-profile-page *,
        .og-profile-page *::before,
        .og-profile-page *::after {
            transition: none !important;
            animation: none !important;
        }
    }


    /* =========================================================
       Kullanıcı isteği: mobilde tam genişlik + kalın fontları normalleştir
       ========================================================= */
    .og-profile-page :is(
        .og-name,
        .og-username,
        .og-bio,
        .og-meta,
        .og-points,
        .og-stat,
        .og-stat strong,
        .og-chip,
        .og-chip--link,
        a.og-chip,
        .og-social,
        .og-btn,
        .og-icon-btn,
        .og-menu > summary,
        .og-menu-item,
        .og-tab,
        .og-sort > summary,
        .og-sort-option,
        .og-list-title,
        .og-list-name,
        .og-list-muted,
        .og-comment-post,
        .og-comment-text,
        .og-sheet-title,
        .og-sheet-action,
        .og-sheet-close,
        .og-badge-sheet-subtitle,
        .og-ref-badge-back-name,
        .og-ref-badge-back-brand,
        .og-badge__fallback,
        .og-ref-badge-fallback,
        .og-avatar,
        .og-avatar-preview span,
        .og-list-avatar
    ) {
        font-weight: 400 !important;
    }

    @media (max-width: 960px) {
        body:has(.og-profile-page),
        body:has(.og-profile-page) .main-grid,
        body:has(.og-profile-page) .layout-main {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            overflow-x: hidden !important;
        }

        .og-profile-page {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 0 28px !important;
        }

        .og-profile-wrap,
        .og-card,
        .og-tabs-card,
        .og-content,
        .og-list-card,
        .og-empty,
        .og-post-wrapper,
        .og-post-wrapper [data-post-card-shell] {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .og-profile-wrap {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
    }

    @media (max-width: 640px) {
        .og-card,
        .og-tabs-card,
        .og-list-card,
        .og-empty,
        .og-post-wrapper [data-post-card-shell] {
            border-right: 0 !important;
            border-left: 0 !important;
            border-radius: 0 !important;
        }

        .og-tabs-card,
        .og-content {
            margin-top: 8px !important;
        }
    }



    /* =========================================================
       Son düzeltme: mobil/tablet ekranda profil alanını
       ortadaki dar kapsayıcıdan çıkarıp gerçek tam genişlik yapar.
       ========================================================= */
    @media (max-width: 960px) {
        html,
        body {
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
        }

        body:has(.og-profile-page) {
            min-width: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        body:has(.og-profile-page) :is(
            #app,
            main,
            .main,
            .page,
            .page-wrap,
            .page-wrapper,
            .site,
            .site-main,
            .site-content,
            .app,
            .app-main,
            .app-content,
            .content,
            .content-area,
            .main-content,
            .main-container,
            .container,
            .layout,
            .layout-container,
            .main-grid,
            .layout-main
        ) {
            width: 100% !important;
            max-width: none !important;
            min-width: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .og-profile-page {
            position: relative !important;
            left: 50% !important;
            right: 50% !important;
            width: 100vw !important;
            max-width: 100vw !important;
            min-width: 100vw !important;
            margin-left: -50vw !important;
            margin-right: -50vw !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            overflow-x: hidden !important;
        }

        .og-profile-wrap,
        .og-card,
        .og-tabs-card,
        .og-content,
        .og-list-card,
        .og-empty,
        .og-post-wrapper,
        .og-post-wrapper [data-post-card-shell] {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .og-card,
        .og-tabs-card,
        .og-list-card,
        .og-empty,
        .og-post-wrapper [data-post-card-shell] {
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
        }

        .og-cover {
            width: 100% !important;
            border-radius: 0 !important;
        }

        .og-body,
        .og-list-head,
        .og-list-link {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        .og-tabs-card {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
    }


    /* =========================================================
       Son ince ayar: daha fazla ikon-only, takip mavi,
       gerçek sosyal ikonlar, favicon, rozet alt efekti yok,
       kapak üstüne header boşluğu.
       ========================================================= */
    .og-profile-wrap {
        padding-top: 16px !important;
    }

    .og-actions .og-btn,
    .og-actions .og-btn--primary,
    .og-actions form .og-btn,
    .og-actions form .og-btn--primary {
        min-width: 96px !important;
        border: 1px solid var(--og-li-blue, #0a66c2) !important;
        background: var(--og-li-blue, #0a66c2) !important;
        color: #ffffff !important;
        font-weight: 500 !important;
    }

    .og-actions .og-btn:hover,
    .og-actions .og-btn:focus-visible,
    .og-actions .og-btn--primary:hover,
    .og-actions .og-btn--primary:focus-visible {
        border-color: var(--og-li-blue-hover, #004182) !important;
        background: var(--og-li-blue-hover, #004182) !important;
        color: #ffffff !important;
    }

    .og-menu > summary.og-menu-summary-icon-only,
    .og-menu > summary {
        width: 38px !important;
        min-width: 38px !important;
        height: 32px !important;
        padding: 0 !important;
        gap: 0 !important;
    }

    .og-menu > summary.og-menu-summary-icon-only span,
    .og-menu > summary > span {
        display: none !important;
    }

    .og-menu > summary.og-menu-summary-icon-only svg,
    .og-menu > summary > svg {
        width: 20px !important;
        height: 20px !important;
    }

    .og-links {
        gap: 8px !important;
    }

    .og-website-link {
        min-height: 30px !important;
        gap: 7px !important;
    }

    .og-favicon {
        display: block !important;
        width: 16px !important;
        height: 16px !important;
        border-radius: 4px !important;
        object-fit: cover !important;
        flex: 0 0 16px !important;
    }

    .og-social {
        width: 32px !important;
        min-width: 32px !important;
        height: 32px !important;
        padding: 0 !important;
        text-transform: none !important;
        font-size: 0 !important;
        color: rgba(0, 0, 0, 0.72) !important;
    }

    .og-social svg {
        display: block !important;
        width: 16px !important;
        height: 16px !important;
    }

    .og-social--facebook:hover,
    .og-social--facebook:focus-visible {
        color: #1877f2 !important;
        border-color: #1877f2 !important;
    }

    .og-social--instagram:hover,
    .og-social--instagram:focus-visible {
        color: #c13584 !important;
        border-color: #c13584 !important;
    }

    .og-social--x:hover,
    .og-social--x:focus-visible {
        color: #000000 !important;
        border-color: #000000 !important;
    }

    .og-social--tiktok:hover,
    .og-social--tiktok:focus-visible {
        color: #111827 !important;
        border-color: #111827 !important;
    }

    .og-social--youtube:hover,
    .og-social--youtube:focus-visible {
        color: #ff0000 !important;
        border-color: #ff0000 !important;
    }

    .og-badge::after {
        display: none !important;
        content: none !important;
    }

    .og-badge,
    .og-badge:hover,
    .og-badge:focus-visible,
    .og-badge.is-dragging {
        box-shadow: none !important;
    }

    @media (max-width: 960px) {
        .og-profile-wrap {
            padding-top: 12px !important;
        }

        .og-menu > summary.og-menu-summary-icon-only,
        .og-menu > summary {
            width: 36px !important;
            min-width: 36px !important;
            height: 32px !important;
        }
    }

    body.dark .og-social,
    .dark .og-social {
        color: rgba(255,255,255,0.82) !important;
    }

</style>
@endpush


@section('content')
    @php
        $defaultCoverUrl = 'https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?auto=format&fit=crop&w=1400&q=80';
        $coverUrl = $user->cover_photo_url ?? $user->cover_image ?? $defaultCoverUrl;
        $profileUrl = $user->profile_photo_url ?? null;
        $joinedLabel = $user->joined_at ? $user->joined_at->translatedFormat('Y') . "'den beri" : ($user->created_at ? $user->created_at->translatedFormat('Y') . "'den beri" : null);
        $website = trim((string) ($user->website_url ?? ''));
        $websiteHref = '';
        $websiteHost = '';
        if ($website !== '') {
            $websiteHref = str_starts_with($website, 'http://') || str_starts_with($website, 'https://')
                ? $website
                : 'https://' . ltrim($website, '/');
            $websiteHost = preg_replace('/^www\./', '', (string) (parse_url($websiteHref, PHP_URL_HOST) ?: '')) ?: '';
        }
        $websiteFaviconDomain = $websiteHost !== '' ? $websiteHost : (parse_url($websiteHref, PHP_URL_HOST) ?: $websiteHref);
        $websiteFaviconUrl = $websiteFaviconDomain !== ''
            ? 'https://www.google.com/s2/favicons?domain=' . urlencode($websiteFaviconDomain) . '&sz=32'
            : '';

        $activeTab = in_array(($activeTab ?? 'stories'), ['stories', 'comments', 'followers', 'followings'], true) ? $activeTab : 'stories';
        $commentsCount = (int) ($commentsCount ?? 0);
        $comments = collect($comments ?? []);
        $followers = collect($followers ?? []);
        $followings = collect($followings ?? []);
        $profileInitial = mb_strtoupper(mb_substr((string) ($user->name ?? __('site.profile_page.fallback_name')), 0, 1, 'UTF-8'), 'UTF-8');
        $usernameLabel = filled($user->username ?? null) ? '@' . $user->username : null;
        $locationLabel = trim((string) ($user->location ?? ''));
        $companyLabel = trim((string) ($user->company ?? ''));
        $bioText = trim((string) ($user->bio ?? ''));
        $occupationLabel = trim((string) (
            $user->occupation
            ?? $user->job_title
            ?? $user->profession
            ?? $user->headline
            ?? ''
        ));
        if ($occupationLabel === '') {
            $occupationLabel = $companyLabel;
        }
        $joinedSource = $user->joined_at ?? $user->created_at;
        $joinedDetailLabel = $joinedSource
            ? (app()->getLocale() === 'tr'
                ? \Illuminate\Support\Carbon::parse($joinedSource)->translatedFormat('F Y') . "'te katıldı."
                : \Illuminate\Support\Carbon::parse($joinedSource)->translatedFormat('F Y') . ' joined')
            : null;
        $profileHeadingTitle = trim((string) ($user->name ?? __('site.profile_page.fallback_name')));
        $profileDisplayBio = $bioText !== '' ? $bioText : '';
        $shareProfileLabel = app()->getLocale() === 'tr' ? 'Profili paylaş' : 'Share profile';
        $moreActionsLabel = app()->getLocale() === 'tr' ? 'Daha fazla' : 'More';
        $postsStatLabel = app()->getLocale() === 'tr' ? 'Hikayeler' : 'Stories';
        $followersStatLabel = app()->getLocale() === 'tr' ? 'Takipçiler' : 'Followers';
        $followingsStatLabel = app()->getLocale() === 'tr' ? 'Takip' : 'Following';
        $sort = $sort ?? 'new';
        $profileTabs = [
            'stories' => app()->getLocale() === 'tr' ? 'Gönderiler' : 'Posts',
            'comments' => app()->getLocale() === 'tr' ? 'Yorumlar' : 'Comments',
            'followers' => app()->getLocale() === 'tr' ? 'Takipçiler' : 'Followers',
            'followings' => app()->getLocale() === 'tr' ? 'Takip' : 'Following',
        ];
        $visibleProfileTabs = $profileTabs;
        $profileTabUrl = function (string $tab) use ($user, $sort) {
            return route('users.show', [
                'user' => $user,
                'tab' => $tab,
                'sort' => $sort ?? 'new',
            ]);
        };
        $socialLinks = [
            'facebook' => $user->social_facebook ?? null,
            'instagram' => $user->social_instagram ?? null,
            'x' => $user->social_x ?? null,
            'tiktok' => $user->social_tiktok ?? null,
            'youtube' => $user->social_youtube ?? null,
        ];
        $socialPills = collect($socialLinks)->map(function ($url, $platform) {
            $value = trim((string) $url);
            if ($value === '') {
                return null;
            }
            if (!str_starts_with($value, 'http://') && !str_starts_with($value, 'https://')) {
                $value = 'https://' . ltrim($value, '/');
            }
            return [
                'platform' => $platform,
                'label' => match ($platform) {
                    'facebook' => 'Facebook',
                    'instagram' => 'Instagram',
                    'x' => 'X',
                    'tiktok' => 'TikTok',
                    'youtube' => 'YouTube',
                    default => ucfirst($platform),
                },
                'url' => $value,
                'class' => 'og-social--' . $platform,
                'short' => match ($platform) {
                    'facebook' => 'f',
                    'instagram' => 'ig',
                    'x' => 'x',
                    'tiktok' => 'tt',
                    'youtube' => 'yt',
                    default => mb_substr($platform, 0, 1),
                },
            ];
        })->filter()->values();
        $earnedBadges = collect($earnedBadges ?? []);

        /*
         * Rozet ikon çözümleyici:
         * - PNG/JPG/WEBP/GIF/SVG dosya yollarını SVG'ye çevirmez.
         * - Eğer veri tabanında gerçekten inline <svg> kodu varsa, bunu bozuk URL yapmaz;
         *   güvenli şekilde data:image/svg+xml;base64 biçiminde img src olarak kullanır.
         * - Storage/public/uploads farklı kayıt formatları için primary + fallback URL üretir.
         */
        $resolveBadgeIcon = static function ($badge): array {
            $candidates = [
                // Öncelik: rozet ikonları bu sütundan geliyor.
                $badge->icon_svg_path ?? null,

                // Diğer olası ikon/görsel sütunları.
                $badge->icon_url ?? null,
                $badge->image_url ?? null,
                $badge->avatar_url ?? null,
                $badge->photo_url ?? null,
                $badge->logo_url ?? null,
                $badge->svg_url ?? null,

                $badge->icon ?? null,
                $badge->image ?? null,
                $badge->photo ?? null,
                $badge->logo ?? null,
                $badge->svg ?? null,

                $badge->icon_path ?? null,
                $badge->image_path ?? null,
                $badge->svg_path ?? null,
                $badge->file_path ?? null,
                $badge->path ?? null,
            ];

            if (method_exists($badge, 'getFirstMediaUrl')) {
                foreach (['icon', 'icons', 'badge', 'badges', 'image', 'images', 'default'] as $collectionName) {
                    try {
                        $mediaUrl = $badge->getFirstMediaUrl($collectionName);
                        if (filled($mediaUrl)) {
                            $candidates[] = $mediaUrl;
                        }
                    } catch (\Throwable $e) {
                        // Media library yoksa sessiz geç.
                    }
                }
            }

            foreach ($candidates as $candidate) {
                $rawCandidate = trim((string) $candidate);

                if ($rawCandidate === '') {
                    continue;
                }

                if (str_starts_with($rawCandidate, 'data:image/')) {
                    return [
                        'url' => $rawCandidate,
                        'fallback' => null,
                    ];
                }

                if (str_contains($rawCandidate, '<svg')) {
                    $safeSvg = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $rawCandidate);
                    $safeSvg = preg_replace('/\son[a-z]+\s*=\s*(["\']).*?\1/is', '', $safeSvg);
                    $safeSvg = preg_replace('/javascript:/i', '', $safeSvg);

                    return [
                        'url' => 'data:image/svg+xml;base64,' . base64_encode($safeSvg),
                        'fallback' => null,
                    ];
                }

                if (str_starts_with($rawCandidate, 'http://') || str_starts_with($rawCandidate, 'https://')) {
                    return [
                        'url' => $rawCandidate,
                        'fallback' => null,
                    ];
                }

                $candidate = ltrim($rawCandidate, '/');

                if (str_starts_with($candidate, 'storage/')) {
                    return [
                        'url' => asset($candidate),
                        'fallback' => null,
                    ];
                }

                if (str_starts_with($candidate, 'public/')) {
                    $path = substr($candidate, 7);

                    return [
                        'url' => asset('storage/' . $path),
                        'fallback' => asset($path),
                    ];
                }

                if (str_starts_with($candidate, 'badge-icons/')) {
                    return [
                        'url' => asset('storage/' . $candidate),
                        'fallback' => asset($candidate),
                    ];
                }

                if (str_starts_with($candidate, 'uploads/') || str_starts_with($candidate, 'images/') || str_starts_with($candidate, 'assets/')) {
                    return [
                        'url' => asset($candidate),
                        'fallback' => asset('storage/' . $candidate),
                    ];
                }

                return [
                    'url' => asset('storage/' . $candidate),
                    'fallback' => asset($candidate),
                ];
            }

            return [
                'url' => null,
                'fallback' => null,
            ];
        };

        $badgePoints = (int) ($user->badge_points ?? 0);
        $profileHandle = filled($user->username ?? null)
            ? '@' . $user->username
            : '@' . \Illuminate\Support\Str::slug((string) ($user->name ?? 'user'));
        $sortOptions = [
            'new' => app()->getLocale() === 'tr' ? 'Taze' : __('site.profile_page.sort_newest'),
            'popular' => __('site.profile_page.sort_popular'),
        ];
        $activeSort = array_key_exists((string) ($sort ?? 'new'), $sortOptions) ? (string) ($sort ?? 'new') : 'new';
        $activeSortLabel = $sortOptions[$activeSort];
        $renderProfileAdSlot = static function (string $slotKey, string $device = 'all', string $wrapperClass = ''): ?array {
            $content = \App\Models\Snippet::render($slotKey);
            if (trim((string) $content) === '') {
                return null;
            }

            $classes = ['alma-ad-slot'];
            if ($device === 'desktop') {
                $classes[] = 'alma-ad-slot--desktop';
            } elseif ($device === 'mobile') {
                $classes[] = 'alma-ad-slot--mobile';
            }

            $wrapperClass = trim($wrapperClass);
            if ($wrapperClass !== '') {
                $classes[] = $wrapperClass;
            }

            return [
                'slotKey' => $slotKey,
                'classes' => implode(' ', $classes),
                'content' => $content,
            ];
        };
    @endphp

    <div class="og-profile-page">
        <div class="og-profile-wrap">
            <section class="og-card" aria-label="{{ $profileHeadingTitle }}">
                <div class="og-cover">
                    <img
                        src="{{ $coverUrl }}"
                        alt="{{ $profileHeadingTitle }} {{ __('site.profile_page.cover_alt') }}"
                        loading="lazy"
                        onerror="this.onerror=null;this.src='{{ $defaultCoverUrl }}';"
                    >
                </div>

                <div class="og-body">
                    <div class="og-topline">
                        <button type="button" class="og-avatar-button" data-profile-avatar-open aria-label="{{ $profileHeadingTitle }}">
                            <span class="og-avatar">
                                @if($profileUrl)
                                    <img src="{{ $profileUrl }}" alt="{{ $profileHeadingTitle }}" loading="lazy">
                                @else
                                    <span>{{ $profileInitial }}</span>
                                @endif
                            </span>
                        </button>

                        <div class="og-actions">
                            @if(!$isOwnProfile)
                                @auth
                                    @if(!$isBlockedByUser && !$hasBlockedUser)
                                        <form method="POST" action="{{ route('users.follow', $user) }}">
                                            @csrf
                                            <button type="submit" class="og-btn og-btn--primary" aria-label="{{ $isFollowing ? __('site.profile_page.following') : __('site.profile_page.follow') }}">
                                                {{ $isFollowing ? __('site.profile_page.following') : __('site.profile_page.follow') }}
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="og-btn og-btn--primary">{{ __('site.profile_page.follow') }}</a>
                                @endauth
                            @else
                                <a href="{{ route('profile.edit') }}" class="og-btn">{{ app()->getLocale() === 'tr' ? 'Profili düzenle' : 'Edit profile' }}</a>
                            @endif

                            @if(!$isOwnProfile && ($messagesEnabled ?? false) && ($canStartChat ?? false))
                                <a href="{{ route('messages.show', $user) }}" class="og-icon-btn" aria-label="{{ __('site.profile_page.message_aria') }}">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M7 9h10M7 13h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M20 12a8 8 0 0 1-11.4 7.2L4 20l.8-4.4A8 8 0 1 1 20 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                                    <span>{{ __('site.profile_page.message_aria') }}</span>
                                </a>
                            @endif

                            <details class="og-menu" data-auto-close-details>
                                <summary aria-label="{{ $moreActionsLabel }}" title="{{ $moreActionsLabel }}" class="og-menu-summary-icon-only">
                                    <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>
                                </summary>
                                <div class="og-menu-panel shadcn-menu" style="width: 192px !important; min-width: 192px !important; max-width: min(192px, calc(100vw - 24px)) !important; box-sizing: border-box !important; padding: 8px !important; overflow: hidden !important; border: 1px solid #e4e4e7 !important; border-radius: 16px !important; background: #ffffff !important; color: #18181b !important; box-shadow: 0 1px 2px rgba(0,0,0,.05), 0 8px 24px rgba(15,23,42,.08) !important; filter: none !important;">
                                    <button type="button" class="og-menu-item" data-profile-share data-share-url="{{ $profilePageUrl }}" data-share-title="{{ $profileHeadingTitle }}">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M8 12h8M14 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M20 12H6a2 2 0 0 0-2 2v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                        {{ $shareProfileLabel }}
                                    </button>
                                    @auth
                                        @if($isOwnProfile)
                                            <a href="{{ route('profile.edit') }}" class="og-menu-item">
                                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" stroke="currentColor" stroke-width="1.7"/><path d="M19 15a2 2 0 0 0 .4 2.2l.1.1a2 2 0 0 1-2.8 2.8l-.1-.1a2 2 0 0 0-2.2-.4 2 2 0 0 0-1.2 1.8V22h-4v-.6A2 2 0 0 0 8 19.6a2 2 0 0 0-2.2.4l-.1.1a2 2 0 0 1-2.8-2.8l.1-.1A2 2 0 0 0 3.4 15 2 2 0 0 0 1.6 13.8H1v-4h.6A2 2 0 0 0 3.4 8 2 2 0 0 0 3 5.8l-.1-.1a2 2 0 0 1 2.8-2.8l.1.1A2 2 0 0 0 8 3.4 2 2 0 0 0 9.2 1.6V1h4v.6A2 2 0 0 0 14.4 3.4a2 2 0 0 0 2.2-.4l.1-.1a2 2 0 0 1 2.8 2.8l-.1.1A2 2 0 0 0 19 8c.2.7.8 1.2 1.6 1.2h.4v4h-.4A2 2 0 0 0 19 15Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
                                                {{ app()->getLocale() === 'tr' ? 'Profili düzenle' : 'Edit profile' }}
                                            </a>
                                        @else
                                            @if(($messagesEnabled ?? false) && ($canStartChat ?? false))
                                                <a href="{{ route('messages.show', $user) }}" class="og-menu-item">
                                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M7 9h10M7 13h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M20 12a8 8 0 0 1-11.4 7.2L4 20l.8-4.4A8 8 0 1 1 20 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                                                    {{ __('site.profile_page.message_aria') }}
                                                </a>
                                            @endif
                                            <a href="{{ route('users.report.form', $user) }}" class="og-menu-item">
                                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M6 4v16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M6 5h10l-1.8 4L16 13H6" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                                                {{ __('site.profile_page.report_user') }}
                                            </a>
                                            <form method="POST" action="{{ route('users.block', $user) }}">
                                                @csrf
                                                <button type="submit" class="og-menu-item">
                                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/><path d="M6.5 17.5 17.5 6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                    {{ $hasBlockedUser ? __('site.profile_page.unblock') : __('site.profile_page.block') }} {{ $profileHandle }}
                                                </button>
                                            </form>
                                            @can('suspend', $user)
                                                <button type="button" class="og-menu-item">
                                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/><path d="M8 12h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                    Suspend {{ $profileHandle }}
                                                </button>
                                            @endcan
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}" class="og-menu-item">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M15 19c0-2.2-2.7-4-6-4s-6 1.8-6 4M19 16v-6m-3 3h6M9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            {{ __('site.profile_page.follow') }}
                                        </a>
                                    @endauth
                                </div>
                            </details>
                        </div>
                    </div>

                    <div class="og-identity">
                        <div class="og-name-row">
                            <h1 class="og-name">{{ $profileHeadingTitle }}</h1>
                            <x-verification-badge :user="$user" class="inline-flex h-5 w-5 shrink-0 items-center justify-center" size="lg" />
                        </div>

                        @if($usernameLabel)
                            <p class="og-username">{{ $usernameLabel }}</p>
                        @endif

                        @if($badgePoints > 0 || $joinedDetailLabel)
                            <div class="og-meta">
                                @if($badgePoints > 0)
                                    <span class="og-points">+{{ number_format($badgePoints) }} puan</span>
                                @endif
                                @if($badgePoints > 0 && $joinedDetailLabel)
                                    <span class="og-dot" aria-hidden="true"></span>
                                @endif
                                @if($joinedDetailLabel)
                                    <span>{{ $joinedDetailLabel }}</span>
                                @endif
                            </div>
                        @endif

                        <div class="og-stats">
                            <a href="{{ $profileTabUrl('stories') }}" class="og-stat"><strong>{{ number_format((int) ($postsCount ?? 0)) }}</strong> {{ $postsStatLabel }}</a>
                            <a href="{{ $profileTabUrl('followers') }}" class="og-stat"><strong>{{ number_format((int) ($user->followers_count ?? 0)) }}</strong> {{ $followersStatLabel }}</a>
                            <a href="{{ $profileTabUrl('followings') }}" class="og-stat"><strong>{{ number_format((int) ($user->followings_count ?? 0)) }}</strong> {{ $followingsStatLabel }}</a>
                        </div>

                        @if($profileDisplayBio !== '')
                            <p class="og-bio">{{ $profileDisplayBio }}</p>
                        @endif

                        @if($locationLabel !== '' || $companyLabel !== '' || $occupationLabel !== '')
                            <div class="og-chips">
                                @if($locationLabel !== '')
                                    <span class="og-chip">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 21s6-5.7 6-11A6 6 0 0 0 6 10c0 5.3 6 11 6 11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="10" r="2.4" stroke="currentColor" stroke-width="1.8"/></svg>
                                        {{ $locationLabel }}
                                    </span>
                                @endif
                                @if($companyLabel !== '')
                                    <span class="og-chip">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M9 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M4 8h16v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M4 12h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                        {{ $companyLabel }}
                                    </span>
                                @endif
                                @if($occupationLabel !== '' && $occupationLabel !== $companyLabel)
                                    <span class="og-chip">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M3 8.5 12 4l9 4.5L12 13 3 8.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M7 11v4.5c0 1.2 2.2 2.5 5 2.5s5-1.3 5-2.5V11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                        {{ $occupationLabel }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        @if($socialPills->isNotEmpty() || $website !== '')
                            <div class="og-links">
                                @if($website !== '')
                                    <a href="{{ $websiteHref }}" target="_blank" rel="noopener noreferrer" class="og-chip og-chip--link og-website-link" aria-label="Website" title="{{ $websiteHost }}">
                                        @if($websiteFaviconUrl !== '')
                                            <img class="og-favicon" src="{{ $websiteFaviconUrl }}" alt="" loading="lazy" onerror="this.onerror=null;this.style.display='none';">
                                        @else
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.1 0l2.1-2.1a5 5 0 0 0-7.1-7.1L11 4.9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 11a5 5 0 0 0-7.1 0l-2.1 2.1a5 5 0 0 0 7.1 7.1L13 19.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @endif
                                        <span>{{ $websiteHost !== '' ? $websiteHost : $website }}</span>
                                    </a>
                                @endif
                                @foreach($socialPills as $pill)
                                    <a href="{{ $pill['url'] }}" target="_blank" rel="noopener noreferrer" class="og-social {{ $pill['class'] }}" aria-label="{{ $pill['label'] }}" title="{{ $pill['label'] }}">
                                        @switch($pill['platform'])
                                            @case('facebook')
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M14 8.35h2.2V5.08c-.38-.05-1.68-.16-3.2-.16-3.16 0-5.33 1.88-5.33 5.36v3H4.2v3.66h3.47V24h4.25v-7.06h3.33l.53-3.66h-3.86v-2.64c0-1.06.3-2.29 2.08-2.29Z"/></svg>
                                                @break
                                            @case('instagram')
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="1.9" d="M7.5 2.75h9A4.75 4.75 0 0 1 21.25 7.5v9a4.75 4.75 0 0 1-4.75 4.75h-9A4.75 4.75 0 0 1 2.75 16.5v-9A4.75 4.75 0 0 1 7.5 2.75Z"/><path fill="none" stroke="currentColor" stroke-width="1.9" d="M15.8 12A3.8 3.8 0 1 1 8.2 12a3.8 3.8 0 0 1 7.6 0Z"/><circle cx="17.55" cy="6.45" r="1.05" fill="currentColor"/></svg>
                                                @break
                                            @case('x')
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M18.9 2.75h3.25l-7.1 8.12 8.35 10.38h-6.54l-5.12-6.35-5.86 6.35H2.62l7.6-8.7L2.2 2.75h6.7l4.63 5.77 5.37-5.77Zm-1.14 16.67h1.8L7.91 4.48H5.98l11.78 14.94Z"/></svg>
                                                @break
                                            @case('tiktok')
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16.7 2.2c.28 2.35 1.6 3.76 3.9 3.9v3.36a7.46 7.46 0 0 1-3.82-1.12v6.32c0 4.02-2.36 6.84-6.22 6.84-3.36 0-6.06-2.22-6.06-5.66 0-3.92 3.12-6.04 6.86-5.66v3.42c-1.66-.26-3.18.42-3.18 2.08 0 1.42 1.12 2.22 2.34 2.22 1.42 0 2.54-.84 2.54-3.02V2.2h3.64Z"/></svg>
                                                @break
                                            @case('youtube')
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M22.2 7.2a3 3 0 0 0-2.1-2.13C18.24 4.56 12 4.56 12 4.56s-6.24 0-8.1.5A3 3 0 0 0 1.8 7.2 31.3 31.3 0 0 0 1.3 12c0 1.62.17 3.24.5 4.8a3 3 0 0 0 2.1 2.13c1.86.5 8.1.5 8.1.5s6.24 0 8.1-.5a3 3 0 0 0 2.1-2.13c.33-1.56.5-3.18.5-4.8 0-1.62-.17-3.24-.5-4.8ZM9.85 15.54V8.46L16.08 12l-6.23 3.54Z"/></svg>
                                                @break
                                            @default
                                                <span>{{ $pill['short'] }}</span>
                                        @endswitch
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if($earnedBadges->isNotEmpty())
                            <div class="og-badges" aria-label="Rozetler">
                                @foreach($earnedBadges as $badge)
                                    @php
                                        $badgeIcon = $resolveBadgeIcon($badge);
                                        $badgeIconUrl = $badgeIcon['url'] ?? null;
                                        $badgeIconFallbackUrl = $badgeIcon['fallback'] ?? null;
                                        $badgeFallbackLetter = mb_strtoupper(mb_substr((string) ($badge->name ?? 'R'), 0, 1, 'UTF-8'), 'UTF-8');
                                    @endphp
                                    <button
                                        type="button"
                                        class="og-badge"
                                        style="--badge-color: {{ $badge->color ?? '#67e8f9' }}"
                                        title="{{ $badge->name ?? 'Rozet' }}"
                                        data-profile-badge-open
                                        data-manual-rotate-badge
                                        data-badge-name="{{ $badge->name ?? 'Rozet' }}"
                                        data-badge-description="{{ $badge->description ?? 'Bu rozet için açıklama eklenmemiş.' }}"
                                        data-badge-points="{{ number_format((int) ($badge->points_required ?? 0)) }}"
                                        data-badge-awarded-at="{{ optional($badge->pivot?->created_at ?? null)->translatedFormat('d F Y') ?? '-' }}"
                                        data-badge-color="{{ $badge->color ?? '#67e8f9' }}"
                                        data-badge-icon-url="{{ $badgeIconUrl ?? '' }}"
                                        data-badge-icon-fallback-url="{{ $badgeIconFallbackUrl ?? '' }}"
                                        data-badge-fallback-letter="{{ $badgeFallbackLetter }}"
                                    >
                                        <span class="og-badge__face">
                                            @if($badgeIconUrl)
                                                <img
                                                    class="og-badge__media"
                                                    src="{{ $badgeIconUrl }}"
                                                    @if($badgeIconFallbackUrl) data-badge-img-fallback="{{ $badgeIconFallbackUrl }}" @endif
                                                    alt="{{ $badge->name ?? 'Rozet' }}"
                                                    loading="lazy"
                                                    decoding="async"
                                                >
                                                <span class="og-badge__fallback og-badge__fallback--backup">{{ $badgeFallbackLetter }}</span>
                                            @else
                                                <span class="og-badge__fallback">{{ $badgeFallbackLetter }}</span>
                                            @endif
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <nav class="og-tabs-card" aria-label="{{ __('site.profile_page.title_suffix') }}">
                <div class="og-tabs">
                    @foreach ($visibleProfileTabs as $tabKey => $tabLabel)
                        <a href="{{ $profileTabUrl($tabKey) }}" class="og-tab" @if($activeTab === $tabKey) aria-current="page" @endif>{{ $tabLabel }}</a>
                    @endforeach
                </div>

                @if($activeTab === 'stories')
                    <details class="og-sort" data-auto-close-details>
                        <summary>
                            <span>{{ $activeSortLabel }}</span>
                            <svg viewBox="0 0 20 20" width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="m5 8 5 5 5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </summary>
                        <div class="og-sort-panel">
                            @foreach($sortOptions as $sortKey => $sortLabel)
                                <a href="{{ route('users.show', ['user' => $user, 'tab' => 'stories', 'sort' => $sortKey]) }}" class="og-sort-option" aria-current="{{ $activeSort === $sortKey ? 'true' : 'false' }}">{{ $sortLabel }}</a>
                            @endforeach
                        </div>
                    </details>
                @endif
            </nav>

            <div class="og-content">
                @if(($hasBlockedUser ?? false) || ($isBlockedByUser ?? false))
                    <div class="og-empty">{{ __('site.profile_page.restricted') }}</div>
                @elseif($activeTab === 'stories')
                    @php
                        $topFeedAd = $renderProfileAdSlot('ads_feed_top');
                    @endphp
                    @if($topFeedAd)
                        <div class="{{ $topFeedAd['classes'] }}" data-ad-slot="{{ $topFeedAd['slotKey'] }}">
                            @include('partials.ads.icon')
                            <div class="alma-ad-slot__inner">
                                {!! $topFeedAd['content'] !!}
                            </div>
                        </div>
                    @endif

                    @forelse($posts as $post)
                        @php
                            $featured = $post->featured_image_url
                                ?? $post->featured_image
                                ?? $post->cover_image
                                ?? null;
                            $reactionTypesAll = $reactionTypes ?? ($post->reactionTypes ?? collect());
                            $typeMap = collect($reactionTypesAll)->mapWithKeys(function ($type) {
                                $id = $type['id'] ?? ($type->id ?? null);
                                return $id ? [$id => [
                                    'id' => $id,
                                    'short_code' => $type['short_code'] ?? ($type->short_code ?? null),
                                    'emoji' => $type['emoji'] ?? ($type->emoji ?? null),
                                    'gif_url' => $type['gif_url'] ?? ($type->gif_url ?? null),
                                    'label' => $type['label'] ?? ($type->label ?? null),
                                ]] : [];
                            });

                            $reactionCounts = collect($post->reaction_counts ?? [])->mapWithKeys(fn ($cnt, $typeId) => [$typeId => $cnt]);
                            if ($reactionCounts->isEmpty() && method_exists($post, 'reactions')) {
                                $reactionCounts = $post->reactions()
                                    ->whereNotNull('reaction_type_id')
                                    ->selectRaw('reaction_type_id, count(*) as count')
                                    ->groupBy('reaction_type_id')
                                    ->pluck('count', 'reaction_type_id');
                            }

                            $reactionPills = $reactionCounts->map(function ($count, $typeId) use ($typeMap) {
                                $type = $typeMap->get($typeId);
                                if (!$type) return null;
                                $icon = $type['emoji'] ?? $type['gif_url'] ?? null;
                                return [
                                    'type_id' => $type['id'] ?? $typeId,
                                    'count' => (int) $count,
                                    'icon' => $icon,
                                    'emoji' => $type['emoji'] ?? null,
                                    'gif_url' => $type['gif_url'] ?? null,
                                    'label' => $type['label'] ?? null,
                                    'short_code' => $type['short_code'] ?? null,
                                ];
                            })->filter()->values();
                        @endphp
                        <div class="og-post-wrapper">
                            @include('blog.post-card', [
                                'post' => $post,
                                'title' => filled($post->title) ? $post->title : ('/' . ltrim((string) ($post->slug ?? ''), '/')),
                                'excerpt' => trim(strip_tags($post->excerpt ?? $post->content ?? '')),
                                'featuredImage' => $featured,
                                'createdAt' => $post->published_at,
                                'authorName' => optional($post->author)->name ?? __('site.post.community_author'),
                                'authorAvatar' => optional($post->author)->profile_photo_url ?? null,
                                'reactions' => $reactionPills,
                                'reactionTypes' => $reactionTypesAll,
                                'isHero' => false,
                            ])
                        </div>

                        @php
                            $feedBreakAd = null;
                            if ($loop->iteration === 1) {
                                $feedBreakAd = $renderProfileAdSlot('ads_mobile_inline', 'mobile');
                            } elseif ($loop->iteration > 0 && $loop->iteration % 3 === 0 && !$loop->last) {
                                $feedBreakAd = $renderProfileAdSlot('ads_feed_inline', 'all');
                            }
                        @endphp
                        @if($feedBreakAd)
                            <div class="{{ $feedBreakAd['classes'] }}" data-ad-slot="{{ $feedBreakAd['slotKey'] }}">
                                @include('partials.ads.icon')
                                <div class="alma-ad-slot__inner">
                                    {!! $feedBreakAd['content'] !!}
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="og-empty">{{ __('site.profile_page.empty_posts') }}</div>
                    @endforelse

                    @if (is_object($posts) && method_exists($posts, 'links'))
                        <div class="pt-2">
                            {{-- {{ $posts->appends(['tab' => 'stories', 'sort' => $sort])->links() }} --}}
                        </div>
                    @endif
                @elseif($activeTab === 'comments')
                    <section class="og-list-card">
                        <div class="og-list-head">
                            <h2 class="og-list-title">{{ __('site.profile_page.tabs_comments') }}</h2>
                            <p class="og-list-desc">{{ __('site.profile_page.comments_count', ['count' => number_format($commentsCount)]) }}</p>
                        </div>
                        @forelse($comments as $comment)
                            @php
                                $commentUrl = $comment->post?->slug ? route('blog.post', ['post' => $comment->post->slug]) . '#comment-' . $comment->id : null;
                                $commentAvatar = $comment->user?->profile_photo_url;
                                $commentName = $comment->user?->name ?? $user->name ?? __('site.profile_page.fallback_name');
                                $commentInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($commentName, 0, 1));
                            @endphp
                            @if($commentUrl)
                                <a href="{{ $commentUrl }}" class="og-list-link">
                                    <span class="og-list-avatar">
                                        @if($commentAvatar)
                                            <img src="{{ $commentAvatar }}" alt="{{ $commentName }}" loading="lazy">
                                        @else
                                            <span>{{ $commentInitial }}</span>
                                        @endif
                                    </span>
                                    <span class="og-list-main">
                                        <span class="og-list-name">
                                            {{ $commentName }}
                                            @if($comment->user)
                                                <x-verification-badge :user="$comment->user" class="inline-flex h-3.5 w-3.5 shrink-0 items-center justify-center" size="xs" />
                                            @endif
                                            <span class="og-list-muted">{{ optional($comment->created_at)->diffForHumans() }}</span>
                                        </span>
                                        <span class="og-comment-post">{{ $comment->post?->title ?? __('site.profile_page.default_post_title') }}</span>
                                        <span class="og-comment-text">{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $comment->content)), 180) }}</span>
                                    </span>
                                </a>
                            @endif
                        @empty
                            <div class="og-empty">{{ __('site.profile_page.empty_comments') }}</div>
                        @endforelse
                    </section>
                @elseif($activeTab === 'followers')
                    <section class="og-list-card">
                        <div class="og-list-head">
                            <h2 class="og-list-title">{{ __('site.profile_page.tabs_followers') }}</h2>
                            <p class="og-list-desc">{{ number_format((int) ($user->followers_count ?? 0)) }} {{ __('site.profile_page.followers') }}</p>
                        </div>
                        @forelse($followers as $person)
                            <a href="{{ route('users.show', $person) }}" class="og-list-link">
                                <span class="og-list-avatar"><img src="{{ $person->profile_photo_url ?? 'https://placehold.co/80x80' }}" alt="{{ $person->name }}" loading="lazy"></span>
                                <span class="og-list-main">
                                    <span class="og-list-name">{{ $person->name }} <x-verification-badge :user="$person" class="inline-flex h-4 w-4 shrink-0 items-center justify-center" size="sm" /></span>
                                    <span class="og-list-muted">{{ '@' . ($person->username ?? __('site.profile_page.default_username')) }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="og-empty">{{ __('site.profile_page.empty_followers') }}</div>
                        @endforelse
                    </section>
                @elseif($activeTab === 'followings')
                    <section class="og-list-card">
                        <div class="og-list-head">
                            <h2 class="og-list-title">{{ __('site.profile_page.tabs_followings') }}</h2>
                            <p class="og-list-desc">{{ number_format((int) ($user->followings_count ?? 0)) }} {{ __('site.profile_page.followings') }}</p>
                        </div>
                        @forelse($followings as $person)
                            <a href="{{ route('users.show', $person) }}" class="og-list-link">
                                <span class="og-list-avatar"><img src="{{ $person->profile_photo_url ?? 'https://placehold.co/80x80' }}" alt="{{ $person->name }}" loading="lazy"></span>
                                <span class="og-list-main">
                                    <span class="og-list-name">{{ $person->name }} <x-verification-badge :user="$person" class="inline-flex h-4 w-4 shrink-0 items-center justify-center" size="sm" /></span>
                                    <span class="og-list-muted">{{ '@' . ($person->username ?? __('site.profile_page.default_username')) }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="og-empty">{{ __('site.profile_page.empty_followings') }}</div>
                        @endforelse
                    </section>
                @endif
            </div>
        </div>
    </div>

    <div id="profile-avatar-sheet" class="og-sheet" aria-hidden="true">
        <div class="og-sheet-backdrop" data-profile-avatar-close></div>
        <div class="og-sheet-panel" data-profile-avatar-panel>
            <div class="og-sheet-handle"></div>
            <h3 class="og-sheet-title">{{ $profileHeadingTitle }}</h3>
            <div class="og-avatar-preview">
                @if($profileUrl)
                    <img src="{{ $profileUrl }}" alt="{{ $profileHeadingTitle }}">
                @else
                    <span>{{ $profileInitial }}</span>
                @endif
            </div>
            <button type="button" class="og-sheet-close" data-profile-avatar-close>{{ __('post_create.close') }}</button>
        </div>
    </div>

    <div id="profile-badge-sheet" class="og-sheet" aria-hidden="true" inert>
        <div class="og-sheet-backdrop" data-profile-badge-close></div>
        <div class="og-sheet-panel og-badge-sheet-panel" data-profile-badge-panel>
            <button type="button" class="og-badge-sheet-close-icon" data-profile-badge-close aria-label="{{ __('post_create.close') }}">
                <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
            <div class="og-badge-sheet-media">
                <div class="og-ref-badge-stage" style="--badge-preview-color: #67e8f9" data-profile-badge-preview data-manual-rotate-badge>
                    <div class="og-ref-badge-rotor">
                        <div class="og-ref-badge-layer og-ref-badge-layer--front" data-profile-badge-front>
                            <span class="og-ref-badge-glow" aria-hidden="true"></span>
                            <div class="og-ref-badge-content" data-profile-badge-preview-face>
                                <span class="og-ref-badge-fallback">R</span>
                            </div>
                        </div>
                        <div class="og-ref-badge-layer og-ref-badge-layer--back" data-profile-badge-back>
                            <div class="og-ref-badge-back-shape" data-profile-badge-back-shape>
                                <div class="og-ref-badge-back-copy">
                                    <span class="og-ref-badge-back-name" data-profile-badge-back-name>Rozet</span>
                                    <strong class="og-ref-badge-back-brand">OGRAFI</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="og-ref-badge-shadow" aria-hidden="true"></div>
            </div>
            <h3 class="og-sheet-title" data-profile-badge-title>Rozet</h3>
            <p class="og-badge-sheet-subtitle" data-profile-badge-description></p>
            <div class="og-chips og-badge-sheet-meta">
                <span class="og-chip"><strong data-profile-badge-points></strong> puan</span>
                <span class="og-chip" data-profile-badge-awarded-at></span>
            </div>
            <button type="button" class="og-sheet-close og-badge-sheet-close-text" data-profile-badge-close>{{ __('post_create.close') }}</button>
        </div>
    </div>
@endsection


@push('scripts')
<script>
    (() => {
        const detailsItems = Array.from(document.querySelectorAll('[data-auto-close-details]'));
        const shareButtons = Array.from(document.querySelectorAll('[data-profile-share]'));

        const copyText = async (value) => {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(value);
                return;
            }
            const input = document.createElement('input');
            input.value = value;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            input.remove();
        };

        const flashButtonLabel = (button, label) => {
            if (!button) return;
            const original = button.dataset.originalLabel || button.textContent.trim();
            button.dataset.originalLabel = original;
            button.textContent = label;
            window.clearTimeout(button.__labelTimer);
            button.__labelTimer = window.setTimeout(() => {
                button.textContent = original;
            }, 1400);
        };

        const closeDetails = (activeItem = null) => {
            detailsItems.forEach((item) => {
                if (item !== activeItem) item.removeAttribute('open');
            });
        };

        detailsItems.forEach((item) => {
            item.addEventListener('toggle', () => {
                if (item.open) closeDetails(item);
            });
        });

        document.addEventListener('click', (event) => {
            detailsItems.forEach((item) => {
                if (!item.contains(event.target)) item.removeAttribute('open');
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeDetails();
        });

        shareButtons.forEach((button) => {
            button.addEventListener('click', async () => {
                const url = button.getAttribute('data-share-url') || window.location.href;
                const title = button.getAttribute('data-share-title') || document.title;
                try {
                    if (navigator.share) {
                        await navigator.share({ title, url });
                        flashButtonLabel(button, '{{ app()->getLocale() === 'tr' ? 'Paylaşıldı' : 'Shared' }}');
                        return;
                    }
                    await copyText(url);
                    flashButtonLabel(button, '{{ app()->getLocale() === 'tr' ? 'Kopyalandı' : 'Copied' }}');
                } catch (error) {
                    if (error?.name !== 'AbortError') console.warn(error);
                }
            });
        });

        const avatarSheet = document.getElementById('profile-avatar-sheet');
        const avatarOpenButton = document.querySelector('[data-profile-avatar-open]');
        const avatarCloseButtons = avatarSheet?.querySelectorAll('[data-profile-avatar-close]');

        const badgeSheet = document.getElementById('profile-badge-sheet');
        const badgeOpenButtons = document.querySelectorAll('[data-profile-badge-open]');
        const badgeCloseButtons = badgeSheet?.querySelectorAll('[data-profile-badge-close]');
        const badgeTitle = badgeSheet?.querySelector('[data-profile-badge-title]');
        const badgeDescription = badgeSheet?.querySelector('[data-profile-badge-description]');
        const badgePoints = badgeSheet?.querySelector('[data-profile-badge-points]');
        const badgeAwardedAt = badgeSheet?.querySelector('[data-profile-badge-awarded-at]');
        const badgePreview = badgeSheet?.querySelector('[data-profile-badge-preview]');
        const badgePreviewFace = badgeSheet?.querySelector('[data-profile-badge-preview-face]');
        const badgeBackName = badgeSheet?.querySelector('[data-profile-badge-back-name]');
        const badgeBackShape = badgeSheet?.querySelector('[data-profile-badge-back-shape]');
        const badgeFrontLayer = badgeSheet?.querySelector('[data-profile-badge-front]');
        const badgeBackLayer = badgeSheet?.querySelector('[data-profile-badge-back]');

        // Sayfa ilk açıldığında rozet popup'ı kesin kapalı başlasın.
        badgeSheet?.setAttribute('aria-hidden', 'true');

        const syncScrollLock = () => {
            const hasOpenSheet = [avatarSheet, badgeSheet].some((sheet) => sheet?.getAttribute('aria-hidden') === 'false');
            document.documentElement.classList.toggle('overflow-hidden', hasOpenSheet);
            document.body.classList.toggle('overflow-hidden', hasOpenSheet);
        };

        const showSheet = (sheet) => {
            if (!sheet) return;
            sheet.setAttribute('aria-hidden', 'false');
            sheet.removeAttribute('inert');
            syncScrollLock();
        };

        const hideSheet = (sheet) => {
            if (!sheet) return;
            sheet.setAttribute('aria-hidden', 'true');
            sheet.setAttribute('inert', '');
            syncScrollLock();
        };

        avatarOpenButton?.addEventListener('click', () => showSheet(avatarSheet));
        avatarCloseButtons?.forEach((button) => button.addEventListener('click', () => hideSheet(avatarSheet)));



        const handleBadgeImageError = (img) => {
            const fallbackUrl = img.dataset.badgeImgFallback || '';

            if (fallbackUrl && img.src !== fallbackUrl) {
                img.dataset.badgeImgFallback = '';
                img.src = fallbackUrl;
                return;
            }

            const smallBadge = img.closest('.og-badge');
            if (smallBadge) {
                smallBadge.classList.add('has-image-error');
                return;
            }

            const previewFace = img.closest('[data-profile-badge-preview-face]');
            if (previewFace) {
                const fallback = document.createElement('span');
                fallback.className = 'og-ref-badge-fallback';
                fallback.textContent = badgePreviewFace?.closest('[data-profile-badge-preview]') ? (badgeTitle?.textContent || 'R').trim().charAt(0).toUpperCase() : 'R';
                previewFace.replaceChildren(fallback);
            }
        };

        document.addEventListener('error', (event) => {
            const target = event.target;

            if (target instanceof HTMLImageElement && (target.classList.contains('og-badge__media') || target.classList.contains('og-badge-coin__media') || target.classList.contains('og-ref-badge-media'))) {
                handleBadgeImageError(target);
            }
        }, true);

        const manualRotateBadges = document.querySelectorAll('[data-manual-rotate-badge]');

        const updateBadgeVisual = (badge, angle) => {
            const normalized = ((angle % 360) + 360) % 360;
            const radians = normalized * (Math.PI / 180);
            const cos = Math.cos(radians);
            const sin = Math.sin(radians);

            const frontScale = Math.max(0.02, Math.abs(cos));
            const backScale = Math.max(0.02, Math.abs(cos));
            const frontOpacity = cos >= 0 ? Math.max(0, Math.pow(frontScale, 0.65)) : 0;
            const backOpacity = cos < 0 ? Math.max(0, Math.pow(backScale, 0.65)) : 0;

            const frontVisible = cos >= 0 ? 1 : 0;
            const lightTravel = ((sin + 1) / 2); // 0..1 soldan sağa
            const lightLeft = -18 + (lightTravel * 92); // %
            const lightOpacity = frontVisible ? (0.08 + Math.abs(sin) * 0.36) * frontOpacity : 0;
            const shadowShift = sin * 8; // px
            const highlightScale = 0.96 + (Math.abs(cos) * 0.05);

            badge.style.setProperty('--badge-front-scale', frontScale.toFixed(4));
            badge.style.setProperty('--badge-back-scale', backScale.toFixed(4));
            badge.style.setProperty('--badge-front-opacity', frontOpacity.toFixed(4));
            badge.style.setProperty('--badge-back-opacity', backOpacity.toFixed(4));
            badge.style.setProperty('--badge-light-left', `${lightLeft.toFixed(2)}%`);
            badge.style.setProperty('--badge-light-opacity', lightOpacity.toFixed(4));
            badge.style.setProperty('--badge-shadow-shift', `${shadowShift.toFixed(2)}px`);
            badge.style.setProperty('--badge-highlight-scale', highlightScale.toFixed(4));
        };

        const bindManualBadgeRotate = (badge) => {
            let isDragging = false;
            let startX = 0;
            let startRotateY = 0;
            let moved = false;

            const readNumber = (name) => {
                const value = getComputedStyle(badge).getPropertyValue(name).trim();
                return Number.parseFloat(value) || 0;
            };

            const setRotation = (y) => {
                badge.style.setProperty('--badge-rotate-x', `0deg`);
                badge.style.setProperty('--badge-rotate-y', `${y}deg`);
                updateBadgeVisual(badge, y);
            };

            badge.addEventListener('pointerdown', (event) => {
                if (event.button !== undefined && event.button !== 0) return;

                isDragging = true;
                moved = false;
                startX = event.clientX;
                startRotateY = readNumber('--badge-rotate-y');

                badge.classList.add('is-dragging');
                badge.setPointerCapture?.(event.pointerId);
            });

            badge.addEventListener('pointermove', (event) => {
                if (!isDragging) return;

                const diffX = event.clientX - startX;

                if (Math.abs(diffX) > 3) {
                    moved = true;
                }

                const nextY = startRotateY + diffX * 1.8;

                setRotation(nextY);
                event.preventDefault();
            }, { passive: false });

            const stopDrag = (event) => {
                if (!isDragging) return;

                isDragging = false;
                badge.classList.remove('is-dragging');
                badge.releasePointerCapture?.(event.pointerId);

                if (moved) {
                    badge.dataset.draggedBadge = 'true';
                    window.setTimeout(() => {
                        delete badge.dataset.draggedBadge;
                    }, 80);
                }
            };

            badge.addEventListener('pointerup', stopDrag);
            badge.addEventListener('pointercancel', stopDrag);
            badge.addEventListener('lostpointercapture', () => {
                isDragging = false;
                badge.classList.remove('is-dragging');
            });
        };

        manualRotateBadges.forEach((badge) => {
            bindManualBadgeRotate(badge);
            updateBadgeVisual(badge, 0);
        });

        badgeOpenButtons.forEach((button) => {
            button.addEventListener('click', () => {
                if (button.dataset.draggedBadge === 'true') return;
                if (badgeTitle) badgeTitle.textContent = button.getAttribute('data-badge-name') || 'Rozet';
                if (badgeDescription) badgeDescription.textContent = button.getAttribute('data-badge-description') || 'Bu rozet için açıklama eklenmemiş.';
                if (badgePoints) badgePoints.textContent = button.getAttribute('data-badge-points') || '-';
                if (badgeAwardedAt) badgeAwardedAt.textContent = button.getAttribute('data-badge-awarded-at') || '-';
                if (badgePreview) {
                    badgePreview.style.setProperty('--badge-preview-color', button.getAttribute('data-badge-color') || '#67e8f9');
                }
                if (badgePreviewFace) {
                    const iconUrl = button.getAttribute('data-badge-icon-url') || '';
                    const fallbackUrl = button.getAttribute('data-badge-icon-fallback-url') || '';
                    const fallbackLetter = button.getAttribute('data-badge-fallback-letter') || 'R';
                    const badgeName = button.getAttribute('data-badge-name') || 'Rozet';

                    if (iconUrl) {
                        const img = document.createElement('img');
                        img.className = 'og-ref-badge-media';
                        img.src = iconUrl;
                        img.alt = badgeName;
                        img.loading = 'eager';

                        if (fallbackUrl) {
                            img.dataset.badgeImgFallback = fallbackUrl;
                        }

                        badgePreviewFace.replaceChildren(img);

                        if (badgePreview && badgeBackShape) {
                            const safeIconUrl = iconUrl
                                .replace(/\\/g, '\\\\')
                                .replace(/"/g, '\\\"')
                                .replace(/'/g, "\\'");
                            badgePreview.style.setProperty('--badge-back-icon-url', `url("${safeIconUrl}")`);
                            badgePreview.style.setProperty('--badge-front-icon-url', `url("${safeIconUrl}")`);
                            badgeBackShape.classList.remove('is-fallback');
                        }
                    } else {
                        const fallback = document.createElement('span');
                        fallback.className = 'og-ref-badge-fallback';
                        fallback.textContent = fallbackLetter;
                        badgePreviewFace.replaceChildren(fallback);

                        if (badgePreview && badgeBackShape) {
                            badgePreview.style.removeProperty('--badge-back-icon-url');
                            badgePreview.style.removeProperty('--badge-front-icon-url');
                            badgeBackShape.classList.add('is-fallback');
                        }
                    }
                    if (badgeBackName) {
                        badgeBackName.textContent = button.getAttribute('data-badge-name') || 'Rozet';
                    }
                    const badgeBackBrand = badgeSheet?.querySelector('.og-ref-badge-back-brand');
                    if (badgeBackBrand) {
                        badgeBackBrand.textContent = 'OGRAFI';
                    }
                    if (badgePreview) {
                        badgePreview.style.setProperty('--badge-rotate-x', '0deg');
                        badgePreview.style.setProperty('--badge-rotate-y', '0deg');
                        updateBadgeVisual(badgePreview, 0);
                    }
                }
                showSheet(badgeSheet);
            });
        });
        badgeCloseButtons?.forEach((button) => button.addEventListener('click', () => hideSheet(badgeSheet)));

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            hideSheet(avatarSheet);
            hideSheet(badgeSheet);
        });
    })();
</script>
@endpush
