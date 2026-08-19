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
    $addUrl = function (?string $value, string $platform = 'website') use (&$sameAs) {
        $url = \App\Support\StructuredDataUrl::sameAs($value, $platform);
        if ($url !== null) {
            $sameAs[] = $url;
        }
    };

    $addUrl($user->website_url ?? null, 'website');
    $addUrl($user->social_facebook ?? null, 'facebook');
    $addUrl($user->social_instagram ?? null, 'instagram');
    $addUrl($user->social_x ?? null, 'x');
    $addUrl($user->social_tiktok ?? null, 'tiktok');
    $addUrl($user->social_youtube ?? null, 'youtube');
    $addUrl($user->social_whatsapp ?? null, 'whatsapp');
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
       OGRafi profile — Emil Kowalski inspired design pass
       - fast, purposeful motion only
       - origin-aware popovers
       - strong hierarchy / compact typography
       - full-width mobile layout
       - modal + sheet spatial consistency
       ========================================================= */

    body:has(.og-profile-page) {
        --og-page: #f7f7f8;
        --og-surface: #ffffff;
        --og-surface-soft: #f8fafc;
        --og-text: #111827;
        --og-text-2: #334155;
        --og-muted: #64748b;
        --og-faint: #94a3b8;
        --og-line: rgba(15, 23, 42, 0.09);
        --og-line-strong: rgba(15, 23, 42, 0.14);
        --og-hover: #f1f5f9;
        --og-blue: #2563eb;
        --og-blue-hover: #1d4ed8;
        --og-blue-soft: #eff6ff;
        --og-danger: #dc2626;
        --og-ease-out: cubic-bezier(0.23, 1, 0.32, 1);
        --og-ease-in-out: cubic-bezier(0.77, 0, 0.175, 1);
        background: var(--og-page);
        color: var(--og-text);
    }

    body:has(.og-profile-page) * {
        box-sizing: border-box;
    }

    .og-profile-page {
        width: 100%;
        padding: 14px 12px 28px;
        color: var(--og-text);
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }

    .og-profile-wrap {
        width: min(100%, 690px);
        margin: 0 auto;
        display: grid;
        gap: 10px;
    }

    .og-card,
    .og-tabs-card,
    .og-list-card,
    .og-empty {
        background: var(--og-surface);
        border: 1px solid var(--og-line);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .og-card {
        overflow: visible;
        border-radius: 14px;
    }

    .og-cover {
        position: relative;
        height: 188px;
        overflow: hidden;
        border-radius: 14px 14px 0 0;
        background:
            radial-gradient(circle at 24% 30%, rgba(255,255,255,.42), transparent 34%),
            linear-gradient(125deg, #7ba8ff 0%, #b9d4ff 46%, #a9c5ef 100%);
    }

    .og-cover > img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .og-cover-edit-btn {
        position: absolute;
        top: 12px;
        right: 12px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 34px;
        padding: 0 12px;
        border: 0;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.88);
        box-shadow: 0 1px 2px rgba(15,23,42,.06), 0 6px 18px rgba(15,23,42,.08);
        color: #1f2937;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
        text-decoration: none;
        backdrop-filter: blur(10px) saturate(150%);
        -webkit-backdrop-filter: blur(10px) saturate(150%);
        transition: background-color 140ms ease, transform 140ms var(--og-ease-out);
    }

    .og-cover-edit-btn iconify-icon {
        width: 15px;
        height: 15px;
        font-size: 15px;
    }

    .og-body {
        position: relative;
        padding: 0 18px 18px;
    }

    .og-topline {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        min-height: 56px;
        gap: 12px;
    }

    .og-avatar-wrap,
    .og-topline > .og-avatar-button {
        position: relative;
        z-index: 2;
        margin-top: -48px;
    }

    .og-avatar-button {
        display: block;
        padding: 0;
        border: 0;
        border-radius: 999px;
        background: transparent;
        cursor: pointer;
        touch-action: manipulation;
    }

    .og-avatar {
        display: grid;
        place-items: center;
        width: 104px;
        height: 104px;
        overflow: hidden;
        border: 4px solid var(--og-surface);
        border-radius: 999px;
        background: #e2e8f0;
        box-shadow: 0 1px 2px rgba(15,23,42,.04);
        color: #475569;
        font-size: 30px;
        font-weight: 700;
        user-select: none;
    }

    .og-avatar img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .og-avatar-edit-btn {
        position: absolute;
        right: 0;
        bottom: 3px;
        display: grid;
        place-items: center;
        width: 30px;
        height: 30px;
        border: 3px solid var(--og-surface);
        border-radius: 999px;
        background: var(--og-blue);
        color: #fff;
        text-decoration: none;
        transition: background-color 140ms ease, transform 140ms var(--og-ease-out);
    }

    .og-avatar-edit-btn iconify-icon {
        width: 14px;
        height: 14px;
        font-size: 14px;
    }

    .og-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
        margin-top: 14px;
        min-width: 0;
    }

    .og-actions form {
        margin: 0;
    }

    .og-btn,
    .og-icon-btn,
    .og-menu > summary,
    .og-sort > summary,
    .og-sheet-close,
    .og-badge-sheet-close-icon {
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
    }

    .og-btn,
    .og-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        border-radius: 9px;
        text-decoration: none;
        font-size: 12.5px;
        font-weight: 650;
        line-height: 1;
        white-space: nowrap;
        transition: background-color 140ms ease, color 140ms ease, border-color 140ms ease, transform 140ms var(--og-ease-out);
    }

    .og-btn {
        padding: 0 13px;
        border: 1px solid var(--og-line-strong);
        background: var(--og-surface);
        color: var(--og-text-2);
    }

    .og-btn--primary,
    .og-actions > .og-btn {
        border-color: var(--og-blue);
        background: var(--og-blue);
        color: #fff;
    }

    .og-icon-btn,
    .og-menu > summary {
        width: 36px;
        min-width: 36px;
        height: 36px;
        padding: 0;
        border: 1px solid var(--og-line-strong);
        border-radius: 9px;
        background: var(--og-surface);
        color: var(--og-text-2);
    }

    .og-icon-btn svg,
    .og-menu > summary svg {
        width: 17px;
        height: 17px;
    }

    .og-icon-btn > span {
        display: none;
    }

    .og-menu,
    .og-sort {
        position: relative;
    }

    .og-menu > summary,
    .og-sort > summary {
        list-style: none;
        cursor: pointer;
        user-select: none;
    }

    .og-menu > summary::-webkit-details-marker,
    .og-sort > summary::-webkit-details-marker {
        display: none;
    }

    .og-menu-panel,
    .og-sort-panel {
        position: absolute;
        z-index: 50;
        top: calc(100% + 7px);
        right: 0;
        min-width: 190px;
        padding: 5px;
        border: 1px solid var(--og-line);
        border-radius: 11px;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.12), 0 2px 8px rgba(15, 23, 42, 0.06);
        opacity: 1;
        transform: translateY(0) scale(1);
        transform-origin: top right;
        backdrop-filter: blur(14px) saturate(150%);
        -webkit-backdrop-filter: blur(14px) saturate(150%);
        transition: opacity 160ms ease, transform 180ms var(--og-ease-out);
    }

    .og-menu-panel form {
        margin: 0;
    }

    @starting-style {
        .og-menu[open] .og-menu-panel,
        .og-sort[open] .og-sort-panel {
            opacity: 0;
            transform: translateY(-4px) scale(.97);
        }
    }

    .og-menu-item,
    .og-sort-option {
        display: flex;
        width: 100%;
        min-height: 34px;
        align-items: center;
        gap: 9px;
        padding: 0 9px;
        border: 0;
        border-radius: 7px;
        background: transparent;
        color: var(--og-text-2);
        font: inherit;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.2;
        text-align: left;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 120ms ease, color 120ms ease;
    }

    .og-menu-item svg,
    .og-sort-option svg {
        width: 15px;
        height: 15px;
        flex: 0 0 15px;
        color: var(--og-muted);
    }

    .og-sort-option[aria-current="true"] {
        background: var(--og-blue-soft);
        color: var(--og-blue);
        font-weight: 600;
    }

    .og-identity {
        min-width: 0;
        margin-top: 0;
    }

    .og-name-row {
        display: flex;
        align-items: center;
        min-width: 0;
        gap: 5px;
    }

    .og-name {
        min-width: 0;
        margin: 0;
        color: var(--og-text);
        font-size: 20px;
        font-weight: 700;
        line-height: 1.18;
        letter-spacing: -0.02em;
    }

    .og-profile-subline {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 3px 9px;
        margin-top: 5px;
        min-width: 0;
        color: var(--og-muted);
        font-size: 11.5px;
        font-weight: 450;
        line-height: 1.45;
    }

    .og-profile-subline__item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        min-width: 0;
        white-space: nowrap;
    }

    .og-profile-subline__item svg,
    .og-profile-subline__item iconify-icon {
        display: block;
        width: 11px;
        min-width: 11px;
        height: 11px;
        color: var(--og-faint);
        font-size: 11px;
    }

    .og-profile-subline .og-username {
        margin: 0;
        color: var(--og-text-2);
        font-size: 12px;
        font-weight: 650;
        letter-spacing: -0.005em;
    }

    .og-stats {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px 14px;
        margin-top: 8px;
    }

    .og-stat {
        color: var(--og-text-2);
        font-size: 11.5px;
        font-weight: 450;
        line-height: 1.35;
        text-decoration: none;
    }

    .og-stat strong {
        color: var(--og-text);
        font-weight: 650;
    }

    .og-bio {
        max-width: 620px;
        margin: 8px 0 0;
        color: var(--og-text-2);
        font-size: 13px;
        font-weight: 400;
        line-height: 1.5;
    }

    .og-links {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 10px;
    }

    .og-chip,
    .og-website-link {
        display: inline-flex;
        min-width: 0;
        align-items: center;
        gap: 5px;
        color: var(--og-blue);
        font-size: 11.5px;
        font-weight: 550;
        line-height: 1.2;
        text-decoration: none;
    }

    .og-website-link {
        max-width: 220px;
        padding-right: 4px;
    }

    .og-website-link > span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .og-website-link > svg,
    .og-favicon {
        display: block;
        width: 14px;
        min-width: 14px;
        height: 14px;
        object-fit: contain;
    }

    .og-social {
        display: inline-grid;
        place-items: center;
        width: 28px;
        min-width: 28px;
        height: 28px;
        padding: 0;
        overflow: visible;
        border: 0;
        border-radius: 7px;
        background: transparent;
        text-decoration: none;
        opacity: 1;
        visibility: visible;
        transition: background-color 120ms ease, transform 120ms var(--og-ease-out);
    }

    .og-social svg {
        display: block !important;
        width: 17px !important;
        min-width: 17px !important;
        height: 17px !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .og-social--facebook { color: #1877F2; }
    .og-social--instagram { color: #E4405F; }
    .og-social--x { color: #000000; }
    .og-social--tiktok { color: #111111; }
    .og-social--youtube { color: #FF0000; }

    .og-badges {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 10px;
    }

    .og-badge {
        position: relative;
        display: grid;
        place-items: center;
        width: 30px;
        min-width: 30px;
        height: 30px;
        padding: 0;
        overflow: visible;
        border: 0;
        border-radius: 8px;
        background: transparent;
        cursor: pointer;
        touch-action: manipulation;
        transition: background-color 120ms ease, transform 120ms var(--og-ease-out);
    }

    .og-badge::before,
    .og-badge::after {
        display: none;
        content: none;
    }

    .og-badge__face {
        display: grid;
        place-items: center;
        width: 26px;
        height: 26px;
        overflow: hidden;
        border-radius: 7px;
        background: color-mix(in srgb, var(--badge-color, #67e8f9) 16%, white);
    }

    .og-badge__media {
        display: block;
        width: 22px;
        height: 22px;
        object-fit: contain;
    }

    .og-badge__fallback,
    .og-badge__fallback--backup {
        display: grid;
        place-items: center;
        width: 100%;
        height: 100%;
        color: var(--og-text);
        font-size: 10px;
        font-weight: 750;
    }

    .og-badge__media + .og-badge__fallback--backup {
        display: none;
    }

    .og-tabs-card {
        position: relative;
        display: flex;
        min-height: 46px;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 0 8px 0 10px;
        border-radius: 12px;
    }

    .og-tabs {
        display: flex;
        min-width: 0;
        align-self: stretch;
        align-items: stretch;
        gap: 1px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .og-tabs::-webkit-scrollbar {
        display: none;
    }

    .og-tab {
        position: relative;
        display: inline-flex;
        min-width: max-content;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        color: var(--og-muted);
        font-size: 11.5px;
        font-weight: 550;
        line-height: 1;
        text-decoration: none;
        transition: color 120ms ease, background-color 120ms ease, transform 120ms var(--og-ease-out);
    }

    .og-tab[aria-current="page"] {
        color: var(--og-blue);
        font-weight: 650;
    }

    .og-tab[aria-current="page"]::after {
        position: absolute;
        right: 8px;
        bottom: -1px;
        left: 8px;
        height: 2px;
        border-radius: 999px 999px 0 0;
        background: var(--og-blue);
        content: "";
    }

    .og-sort > summary {
        display: inline-flex;
        height: 32px;
        align-items: center;
        gap: 5px;
        padding: 0 9px;
        border: 1px solid var(--og-line);
        border-radius: 8px;
        background: var(--og-surface);
        color: var(--og-text-2);
        font-size: 11.5px;
        font-weight: 550;
        line-height: 1;
        transition: background-color 120ms ease, transform 120ms var(--og-ease-out);
    }

    .og-sort > summary svg {
        width: 14px;
        height: 14px;
    }

    .og-sort-panel {
        min-width: 150px;
    }

    .og-content {
        display: grid;
        gap: 10px;
        min-width: 0;
    }

    .og-post-wrapper {
        min-width: 0;
    }

    .og-list-card,
    .og-empty {
        border-radius: 12px;
    }

    .og-list-card {
        overflow: hidden;
    }

    .og-list-head {
        padding: 14px 15px 11px;
        border-bottom: 1px solid var(--og-line);
    }

    .og-list-title {
        margin: 0;
        color: var(--og-text);
        font-size: 13px;
        font-weight: 650;
        line-height: 1.3;
    }

    .og-list-desc {
        margin: 3px 0 0;
        color: var(--og-muted);
        font-size: 11px;
        line-height: 1.35;
    }

    .og-list-link {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 11px 14px;
        color: inherit;
        text-decoration: none;
        transition: background-color 120ms ease;
    }

    .og-list-link + .og-list-link {
        border-top: 1px solid var(--og-line);
    }

    .og-list-avatar {
        display: grid;
        place-items: center;
        width: 36px;
        min-width: 36px;
        height: 36px;
        overflow: hidden;
        border-radius: 999px;
        background: #e2e8f0;
        color: #475569;
        font-size: 12px;
        font-weight: 650;
    }

    .og-list-avatar img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .og-list-main {
        display: block;
        min-width: 0;
        flex: 1;
    }

    .og-list-name {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
        color: var(--og-text);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.3;
    }

    .og-list-muted,
    .og-comment-post {
        color: var(--og-muted);
        font-size: 10.5px;
        font-weight: 400;
        line-height: 1.4;
    }

    .og-comment-post {
        display: block;
        margin-top: 2px;
    }

    .og-comment-text {
        display: block;
        margin-top: 4px;
        color: var(--og-text-2);
        font-size: 11.5px;
        line-height: 1.48;
    }

    .og-empty {
        min-height: 84px;
        display: grid;
        place-items: center;
        padding: 18px;
        color: var(--og-muted);
        font-size: 12px;
        text-align: center;
    }

    /* ---------- dialogs / sheets ---------- */
    .og-sheet {
        position: fixed;
        inset: 0;
        z-index: 1000;
        visibility: hidden;
        pointer-events: none;
    }

    .og-sheet[aria-hidden="false"] {
        visibility: visible;
        pointer-events: auto;
    }

    .og-sheet-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.48);
        opacity: 0;
        backdrop-filter: blur(14px) saturate(85%);
        -webkit-backdrop-filter: blur(14px) saturate(85%);
        transition: opacity 180ms ease;
    }

    .og-sheet[aria-hidden="false"] .og-sheet-backdrop {
        opacity: 1;
    }

    .og-sheet-panel {
        position: absolute;
        left: 50%;
        top: 50%;
        width: min(420px, calc(100vw - 28px));
        max-height: min(82dvh, 680px);
        overflow: auto;
        padding: 18px;
        border: 1px solid rgba(255,255,255,.42);
        border-radius: 18px;
        background: rgba(255,255,255,.96);
        box-shadow: 0 26px 72px rgba(15,23,42,.22), 0 4px 16px rgba(15,23,42,.10);
        opacity: 0;
        transform: translate(-50%, -48%) scale(.97);
        transition: opacity 180ms ease, transform 220ms var(--og-ease-out);
    }

    .og-sheet[aria-hidden="false"] .og-sheet-panel {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }

    .og-sheet-handle {
        display: none;
    }

    .og-sheet-title {
        margin: 0;
        color: var(--og-text);
        font-size: 15px;
        font-weight: 650;
        line-height: 1.3;
        letter-spacing: -0.01em;
    }

    .og-avatar-preview {
        display: grid;
        place-items: center;
        width: min(270px, 75vw);
        aspect-ratio: 1;
        margin: 14px auto 0;
        overflow: hidden;
        border-radius: 18px;
        background: #e2e8f0;
        color: #475569;
        font-size: 42px;
        font-weight: 700;
    }

    .og-avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .og-sheet-close {
        display: inline-flex;
        width: 100%;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        margin-top: 14px;
        padding: 0 14px;
        border: 0;
        border-radius: 10px;
        background: var(--og-blue);
        color: #fff;
        font-size: 12.5px;
        font-weight: 650;
        cursor: pointer;
        transition: background-color 140ms ease, transform 140ms var(--og-ease-out);
    }

    .og-badge-sheet-panel {
        width: min(430px, calc(100vw - 28px));
        padding: 18px;
    }

    .og-badge-sheet-close-icon {
        position: absolute;
        z-index: 3;
        top: 12px;
        right: 12px;
        display: grid;
        place-items: center;
        width: 32px;
        height: 32px;
        padding: 0;
        border: 1px solid var(--og-line-strong);
        border-radius: 9px;
        background: #fff;
        color: var(--og-text-2);
        cursor: pointer;
        transition: background-color 120ms ease, border-color 120ms ease, transform 120ms var(--og-ease-out);
    }

    .og-badge-sheet-close-icon svg {
        display: block;
        width: 15px;
        height: 15px;
    }

    .og-badge-sheet-summary {
        display: grid;
        grid-template-columns: 64px minmax(0, 1fr);
        align-items: center;
        gap: 13px;
        padding-right: 38px;
    }

    .og-badge-sheet-media {
        position: relative;
        display: grid;
        place-items: center;
        min-height: 64px;
    }

    .og-ref-badge-stage {
        --badge-preview-color: #67e8f9;
        position: relative;
        width: 58px;
        height: 58px;
        perspective: 600px;
    }

    .og-ref-badge-rotor {
        position: absolute;
        inset: 0;
        transform-style: preserve-3d;
        transform: rotateX(var(--badge-rotate-x, 0deg)) rotateY(var(--badge-rotate-y, 0deg));
    }

    .og-ref-badge-layer {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        overflow: hidden;
        border-radius: 14px;
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
    }

    .og-ref-badge-layer--front {
        background: color-mix(in srgb, var(--badge-preview-color) 24%, white);
        box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--badge-preview-color) 42%, transparent);
    }

    .og-ref-badge-layer--back {
        background: color-mix(in srgb, var(--badge-preview-color) 34%, #fff);
        transform: rotateY(180deg);
    }

    .og-ref-badge-glow,
    .og-ref-badge-shadow {
        display: none;
    }

    .og-ref-badge-content,
    .og-ref-badge-back-shape {
        display: grid;
        place-items: center;
        width: 100%;
        height: 100%;
    }

    .og-ref-badge-media {
        display: block;
        width: 44px;
        height: 44px;
        object-fit: contain;
    }

    .og-ref-badge-fallback {
        display: grid;
        place-items: center;
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: rgba(255,255,255,.84);
        color: var(--og-text);
        font-size: 18px;
        font-weight: 750;
    }

    .og-ref-badge-back-copy {
        display: grid;
        gap: 2px;
        padding: 8px;
        text-align: center;
    }

    .og-ref-badge-back-name {
        color: var(--og-text);
        font-size: 9px;
        font-weight: 700;
        line-height: 1.2;
    }

    .og-ref-badge-back-brand {
        color: var(--og-muted);
        font-size: 7px;
        letter-spacing: .08em;
    }

    .og-badge-sheet-copy {
        min-width: 0;
    }

    .og-badge-sheet-subtitle {
        margin: 5px 0 0;
        color: var(--og-muted);
        font-size: 12px;
        line-height: 1.45;
    }

    .og-badge-sheet-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 14px;
    }

    .og-badge-sheet-meta .og-chip {
        min-height: 28px;
        padding: 0 9px;
        border-radius: 8px;
        background: var(--og-surface-soft);
        color: var(--og-muted);
        font-size: 11px;
        font-weight: 500;
    }

    .og-badge-sheet-close-text {
        margin-top: 14px;
        background: var(--og-blue);
        color: #fff;
    }

    /* ---------- precise hover / press feedback ---------- */
    @media (hover: hover) and (pointer: fine) {
        .og-cover-edit-btn:hover,
        .og-btn:not(.og-btn--primary):hover,
        .og-icon-btn:hover,
        .og-menu > summary:hover,
        .og-sort > summary:hover,
        .og-social:hover,
        .og-badge:hover,
        .og-badge-sheet-close-icon:hover {
            background: var(--og-hover);
        }

        .og-btn--primary:hover,
        .og-actions > .og-btn:hover,
        .og-sheet-close:hover,
        .og-badge-sheet-close-text:hover,
        .og-avatar-edit-btn:hover {
            background: var(--og-blue-hover);
        }

        .og-menu-item:hover,
        .og-menu-item:focus-visible,
        .og-sort-option:hover,
        .og-sort-option:focus-visible,
        .og-list-link:hover,
        .og-tab:hover {
            background: var(--og-hover);
        }

        .og-website-link:hover {
            color: var(--og-blue-hover);
        }
    }

    .og-cover-edit-btn:active,
    .og-avatar-edit-btn:active,
    .og-btn:active,
    .og-icon-btn:active,
    .og-menu > summary:active,
    .og-sort > summary:active,
    .og-social:active,
    .og-badge:active,
    .og-tab:active,
    .og-sheet-close:active,
    .og-badge-sheet-close-icon:active {
        transform: scale(.97);
    }

    .og-btn:focus-visible,
    .og-icon-btn:focus-visible,
    .og-menu > summary:focus-visible,
    .og-sort > summary:focus-visible,
    .og-menu-item:focus-visible,
    .og-sort-option:focus-visible,
    .og-social:focus-visible,
    .og-badge:focus-visible,
    .og-tab:focus-visible,
    .og-sheet-close:focus-visible,
    .og-badge-sheet-close-icon:focus-visible,
    .og-cover-edit-btn:focus-visible,
    .og-avatar-button:focus-visible {
        outline: 2px solid color-mix(in srgb, var(--og-blue) 62%, white);
        outline-offset: 2px;
    }

    /* ---------- dark mode ---------- */
    body.dark:has(.og-profile-page),
    .dark body:has(.og-profile-page) {
        --og-page: #0b0d10;
        --og-surface: #12161b;
        --og-surface-soft: #181d23;
        --og-text: #f8fafc;
        --og-text-2: #dbe4ee;
        --og-muted: #9aa8b8;
        --og-faint: #708094;
        --og-line: rgba(255,255,255,.08);
        --og-line-strong: rgba(255,255,255,.14);
        --og-hover: rgba(255,255,255,.07);
        --og-blue-soft: rgba(37,99,235,.14);
    }

    body.dark .og-card,
    body.dark .og-tabs-card,
    body.dark .og-list-card,
    body.dark .og-empty,
    body.dark .og-btn,
    body.dark .og-icon-btn,
    body.dark .og-menu > summary,
    body.dark .og-sort > summary,
    body.dark .og-menu-panel,
    body.dark .og-sort-panel,
    body.dark .og-sheet-panel,
    .dark .og-card,
    .dark .og-tabs-card,
    .dark .og-list-card,
    .dark .og-empty,
    .dark .og-btn,
    .dark .og-icon-btn,
    .dark .og-menu > summary,
    .dark .og-sort > summary,
    .dark .og-menu-panel,
    .dark .og-sort-panel,
    .dark .og-sheet-panel {
        background-color: var(--og-surface);
        color: var(--og-text);
        border-color: var(--og-line);
    }

    body.dark .og-badge-sheet-close-icon,
    .dark .og-badge-sheet-close-icon {
        background: var(--og-surface-soft);
        color: var(--og-text);
    }

    body.dark .og-btn--primary,
    body.dark .og-actions > .og-btn,
    .dark .og-btn--primary,
    .dark .og-actions > .og-btn {
        border-color: var(--og-blue);
        background: var(--og-blue);
        color: #ffffff;
    }

    body.dark .og-social--x svg path,
    body.dark .og-social--tiktok svg path,
    .dark .og-social--x svg path,
    .dark .og-social--tiktok svg path {
        fill: #ffffff !important;
    }

    /* ---------- responsive ---------- */
    @media (max-width: 640px) {
        body:has(.og-profile-page) {
            background: var(--og-surface);
        }

        .og-profile-page {
            padding: 0 0 20px;
        }

        .og-profile-wrap {
            width: 100%;
            gap: 8px;
        }

        .og-card,
        .og-tabs-card,
        .og-list-card,
        .og-empty {
            border-right: 0;
            border-left: 0;
            border-radius: 0;
            box-shadow: none;
        }

        .og-cover {
            height: 154px;
            border-radius: 0;
        }

        .og-cover-edit-btn {
            top: 10px;
            right: 10px;
            min-height: 32px;
            padding: 0 10px;
            font-size: 11.5px;
        }

        .og-body {
            padding: 0 14px 15px;
        }

        .og-topline {
            min-height: 48px;
        }

        .og-avatar-wrap,
        .og-topline > .og-avatar-button {
            margin-top: -42px;
        }

        .og-avatar {
            width: 88px;
            height: 88px;
            border-width: 4px;
            font-size: 26px;
        }

        .og-avatar-edit-btn {
            width: 28px;
            height: 28px;
            right: -1px;
            bottom: 1px;
        }

        .og-actions {
            gap: 6px;
            margin-top: 10px;
        }

        .og-btn,
        .og-icon-btn,
        .og-menu > summary {
            min-height: 34px;
            height: 34px;
        }

        .og-btn {
            padding-inline: 11px;
            font-size: 12px;
        }

        .og-icon-btn,
        .og-menu > summary {
            width: 34px;
            min-width: 34px;
        }

        .og-name {
            font-size: 18px;
        }

        .og-profile-subline {
            gap: 2px 7px;
            margin-top: 4px;
            font-size: 10.5px;
            line-height: 16px;
        }

        .og-profile-subline .og-username {
            margin: 0;
            padding: 0;
            font-size: 11.5px;
            font-weight: 700;
            line-height: 16px;
        }

        .og-profile-subline__item {
            min-height: 16px;
            line-height: 16px;
        }

        .og-profile-subline__item svg,
        .og-profile-subline__item iconify-icon {
            width: 10px;
            min-width: 10px;
            height: 10px;
            font-size: 10px;
        }

        .og-stats {
            gap: 4px 11px;
            margin-top: 7px;
        }

        .og-stat {
            font-size: 10.5px;
        }

        .og-bio {
            margin-top: 7px;
            font-size: 12px;
            line-height: 1.48;
        }

        .og-links {
            gap: 4px;
            margin-top: 8px;
        }

        .og-chip,
        .og-website-link {
            font-size: 10.5px;
        }

        .og-social {
            width: 26px;
            min-width: 26px;
            height: 26px;
        }

        .og-social svg {
            width: 16px !important;
            min-width: 16px !important;
            height: 16px !important;
        }

        .og-badges {
            margin-top: 8px;
        }

        .og-badge {
            width: 28px;
            min-width: 28px;
            height: 28px;
        }

        .og-badge__face {
            width: 24px;
            height: 24px;
        }

        .og-badge__media {
            width: 20px;
            height: 20px;
        }

        .og-tabs-card {
            min-height: 44px;
            padding: 0 6px 0 4px;
        }

        .og-tab {
            padding: 0 8px;
            font-size: 11px;
        }

        .og-sort > summary {
            width: 32px;
            min-width: 32px;
            height: 32px;
            justify-content: center;
            padding: 0;
        }

        .og-sort > summary > span {
            display: none;
        }

        .og-sort-panel {
            min-width: 146px;
        }

        .og-list-card,
        .og-empty {
            border-top: 1px solid var(--og-line);
            border-bottom: 1px solid var(--og-line);
        }

        .og-sheet-panel,
        .og-badge-sheet-panel {
            left: 0;
            right: 0;
            top: auto;
            bottom: 0;
            width: 100%;
            max-width: none;
            max-height: 84dvh;
            padding: 16px 16px calc(16px + env(safe-area-inset-bottom));
            border-right: 0;
            border-bottom: 0;
            border-left: 0;
            border-radius: 18px 18px 0 0;
            opacity: 1;
            transform: translateY(100%);
            transition: transform 240ms cubic-bezier(0.32, 0.72, 0, 1);
        }

        .og-sheet[aria-hidden="false"] .og-sheet-panel,
        .og-sheet[aria-hidden="false"] .og-badge-sheet-panel {
            transform: translateY(0);
        }

        .og-sheet-handle,
        .og-badge-sheet-handle {
            display: block;
            width: 34px;
            height: 4px;
            margin: -6px auto 12px;
            border-radius: 999px;
            background: #cbd5e1;
        }

        .og-badge-sheet-close-icon {
            top: 12px;
            right: 12px;
        }

        .og-badge-sheet-summary {
            grid-template-columns: 54px minmax(0, 1fr);
            gap: 12px;
            padding-right: 38px;
        }

        .og-ref-badge-stage {
            width: 52px;
            height: 52px;
        }

        .og-ref-badge-media {
            width: 40px;
            height: 40px;
        }

        .og-badge-sheet-subtitle {
            font-size: 11.5px;
        }
    }

    @media (max-width: 380px) {
        .og-body {
            padding-inline: 12px;
        }

        .og-profile-subline {
            gap: 2px 6px;
        }

        .og-tab {
            padding-inline: 7px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .og-profile-page *,
        .og-sheet-backdrop,
        .og-sheet-panel,
        .og-menu-panel,
        .og-sort-panel {
            scroll-behavior: auto !important;
            transition-duration: 0.01ms !important;
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
        }

        .og-sheet-panel,
        .og-sheet[aria-hidden="false"] .og-sheet-panel {
            transform: translate(-50%, -50%) !important;
        }

    }

    @media (prefers-reduced-motion: reduce) and (max-width: 640px) {
        .og-sheet-panel,
        .og-sheet[aria-hidden="false"] .og-sheet-panel {
            transform: none !important;
        }
    }

    @media (prefers-reduced-transparency: reduce) {
        .og-cover-edit-btn,
        .og-menu-panel,
        .og-sort-panel,
        .og-sheet-panel {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }

        .og-sheet-backdrop {
            background: rgba(15, 23, 42, 0.68);
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
        }
    }

    @media (prefers-contrast: more) {
        .og-card,
        .og-tabs-card,
        .og-list-card,
        .og-empty,
        .og-btn,
        .og-icon-btn,
        .og-menu > summary,
        .og-sort > summary,
        .og-menu-panel,
        .og-sort-panel,
        .og-sheet-panel,
        .og-badge-sheet-close-icon {
            border-color: currentColor;
        }
    }


    /* =========================================================
       FINAL CONTROL FIXES
       - Confirm/primary actions stay blue
       - Close X is bordered and turns gray on mouse hover
       - Three-dot menu button is perfectly centered/aligned
       ========================================================= */
    .og-profile-page .og-sheet-close,
    .og-profile-page .og-badge-sheet-close-text,
    .og-profile-page .og-btn--primary,
    .og-profile-page .og-actions > .og-btn {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }

    .og-profile-page .og-badge-sheet-close-icon {
        display: inline-grid !important;
        place-items: center !important;
        width: 32px !important;
        min-width: 32px !important;
        height: 32px !important;
        min-height: 32px !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 1px solid #d1d5db !important;
        border-radius: 9px !important;
        background: #ffffff !important;
        color: #111827 !important;
        line-height: 0 !important;
        box-shadow: none !important;
    }

    .og-profile-page .og-badge-sheet-close-icon svg {
        display: block !important;
        width: 15px !important;
        height: 15px !important;
        margin: 0 !important;
        color: currentColor !important;
    }

    .og-profile-page .og-actions .og-menu {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 auto !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .og-profile-page .og-actions .og-menu > summary.og-menu-summary-icon-only {
        display: inline-grid !important;
        place-items: center !important;
        box-sizing: border-box !important;
        width: 36px !important;
        min-width: 36px !important;
        height: 36px !important;
        min-height: 36px !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 1px solid #d1d5db !important;
        border-radius: 9px !important;
        background: #ffffff !important;
        color: #475569 !important;
        line-height: 0 !important;
        list-style: none !important;
        box-shadow: none !important;
        vertical-align: middle !important;
    }

    .og-profile-page .og-actions .og-menu > summary.og-menu-summary-icon-only::-webkit-details-marker {
        display: none !important;
    }

    .og-profile-page .og-actions .og-menu > summary.og-menu-summary-icon-only svg {
        display: block !important;
        width: 17px !important;
        height: 17px !important;
        margin: 0 !important;
        color: currentColor !important;
        fill: currentColor !important;
        transform: none !important;
    }

    .og-profile-page .og-actions .og-menu > summary.og-menu-summary-icon-only svg circle {
        fill: currentColor !important;
        stroke: none !important;
    }

    @media (hover: hover) and (pointer: fine) {
        .og-profile-page .og-sheet-close:hover,
        .og-profile-page .og-badge-sheet-close-text:hover,
        .og-profile-page .og-btn--primary:hover,
        .og-profile-page .og-actions > .og-btn:hover {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
        }

        .og-profile-page .og-badge-sheet-close-icon:hover,
        .og-profile-page .og-actions .og-menu > summary.og-menu-summary-icon-only:hover {
            background: #f3f4f6 !important;
            border-color: #cbd5e1 !important;
            color: #111827 !important;
        }
    }

    .og-profile-page .og-sheet-close:active,
    .og-profile-page .og-badge-sheet-close-text:active,
    .og-profile-page .og-btn--primary:active,
    .og-profile-page .og-actions > .og-btn:active,
    .og-profile-page .og-badge-sheet-close-icon:active,
    .og-profile-page .og-actions .og-menu > summary.og-menu-summary-icon-only:active {
        transform: scale(.97) !important;
    }

    @media (max-width: 640px) {
        .og-profile-page .og-actions .og-menu > summary.og-menu-summary-icon-only {
            width: 34px !important;
            min-width: 34px !important;
            height: 34px !important;
            min-height: 34px !important;
        }
    }


    /* =========================================================
       Mobil profil: kullanici adi ust satir, detaylar alt satir.
       Mobil dis/icerik sag-sol bosluklari tamamen kaldirildi.
       ========================================================= */
    @media (max-width: 640px) {
        .og-profile-page {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .og-profile-wrap,
        .og-card,
        .og-tabs-card,
        .og-content,
        .og-list-card,
        .og-empty {
            width: 100% !important;
            max-width: none !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .og-body {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .og-tabs-card {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .og-profile-subline {
            display: flex !important;
            width: 100% !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            column-gap: 7px !important;
            row-gap: 2px !important;
            margin-top: 4px !important;
        }

        .og-profile-subline .og-username {
            flex: 0 0 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 16px !important;
        }

        .og-profile-subline__item:not(.og-username) {
            margin: 0 !important;
            padding: 0 !important;
            min-height: 16px !important;
            line-height: 16px !important;
        }
    }

    @media (max-width: 380px) {
        .og-body {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
    }

    /* =========================================================
       Mobil: profil kartlarini viewport kenarlarina tam yasla.
       Layout/container tarafindan gelen 14px benzeri dis gutter'lari
       da kirar; profil, sekmeler ve içerik kartlari ekranin iki kenarina
       kadar uzanir.
       ========================================================= */
    @media (max-width: 640px) {
        .og-profile-page {
            position: relative !important;
            left: 50% !important;
            width: 100vw !important;
            max-width: 100vw !important;
            margin-left: -50vw !important;
            margin-right: -50vw !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            overflow-x: clip !important;
        }

        .og-profile-wrap {
            width: 100vw !important;
            max-width: 100vw !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .og-card,
        .og-tabs-card,
        .og-content,
        .og-list-card,
        .og-empty {
            width: 100% !important;
            max-width: none !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
        }

        /* Kart içeriği hâlâ okunabilir kalsın; kaldırılan boşluk dış gutter'dır. */
        .og-body {
            padding-left: 14px !important;
            padding-right: 14px !important;
        }

        .og-tabs-card {
            padding-left: 14px !important;
            padding-right: 14px !important;
        }
    }

    @media (max-width: 380px) {
        .og-body,
        .og-tabs-card {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
    }



    /* FINAL: Rozet modalındaki Anladım butonu her durumda mavi. */
    .og-profile-page button.og-sheet-close.og-badge-sheet-close-text[data-profile-badge-close] {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        min-height: 40px !important;
        padding: 0 16px !important;
        border: 1px solid #2563eb !important;
        border-radius: 10px !important;
        background-color: #2563eb !important;
        background-image: none !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-weight: 600 !important;
        box-shadow: none !important;
        opacity: 1 !important;
    }

    @media (hover: hover) and (pointer: fine) {
        .og-profile-page button.og-sheet-close.og-badge-sheet-close-text[data-profile-badge-close]:hover {
            border-color: #1d4ed8 !important;
            background-color: #1d4ed8 !important;
            background-image: none !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
    }


    /* =========================================================
       FINAL INTERACTION LAYER
       Fare ile üzerine gelinen tüm tıklanabilir nötr kontroller gri olur.
       Primary aksiyonlar mavi kalır; focus ve active durumları belirgindir.
       ========================================================= */

    .og-profile-page a[href],
    .og-profile-page button:not(:disabled),
    .og-profile-page summary,
    .og-profile-page .og-badge {
        cursor: pointer !important;
        -webkit-tap-highlight-color: transparent;
    }

    .og-profile-page :is(
        .og-icon-btn,
        .og-menu > summary,
        .og-menu-item,
        .og-sort > summary,
        .og-sort-option,
        .og-tab,
        .og-chip--link,
        .og-social,
        .og-badge,
        .og-avatar-button,
        .og-avatar-edit-btn,
        .og-cover-edit-btn,
        .og-list-link,
        .og-sheet-close:not(.og-badge-sheet-close-text),
        .og-badge-sheet-close-icon
    ) {
        transition:
            background-color 130ms ease,
            border-color 130ms ease,
            color 130ms ease,
            transform 130ms cubic-bezier(.23, 1, .32, 1),
            box-shadow 130ms ease !important;
    }

    @media (hover: hover) and (pointer: fine) {
        .og-profile-page :is(
            .og-icon-btn,
            .og-menu > summary,
            .og-menu-item,
            .og-sort > summary,
            .og-sort-option,
            .og-tab,
            .og-chip--link,
            .og-social,
            .og-badge,
            .og-avatar-button,
            .og-avatar-edit-btn,
            .og-cover-edit-btn,
            .og-list-link,
            .og-sheet-close:not(.og-badge-sheet-close-text),
            .og-badge-sheet-close-icon
        ):hover {
            background-color: #f3f4f6 !important;
            border-color: #d1d5db !important;
        }

        /* Avatar yuvarlak olduğu için hover alanını da yuvarlak tut. */
        .og-profile-page .og-avatar-button:hover {
            border-radius: 999px !important;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, .08) !important;
        }

        /* Rozet: gri hover + hafif kaldırma. */
        .og-profile-page .og-badge:hover {
            transform: translateY(-1px) !important;
        }

        /* Liste satırları tıklanabilir olduğunu belli etsin. */
        .og-profile-page .og-list-link:hover {
            border-radius: 8px !important;
        }

        /* Primary aksiyonlar griye dönmez, mavi kalır. */
        .og-profile-page :is(
            .og-btn--primary,
            .og-actions > .og-btn,
            button.og-sheet-close.og-badge-sheet-close-text[data-profile-badge-close]
        ):hover {
            background-color: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
    }

    /* Klavye ile gezen kullanıcı da tıklanabilir alanı net görsün. */
    .og-profile-page :is(
        a[href],
        button:not(:disabled),
        summary,
        .og-badge
    ):focus-visible {
        outline: 2px solid #2563eb !important;
        outline-offset: 2px !important;
    }

    /* Basınca anlık geri bildirim. */
    .og-profile-page :is(
        .og-btn,
        .og-icon-btn,
        .og-menu > summary,
        .og-menu-item,
        .og-sort > summary,
        .og-sort-option,
        .og-tab,
        .og-chip--link,
        .og-social,
        .og-badge,
        .og-avatar-button,
        .og-avatar-edit-btn,
        .og-cover-edit-btn,
        .og-list-link,
        .og-sheet-close,
        .og-badge-sheet-close-icon
    ):active {
        transform: scale(.97) !important;
    }

    /* Dark mode nötr hover. */
    @media (hover: hover) and (pointer: fine) {
        body.dark .og-profile-page :is(
            .og-icon-btn,
            .og-menu > summary,
            .og-menu-item,
            .og-sort > summary,
            .og-sort-option,
            .og-tab,
            .og-chip--link,
            .og-social,
            .og-badge,
            .og-avatar-button,
            .og-avatar-edit-btn,
            .og-cover-edit-btn,
            .og-list-link,
            .og-sheet-close:not(.og-badge-sheet-close-text),
            .og-badge-sheet-close-icon
        ):hover,
        .dark .og-profile-page :is(
            .og-icon-btn,
            .og-menu > summary,
            .og-menu-item,
            .og-sort > summary,
            .og-sort-option,
            .og-tab,
            .og-chip--link,
            .og-social,
            .og-badge,
            .og-avatar-button,
            .og-avatar-edit-btn,
            .og-cover-edit-btn,
            .og-list-link,
            .og-sheet-close:not(.og-badge-sheet-close-text),
            .og-badge-sheet-close-icon
        ):hover {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
        }
    }


    /* Yorumlar / Takipçiler / Takip: başlık-sayaç kartı yok, içerik doğrudan akar. */
    .og-direct-list {
        width: 100%;
        margin: 0;
        padding: 0;
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        overflow: visible;
    }

    .og-direct-list .og-list-link {
        width: 100%;
        margin: 0;
        border-radius: 0;
    }

    .og-direct-empty {
        width: 100%;
        margin: 0;
        padding: 18px 14px;
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        color: var(--og-muted);
        font-size: 12px;
        line-height: 1.45;
        text-align: center;
    }

    @media (max-width: 640px) {
        .og-direct-list,
        .og-direct-empty {
            width: 100% !important;
            max-width: none !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
    }

</style>

@endpush


@section('content')
    @php
        // Harici bir stok foto URL'sine varsayilan olarak guvenmek yerine (yavas/engellenmis
        // yuklemede bos/kirik gri bir alan olarak goruluyordu), kullanicinin gercekten
        // yukledigi bir kapak fotografi yoksa hic <img> render etmiyoruz; .og-cover'daki
        // CSS gradient zemin boylece dogrudan gorunur.
        $hasCustomCover = !empty($user->cover_photo_path) || !empty($user->cover_image);
        $coverUrl = $hasCustomCover ? ($user->cover_photo_url ?? $user->cover_image) : null;
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
        $canViewFollowings = (bool) ($canViewFollowings ?? true);
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
                ? \Illuminate\Support\Carbon::parse($joinedSource)->format('m/Y') . "'da katıldı."
                : \Illuminate\Support\Carbon::parse($joinedSource)->format('m/Y') . ' joined')
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
            'facebook' => $user->social_facebook ?? $user->facebook_url ?? $user->facebook ?? null,
            'instagram' => $user->social_instagram ?? $user->instagram_url ?? $user->instagram ?? null,
            'x' => $user->social_x ?? $user->social_twitter ?? $user->x_url ?? $user->twitter_url ?? null,
            'tiktok' => $user->social_tiktok ?? $user->tiktok_url ?? $user->tiktok ?? null,
            'youtube' => $user->social_youtube ?? $user->youtube_url ?? $user->youtube ?? null,
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

            // Hicbir dosya/URL adayi bulunamadi: "icon" sutunu Filament tarafinda
            // Heroicon adi olarak doldurulmus olabilir (or. "heroicon-m-star").
            // Bunu bozuk bir dosya URL'si gibi ele alip 404 fallback'ine
            // dusmemek icin blade-icons paketiyle inline SVG olarak cozmeyi dene.
            $iconName = trim((string) ($badge->icon ?? ''));
            if ($iconName !== '' && function_exists('svg') && !str_contains($iconName, '/') && !str_contains($iconName, '.')) {
                try {
                    $inlineSvg = svg($iconName, 'og-badge__inline-svg')->toHtml();
                    if ($inlineSvg !== '') {
                        // JS taraflı rozet modali da bu URL'yi <img src> olarak
                        // kullaniyor; bu yuzden inline SVG'yi de data-URI'ye
                        // cevirip 'url' olarak donduruyoruz. "currentColor" ise
                        // <img> baglaminda CSS'ten renk almayacagi icin sabit
                        // bir renge cevirmemiz gerekiyor - rozetin ic plakasi
                        // beyaz oldugundan beyaz degil, koyu bir renk kullanmaliyiz
                        // (yoksa ikon beyaz zemin uzerinde beyaz kalip görünmez olur).
                        $coloredSvg = str_replace('currentColor', '#111827', $inlineSvg);

                        return [
                            'url' => 'data:image/svg+xml;base64,' . base64_encode($coloredSvg),
                            'fallback' => null,
                        ];
                    }
                } catch (\Throwable $e) {
                    // Gecersiz/bilinmeyen heroicon adi - sessizce yoksay.
                }
            }

            return [
                'url' => null,
                'fallback' => null,
            ];
        };

        $profileHandle = filled($user->username ?? null)
            ? '@' . $user->username
            : '@' . \Illuminate\Support\Str::slug((string) ($user->name ?? 'user'));
        $sortOptions = [
            'new' => app()->getLocale() === 'tr' ? 'Taze' : __('site.profile_page.sort_newest'),
            'popular' => __('site.profile_page.sort_popular'),
        ];
        $activeSort = array_key_exists((string) ($sort ?? 'new'), $sortOptions) ? (string) ($sort ?? 'new') : 'new';
        $activeSortLabel = $sortOptions[$activeSort];
    @endphp

    <div class="og-profile-page">
        <div class="og-profile-wrap">
            <section class="og-card" aria-label="{{ $profileHeadingTitle }}">
                <div class="og-cover">
                    @if($coverUrl)
                        <img
                            src="{{ $coverUrl }}"
                            alt="{{ $profileHeadingTitle }} {{ __('site.profile_page.cover_alt') }}"
                            loading="lazy"
                            onerror="this.remove();"
                        >
                    @endif

                    @if($isOwnProfile)
                        <a href="{{ route('profile.edit') }}#section-profile" class="og-cover-edit-btn" aria-label="{{ app()->getLocale() === 'tr' ? 'Kapak görselini değiştir' : 'Change cover photo' }}">
                            <iconify-icon icon="lucide:image-up"></iconify-icon>
                            {{ app()->getLocale() === 'tr' ? 'Kapağı değiştir' : 'Change cover' }}
                        </a>
                    @endif
                </div>

                <div class="og-body">
                    <div class="og-topline">
                        @if($isOwnProfile)
                            <div class="og-avatar-wrap">
                                <button type="button" class="og-avatar-button" data-profile-avatar-open aria-label="{{ $profileHeadingTitle }}">
                                    <span class="og-avatar">
                                        @if($profileUrl)
                                            <img src="{{ $profileUrl }}" alt="{{ $profileHeadingTitle }}" loading="lazy">
                                        @else
                                            <span>{{ $profileInitial }}</span>
                                        @endif
                                    </span>
                                </button>

                                <a href="{{ route('profile.edit') }}#section-profile" class="og-avatar-edit-btn" aria-label="{{ app()->getLocale() === 'tr' ? 'Profil fotoğrafını değiştir' : 'Change profile photo' }}">
                                    <iconify-icon icon="lucide:camera"></iconify-icon>
                                </a>
                            </div>
                        @else
                            <button type="button" class="og-avatar-button" data-profile-avatar-open aria-label="{{ $profileHeadingTitle }}">
                                <span class="og-avatar">
                                    @if($profileUrl)
                                        <img src="{{ $profileUrl }}" alt="{{ $profileHeadingTitle }}" loading="lazy">
                                    @else
                                        <span>{{ $profileInitial }}</span>
                                    @endif
                                </span>
                            </button>
                        @endif

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
                                <a href="{{ route('profile.edit') }}" class="og-btn og-btn--primary">{{ app()->getLocale() === 'tr' ? 'Profili düzenle' : 'Edit profile' }}</a>
                            @endif

                            @if(!$isOwnProfile && ($messagesEnabled ?? false) && ($canStartChat ?? false))
                                <a href="{{ route('messages.show', $user) }}" class="og-icon-btn" aria-label="{{ __('site.profile_page.message_aria') }}">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M7 9h10M7 13h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M20 12a8 8 0 0 1-11.4 7.2L4 20l.8-4.4A8 8 0 1 1 20 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                                    <span>{{ __('site.profile_page.message_aria') }}</span>
                                </a>
                            @endif

                            <details class="og-menu" data-auto-close-details>
                                <summary aria-label="{{ $moreActionsLabel }}" title="{{ $moreActionsLabel }}" class="og-menu-summary-icon-only" aria-haspopup="menu" aria-expanded="false" aria-controls="profile-actions-menu">
                                    <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>
                                </summary>
                                <div id="profile-actions-menu" class="og-menu-panel shadcn-menu" role="menu">
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

                        @if($usernameLabel || $joinedDetailLabel || $locationLabel !== '' || $occupationLabel !== '' || $companyLabel !== '')
                            <div class="og-profile-subline">
                                @if($usernameLabel)
                                    <span class="og-profile-subline__item og-username">{{ $usernameLabel }}</span>
                                @endif
                                @if($joinedDetailLabel)
                                    <span class="og-profile-subline__item">{{ $joinedDetailLabel }}</span>
                                @endif
                                @if($locationLabel !== '')
                                    <span class="og-profile-subline__item">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s6-5.7 6-11A6 6 0 0 0 6 10c0 5.3 6 11 6 11Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="12" cy="10" r="2.3" stroke="currentColor" stroke-width="1.7"/></svg>
                                        {{ $locationLabel }}
                                    </span>
                                @endif
                                @if($occupationLabel !== '')
                                    <span class="og-profile-subline__item">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M4 8h16v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M4 12h16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                        {{ $occupationLabel }}
                                    </span>
                                @elseif($companyLabel !== '')
                                    <span class="og-profile-subline__item">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M4 8h16v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M4 12h16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                        {{ $companyLabel }}
                                    </span>
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
                                    <a href="{{ $pill['url'] }}" target="_blank" rel="noopener noreferrer" data-external-bridge="off" class="og-social {{ $pill['class'] }}" aria-label="{{ $pill['label'] }}" title="{{ $pill['label'] }}">
                                        @switch($pill['platform'])
                                            @case('facebook')
                                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill="#1877F2" d="M14 8.35h2.2V5.08c-.38-.05-1.68-.16-3.2-.16-3.16 0-5.33 1.88-5.33 5.36v3H4.2v3.66h3.47V24h4.25v-7.06h3.33l.53-3.66h-3.86v-2.64c0-1.06.3-2.29 2.08-2.29Z"/></svg>
                                                @break
                                            @case('instagram')
                                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="#E4405F" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="#E4405F" stroke-width="2"/><circle cx="17.4" cy="6.7" r="1.15" fill="#E4405F"/></svg>
                                                @break
                                            @case('x')
                                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill="#000000" d="M18.9 2.75h3.25l-7.1 8.12 8.35 10.38h-6.54l-5.12-6.35-5.86 6.35H2.62l7.6-8.7L2.2 2.75h6.7l4.63 5.77 5.37-5.77Zm-1.14 16.67h1.8L7.91 4.48H5.98l11.78 14.94Z"/></svg>
                                                @break
                                            @case('tiktok')
                                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill="#111111" d="M16.7 2.2c.28 2.35 1.6 3.76 3.9 3.9v3.36a7.46 7.46 0 0 1-3.82-1.12v6.32c0 4.02-2.36 6.84-6.22 6.84-3.36 0-6.06-2.22-6.06-5.66 0-3.92 3.12-6.04 6.86-5.66v3.42c-1.66-.26-3.18.42-3.18 2.08 0 1.42 1.12 2.22 2.34 2.22 1.42 0 2.54-.84 2.54-3.02V2.2h3.64Z"/></svg>
                                                @break
                                            @case('youtube')
                                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill="#FF0000" d="M22.2 7.2a3 3 0 0 0-2.1-2.13C18.24 4.56 12 4.56 12 4.56s-6.24 0-8.1.5A3 3 0 0 0 1.8 7.2 31.3 31.3 0 0 0 1.3 12c0 1.62.17 3.24.5 4.8a3 3 0 0 0 2.1 2.13c1.86.5 8.1.5 8.1.5s6.24 0 8.1-.5a3 3 0 0 0 2.1-2.13c.33-1.56.5-3.18.5-4.8 0-1.62-.17-3.24-.5-4.8ZM9.85 15.54V8.46L16.08 12l-6.23 3.54Z"/></svg>
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
                                        $badgeFallbackLetter = collect(preg_split('/\s+/', trim((string) ($badge->name ?? 'Rozet')), -1, PREG_SPLIT_NO_EMPTY))
                                            ->take(2)
                                            ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                                            ->implode('');
                                        $badgeFallbackLetter = $badgeFallbackLetter !== '' ? $badgeFallbackLetter : 'R';
                                    @endphp
                                    <button
                                        type="button"
                                        class="og-badge"
                                        style="--badge-color: {{ $badge->color ?? '#67e8f9' }}"
                                        title="{{ $badge->name ?? 'Rozet' }}"
                                        data-profile-badge-open
                                        aria-haspopup="dialog"
                                        aria-controls="profile-badge-sheet"
                                        aria-expanded="false"
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
                        <summary aria-haspopup="menu" aria-expanded="false" aria-controls="profile-sort-menu">
                            <span>{{ $activeSortLabel }}</span>
                            <svg viewBox="0 0 20 20" width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="m5 8 5 5 5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </summary>
                        <div id="profile-sort-menu" class="og-sort-panel" role="menu">
                            @foreach($sortOptions as $sortKey => $sortLabel)
                                <a href="{{ route('users.show', ['user' => $user, 'tab' => 'stories', 'sort' => $sortKey]) }}" class="og-sort-option" aria-current="{{ $activeSort === $sortKey ? 'true' : 'false' }}">
                                    @if($sortKey === 'popular')
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 2c1 3-2 4-2 7a3 3 0 0 0 6 0c1.5 1 2 3 2 4.5a6 6 0 1 1-12 0C6 9 9 8 12 2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M13 3a9 9 0 1 0 8.5 12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M21 3v6h-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @endif
                                    <span>{{ $sortLabel }}</span>
                                </a>
                            @endforeach
                        </div>
                    </details>
                @endif
            </nav>

            <div class="og-content">
                @if(($hasBlockedUser ?? false) || ($isBlockedByUser ?? false))
                    <div class="og-empty">{{ __('site.profile_page.restricted') }}</div>
                @elseif($activeTab === 'stories')
                    @include('partials.ads.slot', [
                        'slotKey' => 'ads_feed_top',
                    ])

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

                        @unless($loop->last)
                            @include('partials.ads.feed-breaks', [
                                'iteration' => $loop->iteration,
                                'isLast' => $loop->last,
                            ])
                        @endunless
                    @empty
                        <div class="og-empty">{{ __('site.profile_page.empty_posts') }}</div>
                    @endforelse

                    @if (is_object($posts) && method_exists($posts, 'links'))
                        <div class="pt-2">
                            {{-- {{ $posts->appends(['tab' => 'stories', 'sort' => $sort])->links() }} --}}
                        </div>
                    @endif
                @elseif($activeTab === 'comments')
                    <div class="og-direct-list">
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
                            <div class="og-direct-empty">{{ __('site.profile_page.empty_comments') }}</div>
                        @endforelse
                    </div>
                @elseif($activeTab === 'followers')
                    <div class="og-direct-list">
                        @forelse($followers as $person)
                            <a href="{{ route('users.show', $person) }}" class="og-list-link">
                                <span class="og-list-avatar"><img src="{{ $person->profile_photo_url ?? 'https://placehold.co/80x80' }}" alt="{{ $person->name }}" loading="lazy"></span>
                                <span class="og-list-main">
                                    <span class="og-list-name">{{ $person->name }} <x-verification-badge :user="$person" class="inline-flex h-4 w-4 shrink-0 items-center justify-center" size="sm" /></span>
                                    <span class="og-list-muted">{{ '@' . ($person->username ?? __('site.profile_page.default_username')) }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="og-direct-empty">{{ __('site.profile_page.empty_followers') }}</div>
                        @endforelse
                    </div>
                @elseif($activeTab === 'followings')
                    <div class="og-direct-list">
                        @if(! $canViewFollowings)
                            <div class="og-direct-empty">Bu kullanıcının takip ettiği hesaplar gizli.</div>
                        @else
                        @forelse($followings as $person)
                            <a href="{{ route('users.show', $person) }}" class="og-list-link">
                                <span class="og-list-avatar"><img src="{{ $person->profile_photo_url ?? 'https://placehold.co/80x80' }}" alt="{{ $person->name }}" loading="lazy"></span>
                                <span class="og-list-main">
                                    <span class="og-list-name">{{ $person->name }} <x-verification-badge :user="$person" class="inline-flex h-4 w-4 shrink-0 items-center justify-center" size="sm" /></span>
                                    <span class="og-list-muted">{{ '@' . ($person->username ?? __('site.profile_page.default_username')) }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="og-direct-empty">{{ __('site.profile_page.empty_followings') }}</div>
                        @endforelse
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div id="profile-avatar-sheet" class="og-sheet" role="dialog" aria-modal="true" aria-labelledby="profile-avatar-title" aria-hidden="true" inert>
        <div class="og-sheet-backdrop" data-profile-avatar-close></div>
        <div class="og-sheet-panel" data-profile-avatar-panel>
            <div class="og-sheet-handle"></div>
            <h3 id="profile-avatar-title" class="og-sheet-title" tabindex="-1">{{ $profileHeadingTitle }}</h3>
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

    <div id="profile-badge-sheet" class="og-sheet" role="dialog" aria-modal="true" aria-labelledby="profile-badge-title" aria-hidden="true" inert>
        <div class="og-sheet-backdrop" data-profile-badge-close></div>
        <div class="og-sheet-panel og-badge-sheet-panel" data-profile-badge-panel>
            <div class="og-sheet-handle og-badge-sheet-handle" aria-hidden="true"></div>
            <button type="button" class="og-badge-sheet-close-icon" data-profile-badge-close aria-label="{{ __('post_create.close') }}">
                <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
            <div class="og-badge-sheet-summary">
                <div class="og-badge-sheet-media">
                    <div class="og-ref-badge-stage" style="--badge-preview-color: #67e8f9" data-profile-badge-preview>
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
                <div class="og-badge-sheet-copy">
                    <h3 id="profile-badge-title" class="og-sheet-title" data-profile-badge-title tabindex="-1">Rozet</h3>
                    <p class="og-badge-sheet-subtitle" data-profile-badge-description></p>
                </div>
            </div>
            <div class="og-chips og-badge-sheet-meta">
                <span class="og-chip" data-profile-badge-awarded-at></span>
            </div>
            <button type="button" class="og-sheet-close og-badge-sheet-close-text" data-profile-badge-close>Anladım</button>
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

        const menuItemsFor = (item) => Array.from(item.querySelectorAll('.og-menu-item, .og-sort-option'));

        detailsItems.forEach((item) => {
            const summary = item.querySelector(':scope > summary');
            const panel = item.querySelector('.og-menu-panel, .og-sort-panel');
            const menuItems = menuItemsFor(item);

            summary?.setAttribute('aria-expanded', item.open ? 'true' : 'false');
            panel?.setAttribute('role', 'menu');
            menuItems.forEach((menuItem) => {
                menuItem.setAttribute('role', 'menuitem');
                menuItem.setAttribute('tabindex', '-1');
            });

            const openAt = (index) => {
                item.setAttribute('open', '');
                window.requestAnimationFrame(() => {
                    const items = menuItemsFor(item);
                    items[index < 0 ? items.length - 1 : index]?.focus();
                });
            };

            item.addEventListener('toggle', () => {
                summary?.setAttribute('aria-expanded', item.open ? 'true' : 'false');
                if (item.open) closeDetails(item);
            });

            summary?.addEventListener('keydown', (event) => {
                if (!['Enter', ' ', 'ArrowDown', 'ArrowUp'].includes(event.key)) return;
                event.preventDefault();
                openAt(event.key === 'ArrowUp' ? -1 : 0);
            });

            panel?.addEventListener('keydown', (event) => {
                const items = menuItemsFor(item);
                const currentIndex = items.indexOf(document.activeElement);
                let nextIndex = currentIndex;

                if (event.key === 'ArrowDown') nextIndex = (currentIndex + 1 + items.length) % items.length;
                else if (event.key === 'ArrowUp') nextIndex = (currentIndex - 1 + items.length) % items.length;
                else if (event.key === 'Home') nextIndex = 0;
                else if (event.key === 'End') nextIndex = items.length - 1;
                else if (event.key === 'Escape') {
                    event.preventDefault();
                    item.removeAttribute('open');
                    summary?.focus();
                    return;
                } else return;

                event.preventDefault();
                items[nextIndex]?.focus();
            });

            panel?.addEventListener('click', (event) => {
                if (event.target.closest('.og-menu-item, .og-sort-option')) {
                    item.removeAttribute('open');
                }
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
        const badgeAwardedAt = badgeSheet?.querySelector('[data-profile-badge-awarded-at]');
        const badgePreview = badgeSheet?.querySelector('[data-profile-badge-preview]');
        const badgePreviewFace = badgeSheet?.querySelector('[data-profile-badge-preview-face]');
        const badgeBackName = badgeSheet?.querySelector('[data-profile-badge-back-name]');
        const badgeBackShape = badgeSheet?.querySelector('[data-profile-badge-back-shape]');
        const badgeFrontLayer = badgeSheet?.querySelector('[data-profile-badge-front]');
        const badgeBackLayer = badgeSheet?.querySelector('[data-profile-badge-back]');

        // Rozet popup'ı yalnızca rozete tıklanınca açılsın.
        // Sayfa ilk açıldığında ve mobil tarayıcı geri/ileri önbelleğinden
        // döndüğünde kesin kapalı duruma getir.
        const forceBadgeSheetClosed = () => {
            if (!badgeSheet) return;
            badgeSheet.setAttribute('aria-hidden', 'true');
            badgeSheet.setAttribute('inert', '');
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
            badgeOpenButtons.forEach((button) => button.setAttribute('aria-expanded', 'false'));
        };

        forceBadgeSheetClosed();
        window.addEventListener('pageshow', forceBadgeSheetClosed);

        const syncScrollLock = () => {
            const hasOpenSheet = [avatarSheet, badgeSheet].some((sheet) => sheet?.getAttribute('aria-hidden') === 'false');
            document.documentElement.classList.toggle('overflow-hidden', hasOpenSheet);
            document.body.classList.toggle('overflow-hidden', hasOpenSheet);
        };

        const sheetFocusables = (sheet) => Array.from(sheet?.querySelectorAll(
            'button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        ) || []).filter((element) => !element.hasAttribute('inert'));

        const showSheet = (sheet, trigger = document.activeElement) => {
            if (!sheet) return;
            sheet.__returnFocus = trigger instanceof HTMLElement ? trigger : null;
            if (sheet.__returnFocus?.hasAttribute?.('aria-expanded')) {
                sheet.__returnFocus.setAttribute('aria-expanded', 'true');
            }
            sheet.setAttribute('aria-hidden', 'false');
            sheet.removeAttribute('inert');
            syncScrollLock();
            window.requestAnimationFrame(() => {
                const preferred = sheet.querySelector('button[data-profile-badge-close], button[data-profile-avatar-close], [tabindex="-1"]');
                preferred?.focus();
            });
        };

        const hideSheet = (sheet, restoreFocus = true) => {
            if (!sheet || sheet.getAttribute('aria-hidden') === 'true') return;
            sheet.setAttribute('aria-hidden', 'true');
            sheet.setAttribute('inert', '');
            if (sheet.__returnFocus?.hasAttribute?.('aria-expanded')) {
                sheet.__returnFocus.setAttribute('aria-expanded', 'false');
            }
            syncScrollLock();
            if (restoreFocus) {
                window.requestAnimationFrame(() => sheet.__returnFocus?.focus?.());
            }
        };

        avatarOpenButton?.addEventListener('click', () => showSheet(avatarSheet, avatarOpenButton));
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
                showSheet(badgeSheet, button);
            });
        });
        badgeCloseButtons?.forEach((button) => button.addEventListener('click', () => hideSheet(badgeSheet)));

        document.addEventListener('keydown', (event) => {
            const openSheet = [avatarSheet, badgeSheet].find((sheet) => sheet?.getAttribute('aria-hidden') === 'false');
            if (!openSheet) return;

            if (event.key === 'Escape') {
                event.preventDefault();
                hideSheet(openSheet);
                return;
            }

            if (event.key !== 'Tab') return;
            const focusables = sheetFocusables(openSheet);
            if (!focusables.length) {
                event.preventDefault();
                return;
            }

            const first = focusables[0];
            const last = focusables[focusables.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    })();
</script>
@endpush
