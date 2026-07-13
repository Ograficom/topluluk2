@php
    use Illuminate\Support\Str;

    $categoryToShow = $category ?? null;
    if (!$categoryToShow && isset($activeCategory) && isset($categories) && $activeCategory) {
        $categoryToShow = collect($categories)->firstWhere('slug', $activeCategory);
    }

    $followersCount = isset($followersCount) ? (int) $followersCount : 0;
    $isCategoryJoined = (bool) ($isCategoryJoined ?? false);
    $canManage = auth()->check() && (
        empty($categoryToShow?->created_by_user_id)
        || (int) $categoryToShow?->created_by_user_id === (int) auth()->id()
    );
    $creator = $categoryToShow?->creator ?? null;
    $categoryPageUrl = $categoryToShow ? route('blog.category', $categoryToShow) : url()->current();

    $name = trim((string) ($categoryToShow?->name ?? ''));
    $slug = trim((string) ($categoryToShow?->slug ?? ''));
    $description = trim((string) ($categoryToShow?->description ?? ''));
    $cover = $categoryToShow?->cover_image_url ?? $categoryToShow?->cover_image ?? null;
    $profile = $categoryToShow?->profile_image_url ?? $categoryToShow?->profile_image ?? null;
    $postsCount = (int) ($categoryPostsCount ?? ($categoryToShow?->posts_count ?? 0));
    $initials = Str::upper(Str::substr($name ?: 'K', 0, 2));

    $defaultCoverUrl = 'https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?auto=format&fit=crop&w=1400&q=80';
    $hasCustomCover = filled($cover);
    $coverUrl = $cover ?: $defaultCoverUrl;
    $defaultSeoImage = 'https://ografi.com/storage/app/public/categories/cover/kategoriler.png';
    $seoBaseUrl = 'https://ografi.com';
    $seoSiteName = 'Ografi';
    $seoCategoryName = $name !== '' ? $name : 'Kategoriler';
    $seoTitle = $seoCategoryName . ' - Ografi | Güncel Haberler, Topluluklar ve İçerikler';
    $seoDescriptionSource = $description !== ''
        ? $description
        : ($seoCategoryName . ' kategorisinde güncel haberleri, popüler gönderileri, topluluk paylaşımlarını, etiketleri ve öne çıkan içerikleri Ografi üzerinden keşfedin.');
    $seoDescription = Str::limit(trim(strip_tags($seoDescriptionSource)), 160, '');
    $seoKeywords = implode(', ', array_filter([
        'Ografi',
        $seoCategoryName,
        'kategori',
        'güncel haberler',
        'topluluklar',
        'sosyal içerik',
        'blog',
        'gündem',
    ]));
    $seoUrl = $categoryPageUrl;
    $seoImage = $hasCustomCover ? $coverUrl : $defaultSeoImage;
    $seoImageAlt = $seoCategoryName . ' kategorisi öne çıkan görseli';
    $seoLocale = app()->getLocale() === 'tr' ? 'tr_TR' : str_replace('-', '_', app()->getLocale());
    $seoLanguage = app()->getLocale() === 'tr' ? 'tr-TR' : app()->getLocale();
    $seoSearchUrl = $seoBaseUrl . '/search?q={search_term_string}';
    $seoMenuUrl = $seoBaseUrl . '/categories';

    $localBusiness = [
        '@type' => 'LocalBusiness',
        '@id' => $seoBaseUrl . '/#localbusiness',
        'name' => $seoSiteName,
        'url' => $seoBaseUrl,
        'description' => 'Ografi; güncel haberleri, kategorileri, toplulukları ve dijital içerikleri bir araya getiren modern bir içerik platformudur.',
        'image' => [$seoImage],
        'logo' => $defaultSeoImage,
        'priceRange' => '₺₺',
        'menu' => $seoMenuUrl,
        'hasMenu' => $seoMenuUrl,
        'sameAs' => [$seoBaseUrl],
        'openingHoursSpecification' => [[
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday',
            ],
            'opens' => '00:00',
            'closes' => '23:59',
        ]],
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'Türkiye',
        ],
    ];

    $businessPhone = config('app.business_phone');
    $businessStreetAddress = config('app.business_street_address');
    $businessLocality = config('app.business_locality');
    $businessRegion = config('app.business_region');
    $businessPostalCode = config('app.business_postal_code');
    $businessLatitude = config('app.business_latitude');
    $businessLongitude = config('app.business_longitude');

    if (filled($businessPhone)) {
        $localBusiness['telephone'] = $businessPhone;
    }

    if (filled($businessStreetAddress) || filled($businessLocality) || filled($businessRegion) || filled($businessPostalCode)) {
        $localBusiness['address'] = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $businessStreetAddress,
            'addressLocality' => $businessLocality,
            'addressRegion' => $businessRegion,
            'postalCode' => $businessPostalCode,
            'addressCountry' => 'TR',
        ]);
    }

    if (filled($businessLatitude) && filled($businessLongitude)) {
        $localBusiness['geo'] = [
            '@type' => 'GeoCoordinates',
            'latitude' => (float) $businessLatitude,
            'longitude' => (float) $businessLongitude,
        ];
    }

    $categoryStructuredData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => $seoBaseUrl . '/#website',
                'url' => $seoBaseUrl,
                'name' => $seoSiteName,
                'description' => 'Ografi güncel haberleri, toplulukları, kategorileri ve dijital içerikleri keşfetmeni sağlayan modern bir sosyal içerik platformudur.',
                'inLanguage' => $seoLanguage,
                'publisher' => [
                    '@id' => $seoBaseUrl . '/#organization',
                ],
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => $seoSearchUrl,
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            [
                '@type' => 'Organization',
                '@id' => $seoBaseUrl . '/#organization',
                'name' => $seoSiteName,
                'url' => $seoBaseUrl,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $defaultSeoImage,
                    'caption' => $seoSiteName,
                ],
                'image' => [
                    '@type' => 'ImageObject',
                    'url' => $seoImage,
                    'caption' => $seoImageAlt,
                ],
                'description' => 'Ografi; güncel haberleri, kategorileri, toplulukları ve dijital içerikleri bir araya getiren modern bir içerik platformudur.',
                'sameAs' => [$seoBaseUrl],
            ],
            $localBusiness,
            [
                '@type' => 'BreadcrumbList',
                '@id' => $seoUrl . '#breadcrumb',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Ana sayfa',
                        'item' => $seoBaseUrl,
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Kategoriler',
                        'item' => $seoMenuUrl,
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $seoCategoryName,
                        'item' => $seoUrl,
                    ],
                ],
            ],
            [
                '@type' => 'CollectionPage',
                '@id' => $seoUrl . '#webpage',
                'url' => $seoUrl,
                'name' => $seoTitle,
                'description' => $seoDescription,
                'inLanguage' => $seoLanguage,
                'isPartOf' => [
                    '@id' => $seoBaseUrl . '/#website',
                ],
                'about' => [
                    '@id' => $seoBaseUrl . '/#organization',
                ],
                'primaryImageOfPage' => [
                    '@type' => 'ImageObject',
                    'url' => $seoImage,
                    'caption' => $seoImageAlt,
                ],
                'breadcrumb' => [
                    '@id' => $seoUrl . '#breadcrumb',
                ],
                'mainEntity' => [
                    '@type' => 'ItemList',
                    'name' => $seoCategoryName . ' gönderileri',
                    'numberOfItems' => $postsCount,
                ],
            ],
        ],
    ];

    $usernameLabel = $slug !== '' ? '@' . $slug : null;
    $shareLabel = app()->getLocale() === 'tr' ? 'Paylas' : 'Share';
    $joinLabel = $isCategoryJoined ? 'Takipdesin' : 'Takip et';
    $moreActionsLabel = app()->getLocale() === 'tr' ? 'Diger islemler' : 'More actions';
    $categoryTypeIcon = <<<'SVG'
<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M3 8.25A2.25 2.25 0 0 1 5.25 6h4.032a2.25 2.25 0 0 1 1.591.659l1.218 1.218a2.25 2.25 0 0 0 1.591.659H18.75A2.25 2.25 0 0 1 21 10.786v7.964A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
SVG;
    $menuIcon = <<<'SVG'
<svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>
SVG;
@endphp

@push('head')
    <title>{{ $seoTitle }}</title>
    <meta name="title" content="{{ $seoTitle }}">
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <meta name="author" content="{{ $seoSiteName }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <link rel="canonical" href="{{ $seoUrl }}">

    <meta property="og:locale" content="{{ $seoLocale }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $seoSiteName }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoUrl }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:image:secure_url" content="{{ $seoImage }}">
    <meta property="og:image:alt" content="{{ $seoImageAlt }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    <meta name="twitter:image:alt" content="{{ $seoImageAlt }}">

    <meta name="theme-color" content="#2563eb">
    <meta name="msapplication-TileColor" content="#2563eb">

    <script type="application/ld+json">
        @json($categoryStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    </script>

<style>
[data-auto-close-details] > summary {
        list-style: none;
    }

[data-auto-close-details] > summary::-webkit-details-marker {
        display: none;
    }

.profile-reference-page {
        min-height: 100%;
        background: #efefef;
        padding: 14px 0 28px;
    }

.profile-reference-shell {
        width: 100%;
        max-width: var(--profile-shell-width);
        margin: 0 auto;
        padding: 0 14px;
    }

.profile-reference-card {
        overflow: hidden;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: none;
    }

.profile-reference-cover {
        position: relative;
        aspect-ratio: 3 / 1;
        overflow: hidden;
        background: #d9d9d9;
    }

.profile-reference-cover img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

.category-reference-page {
        background: #f7f9fa !important;
    }

.category-reference-page .profile-reference-card {
        border-radius: 16px 16px 0 0 !important;
    }

.category-reference-page .profile-reference-tabs-bar {
        margin-top: 0 !important;
        padding: 0 16px !important;
        background: #ffffff !important;
        border-radius: 0 0 16px 16px !important;
    }

.category-reference-page .profile-reference-content {
        margin-top: 16px !important;
    }

.profile-reference-body {
        position: relative;
        padding: 0 16px 12px;
    }

.profile-reference-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-top: -46px;
    }

.profile-reference-avatar-wrap {
        display: flex;
        justify-content: flex-start;
        flex: 0 0 auto;
    }

.profile-reference-avatar-button {
        display: inline-flex;
        padding: 0;
        border: 0;
        background: transparent;
        border-radius: 999px;
    }

.profile-reference-avatar {
        width: 92px;
        height: 92px;
        overflow: hidden;
        border: 3px solid #ffffff;
        border-radius: 999px;
        background: #e5e7eb;
    }

.profile-reference-avatar img,
.profile-reference-avatar span {
        display: flex;
        width: 100%;
        height: 100%;
        align-items: center;
        justify-content: center;
        object-fit: cover;
    }

.profile-reference-heading {
        position: relative;
        margin-top: 0;
        text-align: left;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

.profile-reference-name-section {
        flex: 1;
        min-width: 0;
    }

.profile-reference-name-row {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 12px;
        min-width: 0;
        flex-wrap: wrap;
    }

.profile-reference-name-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

.profile-reference-name {
        margin: 0;
        color: #111827;
        font-size: 16px;
        font-weight: 600;
        line-height: 1.28;
        letter-spacing: -0.02em;
    }

.profile-reference-username {
        display: block;
        margin-top: 4px;
        color: #6b7280;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.35;
    }

.profile-reference-meta-row {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 6px;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.35;
    }

.profile-reference-bio {
        margin: 10px 0 0;
        color: #111827;
        font-size: 13px;
        line-height: 1.5;
        text-align: left;
    }

.profile-reference-counts {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-top: 12px;
        color: #111827;
        font-size: 13px;
        line-height: 1.35;
    }

.profile-reference-counts strong {
        font-weight: 700;
    }

.profile-reference-tabs {
        display: flex;
        align-items: flex-end;
        gap: 20px;
        min-width: 0;
        padding-top: 0;
    }

.profile-reference-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 0 8px;
        border: 0;
        border-bottom: 2px solid transparent;
        background: transparent;
        color: #6b7280;
        font-size: 13px;
        font-weight: 500;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
    }

.profile-reference-tab[aria-current="page"] {
        border-bottom-color: #2563eb;
        color: #111827;
    }

.profile-reference-tabs-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-top: 16px;
        background: transparent;
    }

.profile-reference-sort {
        position: relative;
        display: inline-flex;
        align-items: center;
        flex: 0 0 auto;
        background: transparent;
    }

.profile-reference-sort summary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        list-style: none;
        border: 0;
        border-radius: 12px;
        background: transparent;
        padding: 9px 12px;
        color: #111827;
        font-size: 14px;
        font-weight: 500;
        line-height: 1;
    }

.profile-reference-sort-icon {
        width: 16px;
        height: 16px;
        display: block;
        flex: 0 0 auto;
    }

.profile-reference-sort summary::-webkit-details-marker {
        display: none;
    }

.profile-reference-sort-panel {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        z-index: 30;
        min-width: 11rem;
        border-radius: 14px;
        background: #ffffff;
        padding: 8px;
    }

.profile-reference-sort-option {
        display: block;
        border-radius: 10px;
        padding: 10px 12px;
        color: #334155;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        background: transparent;
        border: 0;
    }

.profile-reference-sort-option[aria-current="true"] {
        color: #111827;
        font-weight: 600;
    }

.profile-reference-sort-option:hover {
        background: #f1f5f9;
        color: #111827;
    }

.profile-reference-info-card {
        border-radius: 24px;
        background: #ffffff;
        padding: 26px;
    }

.profile-reference-info-title {
        margin: 0;
        color: #111827;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.3;
    }

.profile-reference-info-description {
        margin: 12px 0 0;
        color: #111827;
        font-size: 14px;
        line-height: 1.6;
    }

.profile-reference-info-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-top: 18px;
    }

.profile-reference-info-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #111827;
        font-size: 13px;
        line-height: 1.5;
    }

.profile-reference-info-icon {
        width: 16px;
        height: 16px;
        display: block;
        flex: 0 0 auto;
        color: #6b7280;
    }

.profile-reference-info-link {
        color: #2563eb;
        text-decoration: none;
    }

.profile-reference-content {
        margin-top: 14px;
    }

.profile-reference-empty {
        padding: 18px;
        border-radius: 24px;
        background: #ffffff;
        color: #6b7280;
        text-align: center;
    }

.profile-post-card-wrapper {
        width: 100%;
        padding: 0;
    }

.profile-reference-actions-wrapper {
        flex-shrink: 0;
    }

.profile-reference-actions-block {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

.profile-reference-actions-inline {
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

.profile-reference-actions-inline form {
        display: inline-flex;
        margin: 0;
    }

.profile-reference-btn-primary {
        display: inline-flex;
        height: 36px;
        align-items: center;
        justify-content: center;
        padding: 0 14px;
        border: 0;
        border-radius: 10px;
        background: #2563eb;
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        line-height: 1;
        text-decoration: none;
    }

.profile-reference-icon-btn,
.profile-reference-menu-summary,
.profile-reference-account-badge {
        display: inline-flex;
        width: 36px;
        height: 36px;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 10px;
        background: #f2f2f2;
        color: #111827;
        padding: 0;
        text-decoration: none;
        flex-shrink: 0;
    }

.profile-reference-account-badge--category {
        background: #dbeafe;
        color: #0369a1;
    }

.profile-reference-icon-btn svg,
.profile-reference-menu-summary svg,
.profile-reference-account-badge svg {
        width: 18px;
        height: 18px;
        display: block;
    }

@media (max-width: 640px) {
.profile-reference-page {
            padding: 0 0 24px !important;
        }

.profile-reference-shell {
            max-width: none !important;
            padding: 0 !important;
        }

.profile-reference-card {
            border-radius: 0 !important;
            margin: 0 !important;
        }

.profile-reference-cover {
            aspect-ratio: 3.5 / 1;
        }

.profile-reference-body {
            padding: 0 12px 10px;
        }

.profile-reference-top {
            align-items: flex-start;
            flex-wrap: nowrap;
            margin-top: -40px;
        }

.profile-reference-avatar {
            width: 84px;
            height: 84px;
        }

.profile-reference-heading {
            margin-top: 6px;
        }

.profile-reference-name-row {
            align-items: center;
        }

.profile-reference-name-wrapper {
            flex: 1 1 auto;
            min-width: 0;
            gap: 5px;
        }

.profile-reference-name {
            text-align: left;
            font-size: 15px;
            line-height: 1.3;
        }

.profile-reference-btn-primary {
            height: 34px;
            padding: 0 12px;
            font-size: 12px;
        }

.profile-reference-icon-btn,
.profile-reference-account-badge,
.profile-reference-menu-summary {
            width: 34px;
            height: 34px;
            border-radius: 10px;
        }

.profile-reference-tabs-bar {
            gap: 10px;
            padding-left: 12px;
            padding-right: 12px;
        }

.profile-reference-tabs {
            gap: 16px;
        }

.profile-reference-sort summary {
            padding: 8px 10px;
            font-size: 13px;
        }

.profile-reference-sort-panel {
            min-width: 9.5rem;
        }

.profile-reference-info-card {
            margin-left: 12px;
            margin-right: 12px;
            border-radius: 16px;
            padding: 16px;
        }

.profile-reference-info-title {
            font-size: 16px;
        }

.profile-reference-info-description,
.profile-reference-info-item {
            font-size: 12px;
        }

.profile-reference-info-list {
            gap: 12px;
            margin-top: 16px;
        }

.profile-reference-content [data-post-card-shell] {
            width: calc(100% - 24px) !important;
            max-width: none !important;
            margin-left: 12px !important;
            margin-right: 12px !important;
            border-radius: 14px !important;
        }

.profile-post-card-wrapper {
            padding: 0 12px;
        }

}

body:has(.category-reference-card) .layout-main {
        max-width: var(--profile-shell-width) !important;
        gap: 16px !important;
    }

body:has(.category-reference-card) .profile-reference-page {
        background: #f4f4f4 !important;
        padding: 14px 0 32px !important;
    }

body:has(.category-reference-card) .profile-reference-shell {
        max-width: var(--profile-shell-width) !important;
        padding: 0 !important;
    }

body:has(.category-reference-card) .category-reference-card,
body:has(.category-reference-card) [data-post-card-shell] {
        border-radius: 8px !important;
        background: #ffffff !important;
        box-shadow: none !important;
    }

body:has(.category-reference-card) .profile-reference-cover {
        height: 206px !important;
        aspect-ratio: auto !important;
        border-radius: 8px 8px 0 0 !important;
        background-color: #ffffff !important;
    }

body:has(.category-reference-card) .category-reference-cover--default {
        background-image:
            radial-gradient(circle at center, #111111 1.35px, transparent 1.45px),
            radial-gradient(circle at center, rgba(17, 17, 17, 0.55) 1px, transparent 1.1px);
        background-position: 0 0, 6px 6px;
        background-size: 12px 12px;
    }

body:has(.category-reference-card) .category-reference-cover--default::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at center, rgba(255, 255, 255, 0.95) 0 18%, rgba(255, 255, 255, 0.52) 34%, transparent 64%);
        pointer-events: none;
    }

body:has(.category-reference-card) .profile-reference-body {
        padding: 0 24px 0 !important;
    }

body:has(.category-reference-card) .profile-reference-top {
        margin-top: -48px !important;
        min-height: 112px !important;
    }

body:has(.category-reference-card) .profile-reference-avatar {
        width: 108px !important;
        height: 108px !important;
        border: 0 !important;
        background: #ef3434 !important;
    }

body:has(.category-reference-card) .profile-reference-avatar span {
        color: #ffffff !important;
        font-size: 48px !important;
        font-weight: 500 !important;
        line-height: 1 !important;
    }

body:has(.category-reference-card) .profile-reference-heading {
        margin-top: 8px !important;
        display: flex !important;
        align-items: flex-start !important;
        gap: 18px !important;
    }

body:has(.category-reference-card) .profile-reference-actions-wrapper {
        margin: -72px 0 0 auto !important;
        align-self: flex-start !important;
    }

body:has(.category-reference-card) .profile-reference-name {
        color: #111111 !important;
        font-size: 21px !important;
        font-weight: 800 !important;
        line-height: 1.15 !important;
        text-transform: uppercase !important;
        letter-spacing: 0 !important;
    }

body:has(.category-reference-card) .profile-reference-username,
body:has(.category-reference-card) .profile-reference-account-badge,
body:has(.category-reference-card) .profile-reference-menu-summary {
        display: none !important;
    }

body:has(.category-reference-card) .profile-reference-btn-primary {
        min-width: 86px !important;
        height: 36px !important;
        border-radius: 8px !important;
        background: #2563eb !important;
        color: #ffffff !important;
        font-size: 14px !important;
        font-weight: 800 !important;
    }

body:has(.category-reference-card) .profile-reference-meta-row {
        margin-top: 10px !important;
        gap: 12px !important;
        color: #737373 !important;
        font-size: 14px !important;
    }

body:has(.category-reference-card) .profile-reference-meta-row strong {
        color: #111111 !important;
        font-weight: 800 !important;
    }

body:has(.category-reference-card) .profile-reference-bio {
        margin-top: 12px !important;
        color: #111111 !important;
        font-size: 14px !important;
        line-height: 1.35 !important;
    }

body:has(.category-reference-card) .profile-reference-tabs-bar {
        margin-top: 20px !important;
        padding: 0 !important;
        gap: 22px !important;
    }

body:has(.category-reference-card) .profile-reference-tab {
        color: #737373 !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        padding-bottom: 14px !important;
    }

body:has(.category-reference-card) .profile-reference-tab[aria-current="page"] {
        color: #2563eb !important;
        border-bottom-color: #2563eb !important;
    }

body:has(.category-reference-card) .profile-reference-sort {
        display: none !important;
    }

body:has(.category-reference-card) .profile-reference-content {
        margin-top: 20px !important;
    }

body:has(.category-reference-card) .profile-reference-empty {
        border-radius: 8px !important;
        background: #ffffff !important;
        padding: 24px !important;
    }

body.route-category .layout-main {
        max-width: var(--profile-shell-width) !important;
        gap: 16px !important;
    }

body.route-category .profile-reference-page {
        background: #f4f4f4 !important;
        padding: 14px 0 32px !important;
    }

body.route-category .profile-reference-shell {
        max-width: var(--profile-shell-width) !important;
        padding: 0 !important;
    }

body.route-category .category-reference-card,
body.route-category [data-post-card-shell] {
        border-radius: 8px !important;
        background: #ffffff !important;
        box-shadow: none !important;
    }

body.route-category .profile-reference-cover {
        height: 206px !important;
        aspect-ratio: auto !important;
        border-radius: 8px 8px 0 0 !important;
        background-color: #ffffff !important;
    }

body.route-category .category-reference-cover--default {
        background-image:
            radial-gradient(circle at center, #111111 1.35px, transparent 1.45px),
            radial-gradient(circle at center, rgba(17, 17, 17, 0.55) 1px, transparent 1.1px);
        background-position: 0 0, 6px 6px;
        background-size: 12px 12px;
    }

body.route-category .category-reference-cover--default::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at center, rgba(255, 255, 255, 0.95) 0 18%, rgba(255, 255, 255, 0.52) 34%, transparent 64%);
        pointer-events: none;
    }

body.route-category .profile-reference-body {
        padding: 0 24px 0 !important;
    }

body.route-category .profile-reference-top {
        margin-top: -48px !important;
        min-height: 112px !important;
    }

body.route-category .profile-reference-avatar {
        width: 108px !important;
        height: 108px !important;
        border: 0 !important;
        background: #ef3434 !important;
    }

body.route-category .profile-reference-avatar span {
        color: #ffffff !important;
        font-size: 48px !important;
        font-weight: 500 !important;
        line-height: 1 !important;
    }

body.route-category .profile-reference-heading {
        margin-top: 8px !important;
        display: flex !important;
        align-items: flex-start !important;
        gap: 18px !important;
    }

body.route-category .profile-reference-actions-wrapper {
        margin: -72px 0 0 auto !important;
        align-self: flex-start !important;
    }

body.route-category .profile-reference-name {
        color: #111111 !important;
        font-size: 21px !important;
        font-weight: 800 !important;
        line-height: 1.15 !important;
        text-transform: uppercase !important;
        letter-spacing: 0 !important;
    }

body.route-category .profile-reference-username,
body.route-category .profile-reference-account-badge,
body.route-category .profile-reference-menu-summary {
        display: none !important;
    }

body.route-category .profile-reference-btn-primary {
        min-width: 86px !important;
        height: 36px !important;
        border-radius: 8px !important;
        background: #2563eb !important;
        color: #ffffff !important;
        font-size: 14px !important;
        font-weight: 800 !important;
    }

body.route-category .profile-reference-meta-row {
        margin-top: 10px !important;
        gap: 12px !important;
        color: #737373 !important;
        font-size: 14px !important;
    }

body.route-category .profile-reference-meta-row strong {
        color: #111111 !important;
        font-weight: 800 !important;
    }

body.route-category .profile-reference-bio {
        margin-top: 12px !important;
        color: #111111 !important;
        font-size: 14px !important;
        line-height: 1.35 !important;
    }

body.route-category .profile-reference-tabs-bar {
        margin-top: 20px !important;
        padding: 0 !important;
        gap: 22px !important;
    }

body.route-category .profile-reference-tab {
        color: #737373 !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        padding-bottom: 14px !important;
    }

body.route-category .profile-reference-tab[aria-current="page"] {
        color: #2563eb !important;
        border-bottom-color: #2563eb !important;
    }

body.route-category .profile-reference-sort {
        display: none !important;
    }

body.route-category .profile-reference-content {
        margin-top: 20px !important;
    }

body.route-category .profile-reference-empty {
        border-radius: 8px !important;
        background: #ffffff !important;
        padding: 24px !important;
    }

body.route-category .layout-main {
        max-width: none !important;
        width: 100% !important;
    }

body.route-category .profile-reference-page {
        background-color: transparent !important;
        padding: 0 !important;
    }

body.route-category .profile-reference-shell {
        width: 100% !important;
        max-width: none !important;
        padding: 0 !important;
    }

body.route-category .category-reference-card {
        background-color: #ffffff !important;
        border-radius: 16px 16px 0 0 !important;
        overflow: hidden !important;
        margin-bottom: 0 !important;
        border: 1px solid #eff3f4 !important;
        border-bottom: 0 !important;
    }

body.route-category .profile-reference-cover {
        height: 150px !important;
        background-color: #ffffff !important;
        border-radius: 16px 16px 0 0 !important;
    }

body.route-category .category-reference-cover--default {
        background-image: radial-gradient(#d1d9dd 1px, transparent 1px) !important;
        background-size: 12px 12px !important;
        background-position: center !important;
    }

body.route-category .category-reference-cover--default::after {
        display: none !important;
    }

body.route-category .profile-reference-body {
        position: relative !important;
        padding: 0 24px 0 !important;
    }

body.route-category .profile-reference-top {
        margin-top: -45px !important;
        min-height: 102px !important;
    }

body.route-category .profile-reference-avatar {
        width: 90px !important;
        height: 90px !important;
        border: 4px solid #ffffff !important;
        background-color: #f4212e !important;
    }

body.route-category .profile-reference-avatar span {
        color: #ffffff !important;
        font-size: 36px !important;
        font-weight: 800 !important;
    }

body.route-category .profile-reference-actions-wrapper {
        position: absolute !important;
        top: 16px !important;
        right: 24px !important;
        margin: 0 !important;
    }

body.route-category .profile-reference-btn-primary {
        min-width: 0 !important;
        height: 34px !important;
        padding: 0 16px !important;
        border-radius: 8px !important;
        background-color: #2563eb !important;
        color: #ffffff !important;
        font-size: 14px !important;
        font-weight: 700 !important;
    }

body.route-category .profile-reference-heading {
        margin-top: 0 !important;
        display: block !important;
    }

body.route-category .profile-reference-name {
        margin: 0 0 6px !important;
        color: #0f1419 !important;
        font-size: 20px !important;
        font-weight: 800 !important;
        line-height: 1.2 !important;
        text-transform: uppercase !important;
    }

body.route-category .profile-reference-meta-row {
        display: flex !important;
        gap: 12px !important;
        margin: 0 0 12px !important;
        color: #536471 !important;
        font-size: 13px !important;
        line-height: 1.35 !important;
    }

body.route-category .profile-reference-meta-row strong {
        color: #0f1419 !important;
        font-weight: 700 !important;
    }

body.route-category .profile-reference-bio {
        margin: 0 0 24px !important;
        color: #0f1419 !important;
        font-size: 14px !important;
        line-height: 1.35 !important;
    }

body.route-category .profile-reference-tabs-bar {
        margin-top: 0 !important;
        padding: 0 24px !important;
        background-color: #ffffff !important;
        border: 1px solid #eff3f4 !important;
        border-top: 0 !important;
        border-radius: 0 0 16px 16px !important;
    }

body.route-category .profile-reference-tabs {
        gap: 32px !important;
    }

body.route-category .profile-reference-tab {
        padding: 12px 0 !important;
        color: #536471 !important;
        font-size: 14px !important;
        font-weight: 600 !important;
    }

body.route-category .profile-reference-tab[aria-current="page"] {
        color: #2563eb !important;
        border-bottom-color: #2563eb !important;
    }

body.route-category .profile-reference-content {
        margin-top: 16px !important;
    }

body.route-category .profile-post-card-wrapper {
        padding: 0 !important;
        margin-bottom: 16px !important;
    }

body.route-category .category-feed-post-card {
        background-color: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 16px;
        border: 1px solid #eff3f4;
        padding: 24px;
    }

body.route-category .category-feed-post-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

body.route-category .category-feed-author-avatar {
        position: relative;
        width: 40px;
        height: 40px;
        overflow: hidden;
        border-radius: 50%;
        background: #eff3f4;
        flex: 0 0 auto;
    }

body.route-category .category-feed-author-avatar img,
body.route-category .category-feed-author-avatar span {
        display: flex;
        width: 100%;
        height: 100%;
        align-items: center;
        justify-content: center;
        object-fit: cover;
        color: #0f1419;
        font-size: 13px;
        font-weight: 700;
    }

body.route-category .category-feed-author-badge {
        position: absolute;
        right: -2px;
        bottom: -2px;
        width: 18px;
        height: 18px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        background-color: #f4212e;
        color: #ffffff;
        font-size: 7px;
        font-weight: 700;
        line-height: 14px;
        text-align: center;
    }

body.route-category .category-feed-author-name {
        margin: 0 0 2px;
        color: #0f1419;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.25;
    }

body.route-category .category-feed-author-meta {
        margin: 0;
        color: #536471;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.25;
    }

body.route-category .category-feed-post-title {
        margin: 0 0 16px;
        color: #0f1419;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.4;
    }

body.route-category .category-feed-post-title a {
        color: inherit;
        text-decoration: none;
    }

body.route-category .category-feed-post-image {
        display: block;
        width: 100%;
        height: auto;
        border-radius: 12px;
    }

body.route-category .profile-reference-empty {
        background-color: #ffffff !important;
        border: 1px solid #eff3f4 !important;
        border-radius: 16px !important;
        padding: 24px !important;
        color: #536471 !important;
        font-size: 14px !important;
        text-align: center !important;
    }

@media (max-width: 960px) {
body.route-category .profile-reference-shell {
            padding: 0 12px !important;
        }

}

.profile-reference-page.category-reference-page {
        background: #f7f9fa !important;
    }

.profile-reference-page.category-reference-page .profile-reference-card {
        border-radius: 16px 16px 0 0 !important;
        margin-bottom: 0 !important;
    }

.profile-reference-page.category-reference-page .profile-reference-tabs-bar {
        margin-top: 0 !important;
        padding: 0 16px !important;
        background: #ffffff !important;
        border-radius: 0 0 16px 16px !important;
    }

.profile-reference-page.category-reference-page .profile-reference-content {
        margin-top: 16px !important;
    }

.profile-reference-page.category-reference-page .profile-reference-tab,
.profile-reference-page.category-reference-page .profile-reference-sort summary {
        background: transparent !important;
        box-shadow: none !important;
    }

.profile-reference-page.category-reference-page .profile-reference-tab:hover,
.profile-reference-page.category-reference-page .profile-reference-sort summary:hover {
        background: #f3f4f6 !important;
    }

.profile-reference-page.category-reference-page .profile-reference-tab[aria-current="page"] {
        background: transparent !important;
    }

/* Sekmeler: resimdeki gibi, sadece mavi aktif renk */

body.route-category .profile-reference-tabs,
body:has(.category-reference-card) .profile-reference-tabs,
.profile-reference-page.category-reference-page .profile-reference-tabs {
        display: flex !important;
        align-items: flex-end !important;
        gap: 12px !important;
    }

body.route-category .profile-reference-tabs-bar,
body:has(.category-reference-card) .profile-reference-tabs-bar,
.profile-reference-page.category-reference-page .profile-reference-tabs-bar {
        margin-top: 0 !important;
        padding: 0 16px !important;
        background: #ffffff !important;
        border-radius: 0 0 16px 16px !important;
        gap: 8px !important;
    }

body.route-category .profile-reference-tab,
body:has(.category-reference-card) .profile-reference-tab,
.profile-reference-page.category-reference-page .profile-reference-tab {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 12px 0 !important;
        border: 0 !important;
        border-bottom: 2px solid transparent !important;
        background: transparent !important;
        color: #6b7280 !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        line-height: 1.25 !important;
        text-decoration: none !important;
        box-shadow: none !important;
    }

body.route-category .profile-reference-tab:hover,
body:has(.category-reference-card) .profile-reference-tab:hover,
.profile-reference-page.category-reference-page .profile-reference-tab:hover {
        background: transparent !important;
        color: #2563eb !important;
    }

body.route-category .profile-reference-tab[aria-current="page"],
body:has(.category-reference-card) .profile-reference-tab[aria-current="page"],
.profile-reference-page.category-reference-page .profile-reference-tab[aria-current="page"] {
        background: transparent !important;
        color: #2563eb !important;
        border-bottom-color: #2563eb !important;
    }

@media (max-width: 640px) {
body.route-category .profile-reference-tabs,
body:has(.category-reference-card) .profile-reference-tabs,
.profile-reference-page.category-reference-page .profile-reference-tabs {
            gap: 10px !important;
        }

body.route-category .profile-reference-tab,
body:has(.category-reference-card) .profile-reference-tab,
.profile-reference-page.category-reference-page .profile-reference-tab {
            padding: 12px 0 !important;
            font-size: 14px !important;
        }

}

/* Son düzeltme: profil resmi büyütüldü, Katılmak butonu yukarı alındı, yeşiller mavi yapıldı */

body.route-category .profile-reference-body,
body:has(.category-reference-card) .profile-reference-body,
.profile-reference-page.category-reference-page .profile-reference-body {
        position: relative !important;
    }

body.route-category .profile-reference-top,
body:has(.category-reference-card) .profile-reference-top,
.profile-reference-page.category-reference-page .profile-reference-top {
        margin-top: -54px !important;
        min-height: 116px !important;
    }

body.route-category .profile-reference-avatar,
body:has(.category-reference-card) .profile-reference-avatar,
.profile-reference-page.category-reference-page .profile-reference-avatar {
        width: 104px !important;
        height: 104px !important;
        border: 4px solid #ffffff !important;
        background-color: #2563eb !important;
    }

body.route-category .profile-reference-avatar span,
body:has(.category-reference-card) .profile-reference-avatar span,
.profile-reference-page.category-reference-page .profile-reference-avatar span {
        font-size: 42px !important;
        color: #ffffff !important;
    }

body.route-category .profile-reference-actions-wrapper,
body:has(.category-reference-card) .profile-reference-actions-wrapper,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper {
        position: absolute !important;
        top: 26px !important;
        right: 24px !important;
        margin: 0 !important;
        z-index: 5 !important;
    }

body.route-category .profile-reference-btn-primary,
body:has(.category-reference-card) .profile-reference-btn-primary,
.profile-reference-page.category-reference-page .profile-reference-btn-primary {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
    }

body.route-category .profile-reference-tab[aria-current="page"],
body:has(.category-reference-card) .profile-reference-tab[aria-current="page"],
.profile-reference-page.category-reference-page .profile-reference-tab[aria-current="page"] {
        color: #2563eb !important;
        border-bottom-color: #2563eb !important;
    }

@media (max-width: 640px) {
body.route-category .profile-reference-top,
body:has(.category-reference-card) .profile-reference-top,
.profile-reference-page.category-reference-page .profile-reference-top {
            margin-top: -50px !important;
            min-height: 108px !important;
        }

body.route-category .profile-reference-avatar,
body:has(.category-reference-card) .profile-reference-avatar,
.profile-reference-page.category-reference-page .profile-reference-avatar {
            width: 96px !important;
            height: 96px !important;
        }

body.route-category .profile-reference-actions-wrapper,
body:has(.category-reference-card) .profile-reference-actions-wrapper,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper {
            top: 22px !important;
            right: 16px !important;
        }

}

/* Giris yapinca kapak/profil görseli değiştirme overlayleri */

.profile-reference-cover-change {
        position: absolute;
        left: 50%;
        top: 50%;
        z-index: 6;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 10px;
        background: rgba(17, 24, 39, 0.72);
        color: #ffffff;
        font-size: 13px;
        font-weight: 600;
        line-height: 1;
        text-decoration: none;
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.96);
        pointer-events: none;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

.profile-reference-cover-change svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
    }

.profile-reference-cover:hover .profile-reference-cover-change,
.profile-reference-cover:focus-within .profile-reference-cover-change {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
        pointer-events: auto;
    }

.profile-reference-avatar-shell {
        position: relative;
        display: inline-flex;
        border-radius: 999px;
    }

.profile-reference-avatar-change {
        position: absolute;
        inset: 0;
        z-index: 8;
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        border-radius: 999px;
        background: rgba(17, 24, 39, 0.46);
        color: #ffffff;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.1;
        text-align: center;
        text-decoration: none;
        opacity: 0;
        pointer-events: none;
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
    }

.profile-reference-avatar-change svg {
        width: 24px;
        height: 24px;
        flex: 0 0 auto;
    }

.profile-reference-avatar-change span {
        max-width: 76px;
        white-space: normal;
    }

.profile-reference-avatar-shell:hover .profile-reference-avatar-change,
.profile-reference-avatar-shell:focus-within .profile-reference-avatar-change {
        opacity: 1;
        pointer-events: auto;
    }

@media (hover: none) {
.profile-reference-cover-change,
.profile-reference-avatar-change {
            opacity: 1;
            pointer-events: auto;
        }

}

/* SON DÜZELTME: Katılmak butonu yukarı, profil resmi büyük */

body.route-category .profile-reference-heading,
body:has(.category-reference-card) .profile-reference-heading,
.profile-reference-page.category-reference-page .profile-reference-heading {
        position: static !important;
    }

body.route-category .profile-reference-body,
body:has(.category-reference-card) .profile-reference-body,
.profile-reference-page.category-reference-page .profile-reference-body {
        position: relative !important;
    }

body.route-category .profile-reference-actions-wrapper,
body:has(.category-reference-card) .profile-reference-actions-wrapper,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper {
        position: absolute !important;
        top: 76px !important;
        right: 24px !important;
        margin: 0 !important;
        z-index: 20 !important;
    }

body.route-category .profile-reference-avatar,
body:has(.category-reference-card) .profile-reference-avatar,
.profile-reference-page.category-reference-page .profile-reference-avatar {
        width: 104px !important;
        height: 104px !important;
        border: 4px solid #ffffff !important;
    }

body.route-category .profile-reference-top,
body:has(.category-reference-card) .profile-reference-top,
.profile-reference-page.category-reference-page .profile-reference-top {
        margin-top: -54px !important;
        min-height: 118px !important;
    }

body.route-category .profile-reference-btn-primary,
body:has(.category-reference-card) .profile-reference-btn-primary,
.profile-reference-page.category-reference-page .profile-reference-btn-primary {
        height: 36px !important;
        padding: 0 18px !important;
        border-radius: 9px !important;
        background: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
    }

@media (max-width: 640px) {
body.route-category .profile-reference-actions-wrapper,
body:has(.category-reference-card) .profile-reference-actions-wrapper,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper {
            top: 72px !important;
            right: 16px !important;
        }

body.route-category .profile-reference-avatar,
body:has(.category-reference-card) .profile-reference-avatar,
.profile-reference-page.category-reference-page .profile-reference-avatar {
            width: 96px !important;
            height: 96px !important;
        }

body.route-category .profile-reference-top,
body:has(.category-reference-card) .profile-reference-top,
.profile-reference-page.category-reference-page .profile-reference-top {
            margin-top: -50px !important;
            min-height: 110px !important;
        }

}

/* Kapak fotoğrafı: masaüstünde 960x260 */

body.route-category .profile-reference-shell,
body:has(.category-reference-card) .profile-reference-shell,
.profile-reference-page.category-reference-page .profile-reference-shell {
        width: 100% !important;
        max-width: 960px !important;
    }

body.route-category .category-reference-card,
body:has(.category-reference-card) .category-reference-card,
.profile-reference-page.category-reference-page .category-reference-card {
        width: 100% !important;
        max-width: 960px !important;
    }

body.route-category .profile-reference-cover,
body:has(.category-reference-card) .profile-reference-cover,
.profile-reference-page.category-reference-page .profile-reference-cover {
        width: 100% !important;
        max-width: 960px !important;
        height: 260px !important;
        min-height: 260px !important;
        aspect-ratio: auto !important;
        overflow: hidden !important;
        border-radius: 16px 16px 0 0 !important;
    }

body.route-category .profile-reference-cover img,
body:has(.category-reference-card) .profile-reference-cover img,
.profile-reference-page.category-reference-page .profile-reference-cover img {
        width: 100% !important;
        height: 260px !important;
        min-height: 260px !important;
        object-fit: cover !important;
        object-position: center !important;
    }

/* Katılmak butonu beyaz alanın içinde kalsın */

body.route-category .profile-reference-actions-wrapper,
body:has(.category-reference-card) .profile-reference-actions-wrapper,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper {
        position: absolute !important;
        top: 76px !important;
        right: 24px !important;
        margin: 0 !important;
        z-index: 20 !important;
    }

/* Mobilde taşmayı engelle */

@media (max-width: 640px) {
body.route-category .profile-reference-shell,
body:has(.category-reference-card) .profile-reference-shell,
.profile-reference-page.category-reference-page .profile-reference-shell,
body.route-category .category-reference-card,
body:has(.category-reference-card) .category-reference-card,
.profile-reference-page.category-reference-page .category-reference-card {
            max-width: 100% !important;
        }

body.route-category .profile-reference-cover,
body:has(.category-reference-card) .profile-reference-cover,
.profile-reference-page.category-reference-page .profile-reference-cover {
            height: 180px !important;
            min-height: 180px !important;
            max-width: 100% !important;
        }

body.route-category .profile-reference-cover img,
body:has(.category-reference-card) .profile-reference-cover img,
.profile-reference-page.category-reference-page .profile-reference-cover img {
            height: 180px !important;
            min-height: 180px !important;
        }

body.route-category .profile-reference-actions-wrapper,
body:has(.category-reference-card) .profile-reference-actions-wrapper,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper {
            top: 72px !important;
            right: 16px !important;
        }

}

/* FINAL: Kapak eski boyuta alındı, profil resmi büyütüldü, Poppins + normal font, mavi buton durumları */

body.route-category *,
body:has(.category-reference-card) *,
.profile-reference-page.category-reference-page,
.profile-reference-page.category-reference-page * {
        font-family: "Poppins", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
    }

body.route-category .profile-reference-shell,
body:has(.category-reference-card) .profile-reference-shell,
.profile-reference-page.category-reference-page .profile-reference-shell {
        width: 100% !important;
        max-width: none !important;
    }

body.route-category .category-reference-card,
body:has(.category-reference-card) .category-reference-card,
.profile-reference-page.category-reference-page .category-reference-card {
        width: 100% !important;
        max-width: none !important;
    }

body.route-category .profile-reference-cover,
body:has(.category-reference-card) .profile-reference-cover,
.profile-reference-page.category-reference-page .profile-reference-cover {
        width: 100% !important;
        max-width: none !important;
        height: 150px !important;
        min-height: 150px !important;
        aspect-ratio: auto !important;
        overflow: hidden !important;
        border-radius: 16px 16px 0 0 !important;
    }

body.route-category .profile-reference-cover img,
body:has(.category-reference-card) .profile-reference-cover img,
.profile-reference-page.category-reference-page .profile-reference-cover img {
        width: 100% !important;
        height: 150px !important;
        min-height: 150px !important;
        object-fit: cover !important;
        object-position: center !important;
    }

body.route-category .profile-reference-top,
body:has(.category-reference-card) .profile-reference-top,
.profile-reference-page.category-reference-page .profile-reference-top {
        margin-top: -58px !important;
        min-height: 132px !important;
    }

body.route-category .profile-reference-avatar,
body:has(.category-reference-card) .profile-reference-avatar,
.profile-reference-page.category-reference-page .profile-reference-avatar {
        width: 116px !important;
        height: 116px !important;
        border: 4px solid #ffffff !important;
        background-color: #2563eb !important;
    }

body.route-category .profile-reference-avatar span,
body:has(.category-reference-card) .profile-reference-avatar span,
.profile-reference-page.category-reference-page .profile-reference-avatar span {
        font-size: 42px !important;
        font-weight: 400 !important;
        color: #ffffff !important;
    }

body.route-category .profile-reference-name,
body:has(.category-reference-card) .profile-reference-name,
.profile-reference-page.category-reference-page .profile-reference-name {
        font-weight: 500 !important;
        letter-spacing: 0 !important;
    }

body.route-category .profile-reference-meta-row,
body.route-category .profile-reference-meta-row *,
body:has(.category-reference-card) .profile-reference-meta-row,
body:has(.category-reference-card) .profile-reference-meta-row *,
.profile-reference-page.category-reference-page .profile-reference-meta-row,
.profile-reference-page.category-reference-page .profile-reference-meta-row *,
body.route-category .profile-reference-bio,
body:has(.category-reference-card) .profile-reference-bio,
.profile-reference-page.category-reference-page .profile-reference-bio {
        font-weight: 400 !important;
    }

body.route-category .profile-reference-tab,
body:has(.category-reference-card) .profile-reference-tab,
.profile-reference-page.category-reference-page .profile-reference-tab {
        font-weight: 500 !important;
    }

body.route-category .profile-reference-tab[aria-current="page"],
body:has(.category-reference-card) .profile-reference-tab[aria-current="page"],
.profile-reference-page.category-reference-page .profile-reference-tab[aria-current="page"] {
        color: #2563eb !important;
        border-bottom-color: #2563eb !important;
    }

body.route-category .profile-reference-btn-primary,
body:has(.category-reference-card) .profile-reference-btn-primary,
.profile-reference-page.category-reference-page .profile-reference-btn-primary {
        background: #2563eb !important;
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 500 !important;
        box-shadow: none !important;
    }

body.route-category .profile-reference-btn-primary:hover,
body.route-category .profile-reference-btn-primary:focus,
body.route-category .profile-reference-btn-primary:active,
body:has(.category-reference-card) .profile-reference-btn-primary:hover,
body:has(.category-reference-card) .profile-reference-btn-primary:focus,
body:has(.category-reference-card) .profile-reference-btn-primary:active,
.profile-reference-page.category-reference-page .profile-reference-btn-primary:hover,
.profile-reference-page.category-reference-page .profile-reference-btn-primary:focus,
.profile-reference-page.category-reference-page .profile-reference-btn-primary:active {
        background: #1d4ed8 !important;
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
    }

body.route-category .profile-reference-actions-wrapper,
body:has(.category-reference-card) .profile-reference-actions-wrapper,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper {
        top: 76px !important;
        right: 24px !important;
    }

@media (max-width: 640px) {
body.route-category .profile-reference-cover,
body:has(.category-reference-card) .profile-reference-cover,
.profile-reference-page.category-reference-page .profile-reference-cover,
body.route-category .profile-reference-cover img,
body:has(.category-reference-card) .profile-reference-cover img,
.profile-reference-page.category-reference-page .profile-reference-cover img {
            height: 150px !important;
            min-height: 150px !important;
        }

body.route-category .profile-reference-top,
body:has(.category-reference-card) .profile-reference-top,
.profile-reference-page.category-reference-page .profile-reference-top {
            margin-top: -54px !important;
            min-height: 124px !important;
        }

body.route-category .profile-reference-avatar,
body:has(.category-reference-card) .profile-reference-avatar,
.profile-reference-page.category-reference-page .profile-reference-avatar {
            width: 108px !important;
            height: 108px !important;
        }

body.route-category .profile-reference-actions-wrapper,
body:has(.category-reference-card) .profile-reference-actions-wrapper,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper {
            top: 72px !important;
            right: 16px !important;
        }

}

/* SON DÜZENLEME: kapak biraz yüksek, mobil daha geniş, dark mode uyumlu */

body.route-category .profile-reference-cover,
body:has(.category-reference-card) .profile-reference-cover,
.profile-reference-page.category-reference-page .profile-reference-cover {
        height: 190px !important;
        min-height: 190px !important;
    }

body.route-category .profile-reference-cover img,
body:has(.category-reference-card) .profile-reference-cover img,
.profile-reference-page.category-reference-page .profile-reference-cover img {
        height: 190px !important;
        min-height: 190px !important;
        object-fit: cover !important;
        object-position: center !important;
    }

@media (max-width: 640px) {
body.route-category .profile-reference-shell,
body:has(.category-reference-card) .profile-reference-shell,
.profile-reference-page.category-reference-page .profile-reference-shell {
            padding-left: 6px !important;
            padding-right: 6px !important;
            width: 100% !important;
            max-width: 100% !important;
        }

body.route-category .category-reference-card,
body:has(.category-reference-card) .category-reference-card,
.profile-reference-page.category-reference-page .profile-reference-card,
.profile-reference-page.category-reference-page .category-reference-card {
            width: 100% !important;
            max-width: 100% !important;
        }

body.route-category .profile-reference-cover,
body:has(.category-reference-card) .profile-reference-cover,
.profile-reference-page.category-reference-page .profile-reference-cover {
            height: 172px !important;
            min-height: 172px !important;
        }

body.route-category .profile-reference-cover img,
body:has(.category-reference-card) .profile-reference-cover img,
.profile-reference-page.category-reference-page .profile-reference-cover img {
            height: 172px !important;
            min-height: 172px !important;
        }

body.route-category .profile-reference-body,
body:has(.category-reference-card) .profile-reference-body,
.profile-reference-page.category-reference-page .profile-reference-body {
            padding-left: 14px !important;
            padding-right: 14px !important;
        }

body.route-category .profile-reference-tabs-bar,
body:has(.category-reference-card) .profile-reference-tabs-bar,
.profile-reference-page.category-reference-page .profile-reference-tabs-bar {
            padding-left: 14px !important;
            padding-right: 14px !important;
        }

}

html.dark body.route-category,
body.dark.route-category,
body.route-category.dark,
.dark body.route-category,
html.dark body:has(.category-reference-card),
body.dark:has(.category-reference-card),
.dark body:has(.category-reference-card) {
        background-color: #0f172a !important;
        color: #e5e7eb !important;
    }

html.dark body.route-category .category-reference-card,
body.dark.route-category .category-reference-card,
body.route-category.dark .category-reference-card,
.dark body.route-category .category-reference-card,
html.dark body:has(.category-reference-card) .category-reference-card,
body.dark:has(.category-reference-card) .category-reference-card,
.dark body:has(.category-reference-card) .category-reference-card,
html.dark .profile-reference-page.category-reference-page .profile-reference-card,
.dark .profile-reference-page.category-reference-page .profile-reference-card {
        background-color: #111827 !important;
        border-color: #1f2937 !important;
    }

html.dark body.route-category .profile-reference-tabs-bar,
body.dark.route-category .profile-reference-tabs-bar,
body.route-category.dark .profile-reference-tabs-bar,
.dark body.route-category .profile-reference-tabs-bar,
html.dark body:has(.category-reference-card) .profile-reference-tabs-bar,
body.dark:has(.category-reference-card) .profile-reference-tabs-bar,
.dark body:has(.category-reference-card) .profile-reference-tabs-bar,
html.dark .profile-reference-page.category-reference-page .profile-reference-tabs-bar,
.dark .profile-reference-page.category-reference-page .profile-reference-tabs-bar {
        background-color: #111827 !important;
        border-color: #1f2937 !important;
    }

html.dark body.route-category .profile-reference-name,
body.dark.route-category .profile-reference-name,
body.route-category.dark .profile-reference-name,
.dark body.route-category .profile-reference-name,
html.dark body:has(.category-reference-card) .profile-reference-name,
body.dark:has(.category-reference-card) .profile-reference-name,
.dark body:has(.category-reference-card) .profile-reference-name,
html.dark .profile-reference-page.category-reference-page .profile-reference-name,
.dark .profile-reference-page.category-reference-page .profile-reference-name,
html.dark body.route-category .profile-reference-meta-row strong,
body.dark.route-category .profile-reference-meta-row strong,
.dark body.route-category .profile-reference-meta-row strong,
html.dark body:has(.category-reference-card) .profile-reference-meta-row strong,
.dark body:has(.category-reference-card) .profile-reference-meta-row strong {
        color: #f8fafc !important;
    }

html.dark body.route-category .profile-reference-meta-row,
body.dark.route-category .profile-reference-meta-row,
body.route-category.dark .profile-reference-meta-row,
.dark body.route-category .profile-reference-meta-row,
html.dark body:has(.category-reference-card) .profile-reference-meta-row,
body.dark:has(.category-reference-card) .profile-reference-meta-row,
.dark body:has(.category-reference-card) .profile-reference-meta-row,
html.dark body.route-category .profile-reference-bio,
body.dark.route-category .profile-reference-bio,
body.route-category.dark .profile-reference-bio,
.dark body.route-category .profile-reference-bio,
html.dark body:has(.category-reference-card) .profile-reference-bio,
body.dark:has(.category-reference-card) .profile-reference-bio,
.dark body:has(.category-reference-card) .profile-reference-bio {
        color: #cbd5e1 !important;
    }

html.dark body.route-category .profile-reference-tab,
body.dark.route-category .profile-reference-tab,
body.route-category.dark .profile-reference-tab,
.dark body.route-category .profile-reference-tab,
html.dark body:has(.category-reference-card) .profile-reference-tab,
body.dark:has(.category-reference-card) .profile-reference-tab,
.dark body:has(.category-reference-card) .profile-reference-tab {
        color: #94a3b8 !important;
        background: transparent !important;
    }

html.dark body.route-category .profile-reference-tab:hover,
body.dark.route-category .profile-reference-tab:hover,
body.route-category.dark .profile-reference-tab:hover,
.dark body.route-category .profile-reference-tab:hover,
html.dark body:has(.category-reference-card) .profile-reference-tab:hover,
body.dark:has(.category-reference-card) .profile-reference-tab:hover,
.dark body:has(.category-reference-card) .profile-reference-tab:hover,
html.dark body.route-category .profile-reference-tab[aria-current="page"],
body.dark.route-category .profile-reference-tab[aria-current="page"],
body.route-category.dark .profile-reference-tab[aria-current="page"],
.dark body.route-category .profile-reference-tab[aria-current="page"],
html.dark body:has(.category-reference-card) .profile-reference-tab[aria-current="page"],
body.dark:has(.category-reference-card) .profile-reference-tab[aria-current="page"],
.dark body:has(.category-reference-card) .profile-reference-tab[aria-current="page"] {
        color: #60a5fa !important;
        border-bottom-color: #60a5fa !important;
    }

html.dark body.route-category .profile-reference-avatar,
body.dark.route-category .profile-reference-avatar,
body.route-category.dark .profile-reference-avatar,
.dark body.route-category .profile-reference-avatar,
html.dark body:has(.category-reference-card) .profile-reference-avatar,
body.dark:has(.category-reference-card) .profile-reference-avatar,
.dark body:has(.category-reference-card) .profile-reference-avatar {
        border-color: #111827 !important;
    }

html.dark body.route-category .profile-reference-empty,
body.dark.route-category .profile-reference-empty,
body.route-category.dark .profile-reference-empty,
.dark body.route-category .profile-reference-empty,
html.dark body:has(.category-reference-card) .profile-reference-empty,
body.dark:has(.category-reference-card) .profile-reference-empty,
.dark body:has(.category-reference-card) .profile-reference-empty {
        background-color: #111827 !important;
        border-color: #1f2937 !important;
        color: #cbd5e1 !important;
    }

/* SON DÜZELTME: Mobil tam genişlik + mobilde Choose image sadece üstüne gelince/tıklanınca görünsün */

@media (max-width: 640px) {
.profile-reference-page.category-reference-page {
            overflow-x: hidden !important;
        }

body.route-category .layout-main,
body:has(.category-reference-card) .layout-main {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

body.route-category .profile-reference-page,
body:has(.category-reference-card) .profile-reference-page,
.profile-reference-page.category-reference-page {
            width: 100% !important;
            max-width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            background: transparent !important;
        }

body.route-category .profile-reference-shell,
body:has(.category-reference-card) .profile-reference-shell,
.profile-reference-page.category-reference-page .profile-reference-shell {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

body.route-category .category-reference-card,
body:has(.category-reference-card) .category-reference-card,
.profile-reference-page.category-reference-page .category-reference-card,
.profile-reference-page.category-reference-page .profile-reference-card {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 0 14px 14px !important;
        }

body.route-category .profile-reference-cover,
body:has(.category-reference-card) .profile-reference-cover,
.profile-reference-page.category-reference-page .profile-reference-cover {
            width: 100% !important;
            max-width: 100% !important;
            border-radius: 0 !important;
        }

body.route-category .profile-reference-cover img,
body:has(.category-reference-card) .profile-reference-cover img,
.profile-reference-page.category-reference-page .profile-reference-cover img {
            width: 100% !important;
            max-width: 100% !important;
        }

body.route-category .profile-reference-body,
body:has(.category-reference-card) .profile-reference-body,
.profile-reference-page.category-reference-page .profile-reference-body {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

body.route-category .profile-reference-tabs-bar,
body:has(.category-reference-card) .profile-reference-tabs-bar,
.profile-reference-page.category-reference-page .profile-reference-tabs-bar {
            padding-left: 16px !important;
            padding-right: 16px !important;
            border-left: 0 !important;
            border-right: 0 !important;
        }

body.route-category .profile-post-card-wrapper,
body:has(.category-reference-card) .profile-post-card-wrapper,
.profile-reference-page.category-reference-page .profile-post-card-wrapper {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

body.route-category .profile-reference-content [data-post-card-shell],
body:has(.category-reference-card) .profile-reference-content [data-post-card-shell],
.profile-reference-page.category-reference-page .profile-reference-content [data-post-card-shell] {
            width: calc(100% - 16px) !important;
            max-width: none !important;
            margin-left: 8px !important;
            margin-right: 8px !important;
        }

}

@media (hover: none) {
.profile-reference-cover-change,
.profile-reference-avatar-change {
            opacity: 0 !important;
            pointer-events: none !important;
        }

.profile-reference-cover:hover .profile-reference-cover-change,
.profile-reference-cover:focus-within .profile-reference-cover-change,
.profile-reference-cover:active .profile-reference-cover-change,
.profile-reference-cover-change:focus,
.profile-reference-cover-change:active,
.profile-reference-avatar-shell:hover .profile-reference-avatar-change,
.profile-reference-avatar-shell:focus-within .profile-reference-avatar-change,
.profile-reference-avatar-shell:active .profile-reference-avatar-change,
.profile-reference-avatar-change:focus,
.profile-reference-avatar-change:active {
            opacity: 1 !important;
            pointer-events: auto !important;
        }

}

/* Mobilde kategori sayfasını tamamen tam genişlik yap */

@media (max-width: 640px) {
html,
body {
            overflow-x: hidden !important;
        }

body.route-category .layout-main,
body:has(.category-reference-card) .layout-main {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

body.route-category .profile-reference-page,
body:has(.category-reference-card) .profile-reference-page,
.profile-reference-page.category-reference-page {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 0 24px !important;
        }

body.route-category .profile-reference-shell,
body:has(.category-reference-card) .profile-reference-shell,
.profile-reference-page.category-reference-page .profile-reference-shell {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

body.route-category .category-reference-card,
body:has(.category-reference-card) .category-reference-card,
.profile-reference-page.category-reference-page .profile-reference-card {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
        }

body.route-category .profile-reference-cover,
body:has(.category-reference-card) .profile-reference-cover,
.profile-reference-page.category-reference-page .profile-reference-cover {
            width: 100% !important;
            max-width: 100% !important;
            height: 188px !important;
            border-radius: 0 !important;
            overflow: hidden !important;
        }

body.route-category .profile-reference-cover img,
body:has(.category-reference-card) .profile-reference-cover img,
.profile-reference-page.category-reference-page .profile-reference-cover img {
            width: 100% !important;
            max-width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center !important;
        }

body.route-category .profile-reference-body,
body:has(.category-reference-card) .profile-reference-body,
.profile-reference-page.category-reference-page .profile-reference-body {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding-left: 16px !important;
            padding-right: 16px !important;
            box-sizing: border-box !important;
        }

body.route-category .profile-reference-tabs-bar,
body:has(.category-reference-card) .profile-reference-tabs-bar,
.profile-reference-page.category-reference-page .profile-reference-tabs-bar {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding-left: 16px !important;
            padding-right: 16px !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
            box-sizing: border-box !important;
        }

body.route-category .profile-reference-content,
body:has(.category-reference-card) .profile-reference-content,
.profile-reference-page.category-reference-page .profile-reference-content {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

body.route-category .profile-post-card-wrapper,
body:has(.category-reference-card) .profile-post-card-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

body.route-category .profile-reference-content [data-post-card-shell],
body:has(.category-reference-card) .profile-reference-content [data-post-card-shell],
body.route-category [data-post-card-shell],
body:has(.category-reference-card) [data-post-card-shell] {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
            box-sizing: border-box !important;
        }

body.route-category .category-feed-post-card,
body:has(.category-reference-card) .category-feed-post-card {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
            box-sizing: border-box !important;
        }

}

/* MOBIL TAM GENISLIK - parent paddinglerini de ezer */

@media (max-width: 640px) {
html,
body {
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
        }

body.route-category .layout-main,
body:has(.category-reference-card) .layout-main,
body.route-category .profile-reference-page,
body:has(.category-reference-card) .profile-reference-page,
body.route-category .profile-reference-shell,
body:has(.category-reference-card) .profile-reference-shell,
.profile-reference-page.category-reference-page,
.profile-reference-page.category-reference-page .profile-reference-shell {
            width: 100vw !important;
            max-width: 100vw !important;
            min-width: 100vw !important;
            margin-left: calc(50% - 50vw) !important;
            margin-right: calc(50% - 50vw) !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            left: auto !important;
            right: auto !important;
        }

body.route-category .category-reference-card,
body:has(.category-reference-card) .category-reference-card,
.profile-reference-page.category-reference-page .profile-reference-card {
            width: 100vw !important;
            max-width: 100vw !important;
            min-width: 100vw !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
        }

body.route-category .profile-reference-cover,
body:has(.category-reference-card) .profile-reference-cover,
.profile-reference-page.category-reference-page .profile-reference-cover {
            width: 100vw !important;
            max-width: 100vw !important;
            min-width: 100vw !important;
            height: 190px !important;
            border-radius: 0 !important;
        }

body.route-category .profile-reference-cover img,
body:has(.category-reference-card) .profile-reference-cover img,
.profile-reference-page.category-reference-page .profile-reference-cover img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center !important;
        }

body.route-category .profile-reference-body,
body:has(.category-reference-card) .profile-reference-body,
.profile-reference-page.category-reference-page .profile-reference-body {
            padding-left: 20px !important;
            padding-right: 20px !important;
        }

body.route-category .profile-reference-tabs-bar,
body:has(.category-reference-card) .profile-reference-tabs-bar,
.profile-reference-page.category-reference-page .profile-reference-tabs-bar {
            width: 100vw !important;
            max-width: 100vw !important;
            min-width: 100vw !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 20px !important;
            padding-right: 20px !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
        }

body.route-category .profile-reference-content,
body:has(.category-reference-card) .profile-reference-content,
.profile-reference-page.category-reference-page .profile-reference-content,
body.route-category .profile-post-card-wrapper,
body:has(.category-reference-card) .profile-post-card-wrapper {
            width: 100vw !important;
            max-width: 100vw !important;
            min-width: 100vw !important;
            margin-left: calc(50% - 50vw) !important;
            margin-right: calc(50% - 50vw) !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

body.route-category .profile-reference-content [data-post-card-shell],
body:has(.category-reference-card) .profile-reference-content [data-post-card-shell],
body.route-category [data-post-card-shell],
body:has(.category-reference-card) [data-post-card-shell] {
            width: 100vw !important;
            max-width: 100vw !important;
            min-width: 100vw !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
        }

body.route-category .profile-reference-actions-wrapper,
body:has(.category-reference-card) .profile-reference-actions-wrapper {
            top: 76px !important;
            right: 20px !important;
        }

}

@media (max-width: 640px) {
html,
body {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden !important;
        }

body.route-category .layout-main,
body:has(.category-reference-card) .layout-main,
body.route-category .profile-reference-page,
body:has(.category-reference-card) .profile-reference-page,
body.route-category .profile-reference-shell,
body:has(.category-reference-card) .profile-reference-shell,
body.route-category .category-reference-card,
body:has(.category-reference-card) .category-reference-card,
body.route-category .profile-reference-card,
body:has(.category-reference-card) .profile-reference-card,
.profile-reference-page.category-reference-page,
.profile-reference-page.category-reference-page .profile-reference-shell,
.profile-reference-page.category-reference-page .profile-reference-card {
            box-sizing: border-box !important;
            display: block !important;
            width: 100vw !important;
            min-width: 100vw !important;
            max-width: 100vw !important;
            margin-left: calc(50% - 50vw) !important;
            margin-right: calc(50% - 50vw) !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
            transform: none !important;
        }

body.route-category .profile-reference-cover,
body:has(.category-reference-card) .profile-reference-cover,
.profile-reference-page.category-reference-page .profile-reference-cover {
            box-sizing: border-box !important;
            width: 100vw !important;
            min-width: 100vw !important;
            max-width: 100vw !important;
            height: 190px !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-radius: 0 !important;
        }

body.route-category .profile-reference-cover img,
body:has(.category-reference-card) .profile-reference-cover img,
.profile-reference-page.category-reference-page .profile-reference-cover img {
            width: 100% !important;
            height: 100% !important;
            max-width: 100% !important;
            object-fit: cover !important;
            object-position: center !important;
        }

body.route-category .profile-reference-body,
body:has(.category-reference-card) .profile-reference-body,
.profile-reference-page.category-reference-page .profile-reference-body {
            box-sizing: border-box !important;
            width: 100% !important;
            max-width: 100% !important;
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

body.route-category .profile-reference-tabs-bar,
body:has(.category-reference-card) .profile-reference-tabs-bar,
.profile-reference-page.category-reference-page .profile-reference-tabs-bar {
            box-sizing: border-box !important;
            width: 100vw !important;
            min-width: 100vw !important;
            max-width: 100vw !important;
            margin-left: calc(50% - 50vw) !important;
            margin-right: calc(50% - 50vw) !important;
            padding-left: 16px !important;
            padding-right: 16px !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
        }

body.route-category .profile-reference-content,
body:has(.category-reference-card) .profile-reference-content,
.profile-reference-page.category-reference-page .profile-reference-content,
body.route-category .profile-post-card-wrapper,
body:has(.category-reference-card) .profile-post-card-wrapper,
body.route-category [data-post-card-shell],
body:has(.category-reference-card) [data-post-card-shell] {
            box-sizing: border-box !important;
            width: 100vw !important;
            min-width: 100vw !important;
            max-width: 100vw !important;
            margin-left: calc(50% - 50vw) !important;
            margin-right: calc(50% - 50vw) !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
        }

body.route-category .profile-reference-actions-wrapper,
body:has(.category-reference-card) .profile-reference-actions-wrapper {
            top: 76px !important;
            right: 16px !important;
        }

}

body.route-category .profile-reference-page,
body:has(.category-reference-card) .profile-reference-page,
.profile-reference-page.category-reference-page,
body.route-category .profile-reference-card,
body:has(.category-reference-card) .profile-reference-card,
body.route-category .category-reference-card,
body:has(.category-reference-card) .category-reference-card,
.profile-reference-page.category-reference-page .profile-reference-card {
        touch-action: pan-y !important;
        -webkit-user-select: text !important;
        user-select: text !important;
    }

body.route-category .profile-reference-cover,
body:has(.category-reference-card) .profile-reference-cover,
.profile-reference-page.category-reference-page .profile-reference-cover {
        touch-action: pan-y !important;
    }

.profile-reference-cover-change,
.profile-reference-avatar-change {
        transition: none !important;
    }

.profile-reference-cover-change {
        opacity: 0 !important;
        pointer-events: none !important;
    }

.profile-reference-cover:hover .profile-reference-cover-change,
.profile-reference-cover:focus-within .profile-reference-cover-change {
        opacity: 1 !important;
        pointer-events: auto !important;
    }

.profile-reference-avatar-change {
        opacity: 0 !important;
        pointer-events: none !important;
    }

.profile-reference-avatar-shell:hover .profile-reference-avatar-change,
.profile-reference-avatar-shell:focus-within .profile-reference-avatar-change {
        opacity: 1 !important;
        pointer-events: auto !important;
    }

@media (max-width: 960px) {
html,
body {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            touch-action: pan-y !important;
        }

body.route-category .layout-main,
body:has(.category-reference-card) .layout-main,
body.route-category .profile-reference-page,
body:has(.category-reference-card) .profile-reference-page,
body.route-category .profile-reference-shell,
body:has(.category-reference-card) .profile-reference-shell,
.profile-reference-page.category-reference-page,
.profile-reference-page.category-reference-page .profile-reference-shell {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            transform: none !important;
            left: auto !important;
            right: auto !important;
            box-sizing: border-box !important;
        }

body.route-category .category-reference-card,
body:has(.category-reference-card) .category-reference-card,
body.route-category .profile-reference-card,
body:has(.category-reference-card) .profile-reference-card,
.profile-reference-page.category-reference-page .profile-reference-card {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
        }

body.route-category .profile-reference-cover,
body:has(.category-reference-card) .profile-reference-cover,
.profile-reference-page.category-reference-page .profile-reference-cover {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            height: 190px !important;
            margin: 0 !important;
            border-radius: 0 !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
        }

body.route-category .profile-reference-cover img,
body:has(.category-reference-card) .profile-reference-cover img,
.profile-reference-page.category-reference-page .profile-reference-cover img {
            width: 100% !important;
            max-width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center !important;
            display: block !important;
        }

body.route-category .profile-reference-body,
body:has(.category-reference-card) .profile-reference-body,
.profile-reference-page.category-reference-page .profile-reference-body {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 16px 0 !important;
            box-sizing: border-box !important;
        }

body.route-category .profile-reference-top,
body:has(.category-reference-card) .profile-reference-top,
.profile-reference-page.category-reference-page .profile-reference-top {
            margin-top: -58px !important;
            min-height: 116px !important;
        }

body.route-category .profile-reference-avatar,
body:has(.category-reference-card) .profile-reference-avatar,
.profile-reference-page.category-reference-page .profile-reference-avatar {
            width: 110px !important;
            height: 110px !important;
            border: 3px solid #ffffff !important;
        }

body.route-category .profile-reference-actions-wrapper,
body:has(.category-reference-card) .profile-reference-actions-wrapper,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper {
            position: absolute !important;
            top: 76px !important;
            right: 16px !important;
            margin: 0 !important;
            z-index: 8 !important;
        }

body.route-category .profile-reference-tabs-bar,
body:has(.category-reference-card) .profile-reference-tabs-bar,
.profile-reference-page.category-reference-page .profile-reference-tabs-bar {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 16px !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
            box-sizing: border-box !important;
        }

body.route-category .profile-reference-tabs,
body:has(.category-reference-card) .profile-reference-tabs,
.profile-reference-page.category-reference-page .profile-reference-tabs {
            gap: 12px !important;
        }

body.route-category .profile-reference-content,
body:has(.category-reference-card) .profile-reference-content,
.profile-reference-page.category-reference-page .profile-reference-content,
body.route-category .profile-post-card-wrapper,
body:has(.category-reference-card) .profile-post-card-wrapper {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            box-sizing: border-box !important;
        }

body.route-category .profile-reference-content [data-post-card-shell],
body:has(.category-reference-card) .profile-reference-content [data-post-card-shell],
body.route-category [data-post-card-shell],
body:has(.category-reference-card) [data-post-card-shell] {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
            box-sizing: border-box !important;
        }

.profile-reference-cover-change,
.profile-reference-avatar-change {
            opacity: 0 !important;
            pointer-events: none !important;
            display: none !important;
        }

.profile-reference-cover,
.profile-reference-avatar-shell,
.profile-reference-body,
.profile-reference-heading,
.profile-reference-meta-row,
.profile-reference-bio,
.profile-reference-tabs-bar,
.profile-reference-content {
            touch-action: pan-y !important;
            -webkit-user-select: text !important;
            user-select: text !important;
        }

}

@media (prefers-color-scheme: dark) {
body.route-category .category-reference-card,
body:has(.category-reference-card) .category-reference-card,
body.route-category .profile-reference-card,
body:has(.category-reference-card) .profile-reference-card,
body.route-category .profile-reference-tabs-bar,
body:has(.category-reference-card) .profile-reference-tabs-bar,
.profile-reference-page.category-reference-page .profile-reference-card,
.profile-reference-page.category-reference-page .profile-reference-tabs-bar {
            background: #111827 !important;
            border-color: #1f2937 !important;
        }

body.route-category .profile-reference-name,
body.route-category .profile-reference-bio,
body.route-category .profile-reference-meta-row strong,
body:has(.category-reference-card) .profile-reference-name,
body:has(.category-reference-card) .profile-reference-bio,
body:has(.category-reference-card) .profile-reference-meta-row strong {
            color: #f8fafc !important;
        }

body.route-category .profile-reference-meta-row,
body:has(.category-reference-card) .profile-reference-meta-row,
body.route-category .profile-reference-tab,
body:has(.category-reference-card) .profile-reference-tab {
            color: #94a3b8 !important;
        }

}

/* FINAL FIX: Mobilde kategori sayfasini tam genislik yapar; header/sag/sol sutunlara masaustunde dokunmaz */
@media (max-width: 640px) {
    html,
    body {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden !important;
    }

    body.route-category,
    body:has(.category-reference-card) {
        overflow-x: hidden !important;
    }

    body.route-category .main-grid,
    body:has(.category-reference-card) .main-grid,
    body.route-category .layout-main,
    body:has(.category-reference-card) .layout-main {
        width: 100vw !important;
        max-width: 100vw !important;
        min-width: 0 !important;
        margin-left: calc(50% - 50vw) !important;
        margin-right: calc(50% - 50vw) !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        gap: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-page,
    body:has(.category-reference-card) .profile-reference-page,
    .profile-reference-page.category-reference-page {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding: 0 0 24px !important;
        background: transparent !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-shell,
    body:has(.category-reference-card) .profile-reference-shell,
    .profile-reference-page.category-reference-page .profile-reference-shell {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .category-reference-card,
    body.route-category .profile-reference-card,
    body:has(.category-reference-card) .category-reference-card,
    body:has(.category-reference-card) .profile-reference-card,
    .profile-reference-page.category-reference-page .category-reference-card,
    .profile-reference-page.category-reference-page .profile-reference-card {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-radius: 0 !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-cover,
    body:has(.category-reference-card) .profile-reference-cover,
    .profile-reference-page.category-reference-page .profile-reference-cover {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        height: 188px !important;
        min-height: 188px !important;
        margin: 0 !important;
        border-radius: 0 !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-cover img,
    body:has(.category-reference-card) .profile-reference-cover img,
    .profile-reference-page.category-reference-page .profile-reference-cover img {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        height: 100% !important;
        min-height: 188px !important;
        object-fit: cover !important;
        object-position: center !important;
        border-radius: 0 !important;
    }

    body.route-category .profile-reference-body,
    body:has(.category-reference-card) .profile-reference-body,
    .profile-reference-page.category-reference-page .profile-reference-body {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding-left: 16px !important;
        padding-right: 16px !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-tabs-bar,
    body:has(.category-reference-card) .profile-reference-tabs-bar,
    .profile-reference-page.category-reference-page .profile-reference-tabs-bar {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding-left: 16px !important;
        padding-right: 16px !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-radius: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-content,
    body:has(.category-reference-card) .profile-reference-content,
    .profile-reference-page.category-reference-page .profile-reference-content,
    body.route-category .profile-post-card-wrapper,
    body:has(.category-reference-card) .profile-post-card-wrapper,
    .profile-reference-page.category-reference-page .profile-post-card-wrapper {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-content [data-post-card-shell],
    body.route-category [data-post-card-shell],
    body:has(.category-reference-card) .profile-reference-content [data-post-card-shell],
    body:has(.category-reference-card) [data-post-card-shell],
    .profile-reference-page.category-reference-page .profile-reference-content [data-post-card-shell],
    body.route-category .category-feed-post-card,
    body:has(.category-reference-card) .category-feed-post-card,
    .profile-reference-page.category-reference-page .category-feed-post-card {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-radius: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-info-card,
    body:has(.category-reference-card) .profile-reference-info-card,
    .profile-reference-page.category-reference-page .profile-reference-info-card {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-radius: 0 !important;
        box-sizing: border-box !important;
    }
}


/* ========================================================================
   FINAL MOBIL TAM GENISLIK DUZELTMESI
   Bu blok en sonda kalmali. Onceki tum mobil margin/padding/width kurallarini ezer.
   ======================================================================== */
@media (max-width: 640px) {
    html,
    body {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box !important;
    }

    body.route-category,
    body:has(.category-reference-card),
    body.alma-app.route-category {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden !important;
    }

    body.route-category .main-grid,
    body.route-category .main-grid.main-grid--no-pad,
    body.route-category .layout-main,
    body.route-category main,
    body.alma-app.route-category .main-grid,
    body.alma-app.route-category .main-grid.main-grid--no-pad,
    body.alma-app.route-category .layout-main,
    body.alma-app.route-category main,
    body:has(.category-reference-card) .main-grid,
    body:has(.category-reference-card) .layout-main,
    body:has(.category-reference-card) main {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        box-sizing: border-box !important;
        overflow-x: hidden !important;
    }

    body.route-category .profile-reference-page,
    body.alma-app.route-category .profile-reference-page,
    body:has(.category-reference-card) .profile-reference-page,
    .profile-reference-page.category-reference-page {
        width: 100vw !important;
        max-width: 100vw !important;
        min-width: 0 !important;
        margin-left: calc(50% - 50vw) !important;
        margin-right: calc(50% - 50vw) !important;
        padding: 0 0 24px !important;
        background: transparent !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-shell,
    body.alma-app.route-category .profile-reference-shell,
    body:has(.category-reference-card) .profile-reference-shell,
    .profile-reference-page.category-reference-page .profile-reference-shell {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .category-reference-card,
    body.alma-app.route-category .category-reference-card,
    body:has(.category-reference-card) .category-reference-card,
    .profile-reference-page.category-reference-page .category-reference-card,
    .profile-reference-page.category-reference-page .profile-reference-card {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-radius: 0 !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
    }

    body.route-category .profile-reference-cover,
    body.alma-app.route-category .profile-reference-cover,
    body:has(.category-reference-card) .profile-reference-cover,
    .profile-reference-page.category-reference-page .profile-reference-cover {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        height: 188px !important;
        min-height: 188px !important;
        margin: 0 !important;
        border-radius: 0 !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-cover img,
    body.alma-app.route-category .profile-reference-cover img,
    body:has(.category-reference-card) .profile-reference-cover img,
    .profile-reference-page.category-reference-page .profile-reference-cover img {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        height: 100% !important;
        min-height: 100% !important;
        object-fit: cover !important;
        object-position: center !important;
        border-radius: 0 !important;
    }

    body.route-category .profile-reference-body,
    body.alma-app.route-category .profile-reference-body,
    body:has(.category-reference-card) .profile-reference-body,
    .profile-reference-page.category-reference-page .profile-reference-body {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding-left: 16px !important;
        padding-right: 16px !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-top,
    body.alma-app.route-category .profile-reference-top,
    body:has(.category-reference-card) .profile-reference-top,
    .profile-reference-page.category-reference-page .profile-reference-top {
        margin-top: -58px !important;
        min-height: 116px !important;
    }

    body.route-category .profile-reference-avatar,
    body.alma-app.route-category .profile-reference-avatar,
    body:has(.category-reference-card) .profile-reference-avatar,
    .profile-reference-page.category-reference-page .profile-reference-avatar {
        width: 110px !important;
        height: 110px !important;
        border: 3px solid #ffffff !important;
    }

    body.route-category .profile-reference-actions-wrapper,
    body.alma-app.route-category .profile-reference-actions-wrapper,
    body:has(.category-reference-card) .profile-reference-actions-wrapper,
    .profile-reference-page.category-reference-page .profile-reference-actions-wrapper {
        position: absolute !important;
        top: 76px !important;
        right: 16px !important;
        margin: 0 !important;
        z-index: 8 !important;
    }

    body.route-category .profile-reference-tabs-bar,
    body.alma-app.route-category .profile-reference-tabs-bar,
    body:has(.category-reference-card) .profile-reference-tabs-bar,
    .profile-reference-page.category-reference-page .profile-reference-tabs-bar {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding: 0 16px !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-radius: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-tabs,
    body.alma-app.route-category .profile-reference-tabs,
    body:has(.category-reference-card) .profile-reference-tabs,
    .profile-reference-page.category-reference-page .profile-reference-tabs {
        gap: 12px !important;
        min-width: 0 !important;
        max-width: 100% !important;
        overflow-x: auto !important;
        scrollbar-width: none !important;
    }

    body.route-category .profile-reference-tabs::-webkit-scrollbar,
    body.alma-app.route-category .profile-reference-tabs::-webkit-scrollbar,
    body:has(.category-reference-card) .profile-reference-tabs::-webkit-scrollbar,
    .profile-reference-page.category-reference-page .profile-reference-tabs::-webkit-scrollbar {
        display: none !important;
    }

    body.route-category .profile-reference-content,
    body.alma-app.route-category .profile-reference-content,
    body:has(.category-reference-card) .profile-reference-content,
    .profile-reference-page.category-reference-page .profile-reference-content,
    body.route-category .profile-post-card-wrapper,
    body.alma-app.route-category .profile-post-card-wrapper,
    body:has(.category-reference-card) .profile-post-card-wrapper,
    .profile-reference-page.category-reference-page .profile-post-card-wrapper,
    body.alma-app.route-category [data-category-post-panel] {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-content [data-post-card-shell],
    body.route-category [data-post-card-shell],
    body.alma-app.route-category .profile-reference-content [data-post-card-shell],
    body.alma-app.route-category .profile-post-card-wrapper > [data-post-card-shell],
    body.alma-app.route-category .profile-post-card-wrapper > .post-card,
    body:has(.category-reference-card) .profile-reference-content [data-post-card-shell],
    body:has(.category-reference-card) [data-post-card-shell],
    .profile-reference-page.category-reference-page .profile-reference-content [data-post-card-shell],
    body.route-category .category-feed-post-card,
    body.alma-app.route-category .category-feed-post-card,
    body:has(.category-reference-card) .category-feed-post-card,
    .profile-reference-page.category-reference-page .category-feed-post-card {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-radius: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-info-card,
    body.alma-app.route-category .profile-reference-info-card,
    body:has(.category-reference-card) .profile-reference-info-card,
    .profile-reference-page.category-reference-page .profile-reference-info-card {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-radius: 0 !important;
        box-sizing: border-box !important;
    }

    .profile-reference-cover-change,
    .profile-reference-avatar-change {
        opacity: 0 !important;
        pointer-events: none !important;
        display: none !important;
    }

    .profile-reference-cover,
    .profile-reference-avatar-shell,
    .profile-reference-body,
    .profile-reference-heading,
    .profile-reference-meta-row,
    .profile-reference-bio,
    .profile-reference-tabs-bar,
    .profile-reference-content {
        touch-action: pan-y !important;
        -webkit-user-select: text !important;
        user-select: text !important;
    }
}


/* ========================================================================
   FINAL FIX: MOBILDE HEADER SABIT
   - Sadece mobil/tablet ekranda header üstte sabit kalır.
   - Kategori sayfasındaki profil/kapak/tab/post genişlik ayarlarına dokunmaz.
   - iOS/Safari için sticky + fixed destekli güvenli yapı.
   - Dark mode arka planı uyumludur.
   ======================================================================== */
@media (max-width: 768px) {
    html,
    body {
        scroll-padding-top: var(--ografi-mobile-header-height, 64px) !important;
    }

    body.alma-app .app-header,
    body.alma-app .site-header,
    body.alma-app .main-header,
    body.alma-app .top-header,
    body.alma-app .alma-header,
    body.alma-app [data-app-header],
    body.alma-app > header,
    .app-header,
    .site-header,
    .main-header,
    .top-header,
    .alma-header,
    [data-app-header],
    body > header {
        position: sticky !important;
        position: -webkit-sticky !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 99999 !important;
        width: 100% !important;
        max-width: 100% !important;
        background: rgba(255, 255, 255, 0.96) !important;
        border-bottom: 1px solid rgba(229, 231, 235, 0.95) !important;
        box-shadow: none !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        transform: translateZ(0) !important;
        will-change: transform !important;
    }

    body.alma-app.route-category .app-header,
    body.alma-app.route-category .site-header,
    body.alma-app.route-category .main-header,
    body.alma-app.route-category .top-header,
    body.alma-app.route-category .alma-header,
    body.alma-app.route-category [data-app-header],
    body.alma-app.route-category > header,
    body.route-category .app-header,
    body.route-category .site-header,
    body.route-category .main-header,
    body.route-category .top-header,
    body.route-category .alma-header,
    body.route-category [data-app-header],
    body.route-category > header {
        position: sticky !important;
        position: -webkit-sticky !important;
        top: 0 !important;
        z-index: 99999 !important;
    }

    html.dark body.alma-app .app-header,
    html.dark body.alma-app .site-header,
    html.dark body.alma-app .main-header,
    html.dark body.alma-app .top-header,
    html.dark body.alma-app .alma-header,
    html.dark body.alma-app [data-app-header],
    html.dark body.alma-app > header,
    body.dark.alma-app .app-header,
    body.dark.alma-app .site-header,
    body.dark.alma-app .main-header,
    body.dark.alma-app .top-header,
    body.dark.alma-app .alma-header,
    body.dark.alma-app [data-app-header],
    body.dark.alma-app > header,
    .dark .app-header,
    .dark .site-header,
    .dark .main-header,
    .dark .top-header,
    .dark .alma-header,
    .dark [data-app-header],
    .dark body > header {
        background: rgba(17, 24, 39, 0.96) !important;
        border-bottom-color: rgba(31, 41, 55, 0.95) !important;
    }

    body.alma-app .layout-main,
    body.alma-app main,
    body.route-category .layout-main,
    body.route-category main {
        min-width: 0 !important;
    }
}

</style>
@endpush

@if($categoryToShow)
    <section class="profile-reference-card category-reference-card">
        <div class="profile-reference-cover{{ $hasCustomCover ? '' : ' category-reference-cover--default' }}">
            <img
                src="{{ $coverUrl }}"
                alt="{{ $name }}"
                loading="lazy"
                onerror="this.onerror=null;this.closest('.profile-reference-cover').classList.add('category-reference-cover--default');this.remove();"
            >

            @auth
                @if($canManage)
                    <a href="{{ route('blog.category.edit', $categoryToShow) }}" class="profile-reference-cover-change" aria-label="Kapak görselini değiştir">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M4 8.75A2.75 2.75 0 0 1 6.75 6h.88c.7 0 1.35-.36 1.72-.96l.3-.48A2.75 2.75 0 0 1 12 3.25h0a2.75 2.75 0 0 1 2.35 1.31l.3.48c.37.6 1.02.96 1.72.96h.88A2.75 2.75 0 0 1 20 8.75v7.5A2.75 2.75 0 0 1 17.25 19H6.75A2.75 2.75 0 0 1 4 16.25v-7.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 15.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                        <span>Change cover</span>
                    </a>
                @endif
            @endauth
        </div>

        <div class="profile-reference-body">
            <div class="profile-reference-top">
                <div class="profile-reference-avatar-wrap">
                    <div class="profile-reference-avatar-shell">
                        <button type="button" class="profile-reference-avatar-button" data-profile-avatar-open aria-label="{{ $name }}">
                            <div class="profile-reference-avatar">
                                @if($profile)
                                    <img src="{{ $profile }}" alt="{{ $name }}" loading="lazy">
                                @else
                                    <span class="text-4xl font-medium text-slate-500">{{ $initials }}</span>
                                @endif
                            </div>
                        </button>

                        @auth
                            @if($canManage)
                                <a href="{{ route('blog.category.edit', $categoryToShow) }}" class="profile-reference-avatar-change" aria-label="Profil görselini değiştir">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M4 8.75A2.75 2.75 0 0 1 6.75 6h.88c.7 0 1.35-.36 1.72-.96l.3-.48A2.75 2.75 0 0 1 12 3.25h0a2.75 2.75 0 0 1 2.35 1.31l.3.48c.37.6 1.02.96 1.72.96h.88A2.75 2.75 0 0 1 20 8.75v7.5A2.75 2.75 0 0 1 17.25 19H6.75A2.75 2.75 0 0 1 4 16.25v-7.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 15.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                    <span>Choose image</span>
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>

            <div class="profile-reference-heading">
                <div class="profile-reference-name-section">
                    <div class="profile-reference-name-row">
                        <div class="profile-reference-name-wrapper">
                            <h1 class="profile-reference-name">{{ $name }}</h1>
                        </div>
                    </div>

                    @if($usernameLabel)
                        <p class="profile-reference-username">{{ $usernameLabel }}</p>
                    @endif
                </div>

                <div class="profile-reference-actions-wrapper">
                    <div class="profile-reference-actions-block">
                        <div class="profile-reference-actions-inline">
                            @auth
                                <form method="POST" action="{{ route('blog.category.join', $categoryToShow) }}">
                                    @csrf
                                    <button type="submit" class="profile-reference-btn-primary">{{ $joinLabel }}</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="profile-reference-btn-primary">Takipdesin</a>
                            @endauth

                            @auth
                                @if($canManage)
                                    <details class="profile-reference-more" data-auto-close-details>
                                        <summary class="profile-reference-menu-summary" aria-label="{{ $moreActionsLabel }}">
                                            {!! $menuIcon !!}
                                        </summary>

                                        <div class="profile-reference-more-panel" role="menu">
                                            <a href="{{ route('blog.category.edit', $categoryToShow) }}" class="profile-reference-menu-item" role="menuitem">
                                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z" stroke="currentColor" stroke-width="1.7"/>
                                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.04.04a2 2 0 1 1-2.83 2.83l-.04-.04a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.06a1.65 1.65 0 0 0-1.08-1.51 1.65 1.65 0 0 0-1.82.33l-.04.04a2 2 0 1 1-2.83-2.83l.04-.04A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.06A1.65 1.65 0 0 0 4.6 8.92a1.65 1.65 0 0 0-.33-1.82l-.04-.04a2 2 0 1 1 2.83-2.83l.04.04a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.06a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.04-.04a2 2 0 1 1 2.83 2.83l-.04.04a1.65 1.65 0 0 0-.33 1.82V9c0 .66.4 1.26 1 1.51H21a2 2 0 1 1 0 4h-.06a1.65 1.65 0 0 0-1.54 1Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <span>Ayarlar</span>
                                            </a>
                                        </div>
                                    </details>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-reference-meta-row mt-2">
                <span><strong>{{ number_format($postsCount) }}</strong> Hikayeler</span>
                <span><strong>{{ number_format($followersCount) }}</strong> Üyeler</span>
                <span><strong>{{ number_format((int) ($categoryViews ?? 0)) }}</strong> Görüşler</span>
            </div>

            @if($description !== '')
                <p class="profile-reference-bio mt-3">{{ $description }}</p>
            @endif

        </div>
    </section>

    <div id="profile-avatar-sheet" class="pointer-events-none fixed inset-0 z-[90] sm:hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-900/50 opacity-0 transition duration-300" data-profile-avatar-close></div>
        <div class="absolute inset-x-0 bottom-0 translate-y-full rounded-t-[28px] bg-white p-4 shadow-[0_-24px_48px_-24px_rgba(15,23,42,0.45)] transition duration-300 ease-out" data-profile-avatar-panel>
            <div class="mx-auto mb-4 h-1.5 w-14 rounded-full bg-slate-200"></div>
            <div class="overflow-hidden rounded-[24px] bg-slate-100">
                @if($profile)
                    <img src="{{ $profile }}" alt="{{ $name }}" class="block h-auto w-full object-cover">
                @else
                    <div class="flex aspect-square items-center justify-center text-6xl font-medium text-slate-400">{{ $initials }}</div>
                @endif
            </div>
            <button type="button" class="mt-4 inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700" data-profile-avatar-close>
                {{ __('post_create.close') }}
            </button>
        </div>
    </div>

    <div id="profile-actions-sheet" class="pointer-events-none fixed inset-0 z-[91]" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-900/50 opacity-0 transition duration-300" data-profile-actions-close></div>
        <div class="absolute inset-x-0 bottom-0 translate-y-full rounded-t-[28px] bg-white p-4 shadow-[0_-24px_48px_-24px_rgba(15,23,42,0.45)] transition duration-300 ease-out sm:left-1/2 sm:max-w-xl sm:-translate-x-1/2" data-profile-actions-panel>
            <div class="mx-auto mb-4 h-1.5 w-14 rounded-full bg-slate-200"></div>
            <button type="button" class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50" data-profile-share data-share-url="{{ $categoryPageUrl }}" data-share-title="{{ $name }}">
                <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-500" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 12V7a2 2 0 0 1 2-2h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="m13 6 4-1v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M17 5 9 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <rect x="5" y="9" width="10" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/>
                </svg>
                {{ $shareLabel }}
            </button>

            @auth
                @if($canManage)
                    <a href="{{ route('blog.category.edit', $categoryToShow) }}" class="mt-1 flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-500" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 20h4l10.5-10.5a2.121 2.121 0 1 0-3-3L5 17v3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Duzenle
                    </a>
                    <form method="POST" action="{{ route('blog.category.destroy', $categoryToShow) }}" class="mt-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left text-sm font-medium text-rose-600 transition hover:bg-rose-50" onclick="return confirm(@js(__('site.category_page.delete_confirm')))">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 7h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <path d="M9 7V5.5A1.5 1.5 0 0 1 10.5 4h3A1.5 1.5 0 0 1 15 5.5V7m-7.5 0 .6 10.2A2 2 0 0 0 10.09 19h3.82a2 2 0 0 0 1.99-1.8L16.5 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Sil
                        </button>
                    </form>
                @elseif($creator)
                    <a href="{{ route('users.report.form', $creator) }}" class="mt-1 flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-slate-500" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 4V20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M6 5H15.2L13.4 9L15.2 13H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Bildir
                    </a>
                @endif
            @endauth

            <button type="button" class="mt-3 inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700" data-profile-actions-close>
                {{ __('post_create.close') }}
            </button>
        </div>
    </div>

    @push('scripts')
    <style>
        @media (max-width: 960px) {
            body.alma-app.route-category .main-grid,
            body.alma-app.route-category .main-grid.main-grid--no-pad {
                width: 100% !important;
                max-width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            body.alma-app.route-category [data-category-post-panel] {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                box-sizing: border-box !important;
            }

            body.alma-app.route-category .profile-post-card-wrapper {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                margin: 0 0 16px !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                box-sizing: border-box !important;
            }

            body.alma-app.route-category .profile-reference-content [data-post-card-shell],
            body.alma-app.route-category .profile-post-card-wrapper > [data-post-card-shell],
            body.alma-app.route-category .profile-post-card-wrapper > .post-card {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                margin: 0 !important;
                padding: 18px 16px 16px !important;
                box-sizing: border-box !important;
            }

            body.alma-app.route-category .category-feed-post-card {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                margin: 0 !important;
                box-sizing: border-box !important;
            }
        }
    

/* ========================================================================
   FINAL FIX 2: Mobilde sadece POST AKISI biraz daraltildi.
   UST PROFIL/KAPAK/TAB ALANINA DOKUNULMADI.
   ======================================================================== */
@media (max-width: 640px) {
    body.route-category .profile-reference-content,
    body.alma-app.route-category .profile-reference-content,
    body:has(.category-reference-card) .profile-reference-content,
    .profile-reference-page.category-reference-page .profile-reference-content {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    body.route-category .profile-post-card-wrapper,
    body.alma-app.route-category .profile-post-card-wrapper,
    body:has(.category-reference-card) .profile-post-card-wrapper,
    .profile-reference-page.category-reference-page .profile-post-card-wrapper {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 0 14px !important;
        padding-left: 12px !important;
        padding-right: 12px !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-content [data-post-card-shell],
    body.route-category [data-post-card-shell],
    body.alma-app.route-category .profile-reference-content [data-post-card-shell],
    body.alma-app.route-category .profile-post-card-wrapper > [data-post-card-shell],
    body.alma-app.route-category .profile-post-card-wrapper > .post-card,
    body:has(.category-reference-card) .profile-reference-content [data-post-card-shell],
    body:has(.category-reference-card) [data-post-card-shell],
    .profile-reference-page.category-reference-page .profile-reference-content [data-post-card-shell],
    body.route-category .category-feed-post-card,
    body.alma-app.route-category .category-feed-post-card,
    body:has(.category-reference-card) .category-feed-post-card,
    .profile-reference-page.category-reference-page .category-feed-post-card {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        border-left: 1px solid #eff3f4 !important;
        border-right: 1px solid #eff3f4 !important;
        border-top: 1px solid #eff3f4 !important;
        border-bottom: 1px solid #eff3f4 !important;
        border-radius: 16px !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
        background: #ffffff !important;
    }

    body.route-category .category-feed-post-card,
    body.alma-app.route-category .category-feed-post-card,
    body:has(.category-reference-card) .category-feed-post-card,
    .profile-reference-page.category-reference-page .category-feed-post-card {
        padding: 18px !important;
    }
}



/* ========================================================================
   FINAL: UST KISMA DOKUNMADAN MOBIL POST AKISI GENISLIK DUZELTMESI
   - Kapak / profil / kategori bilgisi / tab alanini degistirmez.
   - Sadece post akisini mobilde sagdan soldan bosluklu ve radiuslu yapar.
   - Bu blok en sonda kalmali.
   ======================================================================== */
@media (max-width: 640px) {
    body.route-category .profile-reference-content,
    body.alma-app.route-category .profile-reference-content,
    body:has(.category-reference-card) .profile-reference-content,
    .profile-reference-page.category-reference-page .profile-reference-content {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 12px !important;
        padding-right: 12px !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-post-card-wrapper,
    body.alma-app.route-category .profile-post-card-wrapper,
    body:has(.category-reference-card) .profile-post-card-wrapper,
    .profile-reference-page.category-reference-page .profile-post-card-wrapper {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-bottom: 14px !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-content [data-post-card-shell],
    body.route-category [data-post-card-shell],
    body.alma-app.route-category .profile-reference-content [data-post-card-shell],
    body.alma-app.route-category .profile-post-card-wrapper > [data-post-card-shell],
    body.alma-app.route-category .profile-post-card-wrapper > .post-card,
    body:has(.category-reference-card) .profile-reference-content [data-post-card-shell],
    body:has(.category-reference-card) [data-post-card-shell],
    .profile-reference-page.category-reference-page .profile-reference-content [data-post-card-shell],
    body.route-category .category-feed-post-card,
    body.alma-app.route-category .category-feed-post-card,
    body:has(.category-reference-card) .category-feed-post-card,
    .profile-reference-page.category-reference-page .category-feed-post-card {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        border-left: 1px solid #eff3f4 !important;
        border-right: 1px solid #eff3f4 !important;
        border-top: 1px solid #eff3f4 !important;
        border-bottom: 1px solid #eff3f4 !important;
        border-radius: 16px !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
        background: #ffffff !important;
    }

    body.route-category .category-feed-post-card,
    body.alma-app.route-category .category-feed-post-card,
    body:has(.category-reference-card) .category-feed-post-card,
    .profile-reference-page.category-reference-page .category-feed-post-card {
        padding: 18px !important;
    }
}

html.dark body.route-category .profile-reference-content [data-post-card-shell],
.dark body.route-category .profile-reference-content [data-post-card-shell],
html.dark body.alma-app.route-category .profile-reference-content [data-post-card-shell],
.dark body.alma-app.route-category .profile-reference-content [data-post-card-shell],
html.dark body:has(.category-reference-card) .profile-reference-content [data-post-card-shell],
.dark body:has(.category-reference-card) .profile-reference-content [data-post-card-shell],
html.dark .profile-reference-page.category-reference-page .profile-reference-content [data-post-card-shell],
.dark .profile-reference-page.category-reference-page .profile-reference-content [data-post-card-shell],
html.dark body.route-category .category-feed-post-card,
.dark body.route-category .category-feed-post-card,
html.dark body.alma-app.route-category .category-feed-post-card,
.dark body.alma-app.route-category .category-feed-post-card,
html.dark body:has(.category-reference-card) .category-feed-post-card,
.dark body:has(.category-reference-card) .category-feed-post-card,
html.dark .profile-reference-page.category-reference-page .category-feed-post-card,
.dark .profile-reference-page.category-reference-page .category-feed-post-card {
    background: #111827 !important;
    border-color: #1f2937 !important;
}



/* ========================================================================
   FINAL: KATEGORI SHOW MOBIL TAM GENISLIK
   - Sadece mobilde calisir.
   - Kategori ust alanini, kapagi, profil govdesini, tablari, post akisini ve
     Devamini Goster alanini ekran genisligine tam oturtur.
   - Bu blok en sonda kalmali.
   ======================================================================== */
@media (max-width: 640px) {
    html,
    body {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box !important;
    }

    body.route-category,
    body.alma-app.route-category,
    body:has(.category-reference-card),
    .profile-reference-page.category-reference-page {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        overflow-x: hidden !important;
    }

    body.route-category .main-grid,
    body.route-category .main-grid.main-grid--no-pad,
    body.route-category .layout-main,
    body.route-category main,
    body.alma-app.route-category .main-grid,
    body.alma-app.route-category .main-grid.main-grid--no-pad,
    body.alma-app.route-category .layout-main,
    body.alma-app.route-category main,
    body:has(.category-reference-card) .main-grid,
    body:has(.category-reference-card) .main-grid.main-grid--no-pad,
    body:has(.category-reference-card) .layout-main,
    body:has(.category-reference-card) main {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        box-sizing: border-box !important;
        overflow-x: hidden !important;
    }

    body.route-category .profile-reference-page,
    body.alma-app.route-category .profile-reference-page,
    body:has(.category-reference-card) .profile-reference-page,
    .profile-reference-page.category-reference-page {
        width: 100vw !important;
        max-width: 100vw !important;
        min-width: 0 !important;
        margin-left: calc(50% - 50vw) !important;
        margin-right: calc(50% - 50vw) !important;
        padding: 0 0 24px !important;
        background: transparent !important;
        box-sizing: border-box !important;
        overflow-x: hidden !important;
    }

    body.route-category .profile-reference-shell,
    body.alma-app.route-category .profile-reference-shell,
    body:has(.category-reference-card) .profile-reference-shell,
    .profile-reference-page.category-reference-page .profile-reference-shell {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .category-reference-card,
    body.alma-app.route-category .category-reference-card,
    body:has(.category-reference-card) .category-reference-card,
    .profile-reference-page.category-reference-page .category-reference-card,
    .profile-reference-page.category-reference-page .profile-reference-card {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-radius: 0 !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
    }

    body.route-category .profile-reference-cover,
    body.alma-app.route-category .profile-reference-cover,
    body:has(.category-reference-card) .profile-reference-cover,
    .profile-reference-page.category-reference-page .profile-reference-cover {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        height: 188px !important;
        min-height: 188px !important;
        margin: 0 !important;
        border-radius: 0 !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-cover img,
    body.alma-app.route-category .profile-reference-cover img,
    body:has(.category-reference-card) .profile-reference-cover img,
    .profile-reference-page.category-reference-page .profile-reference-cover img {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        height: 100% !important;
        object-fit: cover !important;
        object-position: center !important;
        border-radius: 0 !important;
    }

    body.route-category .profile-reference-body,
    body.alma-app.route-category .profile-reference-body,
    body:has(.category-reference-card) .profile-reference-body,
    .profile-reference-page.category-reference-page .profile-reference-body {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding-left: 16px !important;
        padding-right: 16px !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-tabs-bar,
    body.alma-app.route-category .profile-reference-tabs-bar,
    body:has(.category-reference-card) .profile-reference-tabs-bar,
    .profile-reference-page.category-reference-page .profile-reference-tabs-bar {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding-left: 16px !important;
        padding-right: 16px !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-radius: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-content,
    body.alma-app.route-category .profile-reference-content,
    body:has(.category-reference-card) .profile-reference-content,
    .profile-reference-page.category-reference-page .profile-reference-content {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-post-card-wrapper,
    body.alma-app.route-category .profile-post-card-wrapper,
    body:has(.category-reference-card) .profile-post-card-wrapper,
    .profile-reference-page.category-reference-page .profile-post-card-wrapper {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .profile-reference-content [data-post-card-shell],
    body.route-category [data-post-card-shell],
    body.alma-app.route-category .profile-reference-content [data-post-card-shell],
    body.alma-app.route-category .profile-post-card-wrapper > [data-post-card-shell],
    body.alma-app.route-category .profile-post-card-wrapper > .post-card,
    body:has(.category-reference-card) .profile-reference-content [data-post-card-shell],
    body:has(.category-reference-card) [data-post-card-shell],
    .profile-reference-page.category-reference-page .profile-reference-content [data-post-card-shell],
    body.route-category .category-feed-post-card,
    body.alma-app.route-category .category-feed-post-card,
    body:has(.category-reference-card) .category-feed-post-card,
    .profile-reference-page.category-reference-page .category-feed-post-card {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        border-left: 0 !important;
        border-right: 0 !important;
        border-radius: 0 !important;
        box-sizing: border-box !important;
    }

    body.route-category .ografi-feed-loadmore,
    body.alma-app.route-category .ografi-feed-loadmore,
    body:has(.category-reference-card) .ografi-feed-loadmore,
    .profile-reference-page.category-reference-page .ografi-feed-loadmore {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 12px !important;
        padding-right: 12px !important;
        box-sizing: border-box !important;
    }

    body.route-category .ografi-feed-loadmore__button,
    body.alma-app.route-category .ografi-feed-loadmore__button,
    body:has(.category-reference-card) .ografi-feed-loadmore__button,
    .profile-reference-page.category-reference-page .ografi-feed-loadmore__button {
        width: 100% !important;
        max-width: 100% !important;
    }
}





/* ========================================================================
   FINAL: KATEGORI AKSIYONLARI + MOBIL RESIM BUTONLARI
   - Choose image / Change cover surekli gorunmez.
   - Mobilde sadece profil/kapak alanina dokununca veya focus olunca gorunur.
   - Katil butonunun solunda ikonlar ve uc nokta menusu durur.
   - Menu sadece uc noktaya tiklaninca acilir.
   ======================================================================== */

.profile-reference-actions-wrapper {
    z-index: 80 !important;
}

.profile-reference-actions-block,
.profile-reference-actions-inline {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 8px !important;
    flex-wrap: nowrap !important;
}

.profile-reference-icon-btn,
.profile-reference-menu-summary,
.profile-reference-account-badge {
    display: inline-flex !important;
    width: 36px !important;
    height: 36px !important;
    align-items: center !important;
    justify-content: center !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: transparent !important;
    color: #111827 !important;
    padding: 0 !important;
    text-decoration: none !important;
    flex-shrink: 0 !important;
    box-shadow: none !important;
    cursor: pointer !important;
    -webkit-tap-highlight-color: transparent !important;
}

.profile-reference-icon-btn:hover,
.profile-reference-icon-btn:focus,
.profile-reference-icon-btn:active,
.profile-reference-menu-summary:hover,
.profile-reference-menu-summary:focus,
.profile-reference-menu-summary:active,
.profile-reference-more[open] > .profile-reference-menu-summary {
    background: #f3f4f6 !important;
    color: #111827 !important;
    outline: none !important;
}

.profile-reference-icon-btn svg,
.profile-reference-menu-summary svg,
.profile-reference-account-badge svg {
    width: 19px !important;
    height: 19px !important;
    display: block !important;
    color: currentColor !important;
}

.profile-reference-more {
    position: relative !important;
    display: inline-flex !important;
}

.profile-reference-more > summary {
    list-style: none !important;
}

.profile-reference-more > summary::-webkit-details-marker {
    display: none !important;
}

.profile-reference-more-panel,
.profile-reference-menu-panel {
    position: absolute !important;
    top: calc(100% + 8px) !important;
    right: 0 !important;
    z-index: 100 !important;
    display: none !important;
    min-width: 158px !important;
    padding: 8px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 14px !important;
    background: #ffffff !important;
    color: #111827 !important;
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.14) !important;
}

.profile-reference-more[open] > .profile-reference-more-panel,
.profile-reference-more[open] > .profile-reference-menu-panel {
    display: block !important;
}

.profile-reference-menu-item {
    display: flex !important;
    width: 100% !important;
    min-height: 36px !important;
    align-items: center !important;
    gap: 10px !important;
    border: 0 !important;
    border-radius: 10px !important;
    background: transparent !important;
    color: #111827 !important;
    padding: 0 10px !important;
    font-family: "Poppins", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1 !important;
    text-align: left !important;
    text-decoration: none !important;
    cursor: pointer !important;
}

.profile-reference-menu-item:hover,
.profile-reference-menu-item:focus,
.profile-reference-menu-item:active {
    background: #f3f4f6 !important;
    color: #111827 !important;
    outline: none !important;
}

.profile-reference-menu-item svg {
    width: 16px !important;
    height: 16px !important;
    flex: 0 0 auto !important;
    color: currentColor !important;
}

/* Kapak/profil degistirme butonlari normalde gizli */
.profile-reference-cover-change,
.profile-reference-avatar-change {
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
}

/* Sadece ustune gelince, focus olunca veya dokununca gorunsun */
.profile-reference-cover:hover .profile-reference-cover-change,
.profile-reference-cover:focus-within .profile-reference-cover-change,
.profile-reference-cover:active .profile-reference-cover-change,
.profile-reference-avatar-shell:hover .profile-reference-avatar-change,
.profile-reference-avatar-shell:focus-within .profile-reference-avatar-change,
.profile-reference-avatar-shell:active .profile-reference-avatar-change {
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
}

@media (max-width: 640px) {
    .profile-reference-cover-change {
        display: inline-flex !important;
        position: absolute !important;
        left: 50% !important;
        top: 50% !important;
        z-index: 50 !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        min-height: 34px !important;
        padding: 0 13px !important;
        border-radius: 999px !important;
        background: rgba(17, 24, 39, 0.78) !important;
        color: #ffffff !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        line-height: 1 !important;
        text-decoration: none !important;
        transform: translate(-50%, -50%) !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
    }

    .profile-reference-avatar-shell {
        position: relative !important;
        display: inline-flex !important;
        overflow: visible !important;
        border-radius: 999px !important;
    }

    .profile-reference-avatar-change {
        display: inline-flex !important;
        position: absolute !important;
        inset: 0 !important;
        z-index: 60 !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 5px !important;
        width: 100% !important;
        height: 100% !important;
        border-radius: 999px !important;
        background: rgba(17, 24, 39, 0.36) !important;
        color: #111827 !important;
        font-size: 11px !important;
        font-weight: 500 !important;
        line-height: 1.1 !important;
        text-align: center !important;
        text-decoration: none !important;
        backdrop-filter: blur(2px) !important;
        -webkit-backdrop-filter: blur(2px) !important;
    }

    .profile-reference-avatar-change svg {
        width: 23px !important;
        height: 23px !important;
        display: block !important;
        color: currentColor !important;
    }

    .profile-reference-avatar-change span {
        display: block !important;
        max-width: 72px !important;
        color: currentColor !important;
        font-size: 11px !important;
        font-weight: 500 !important;
        line-height: 1.1 !important;
        white-space: normal !important;
    }

    .profile-reference-actions-wrapper {
        right: 16px !important;
        z-index: 90 !important;
    }

    .profile-reference-actions-block,
    .profile-reference-actions-inline {
        gap: 7px !important;
    }

    .profile-reference-icon-btn,
    .profile-reference-menu-summary,
    .profile-reference-account-badge {
        width: 36px !important;
        height: 36px !important;
    }

    .profile-reference-btn-primary {
        height: 36px !important;
        padding: 0 16px !important;
        border-radius: 12px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
    }
}

html.dark .profile-reference-icon-btn,
.dark .profile-reference-icon-btn,
html.dark .profile-reference-menu-summary,
.dark .profile-reference-menu-summary,
html.dark .profile-reference-account-badge,
.dark .profile-reference-account-badge {
    background: transparent !important;
    color: #f8fafc !important;
}

html.dark .profile-reference-icon-btn:hover,
.dark .profile-reference-icon-btn:hover,
html.dark .profile-reference-icon-btn:focus,
.dark .profile-reference-icon-btn:focus,
html.dark .profile-reference-icon-btn:active,
.dark .profile-reference-icon-btn:active,
html.dark .profile-reference-menu-summary:hover,
.dark .profile-reference-menu-summary:hover,
html.dark .profile-reference-menu-summary:focus,
.dark .profile-reference-menu-summary:focus,
html.dark .profile-reference-menu-summary:active,
.dark .profile-reference-menu-summary:active,
html.dark .profile-reference-more[open] > .profile-reference-menu-summary,
.dark .profile-reference-more[open] > .profile-reference-menu-summary {
    background: rgba(148, 163, 184, 0.16) !important;
    color: #f8fafc !important;
}

html.dark .profile-reference-more-panel,
.dark .profile-reference-more-panel,
html.dark .profile-reference-menu-panel,
.dark .profile-reference-menu-panel {
    background: #111827 !important;
    border-color: #1f2937 !important;
    color: #f8fafc !important;
}

html.dark .profile-reference-menu-item,
.dark .profile-reference-menu-item {
    color: #f8fafc !important;
}

html.dark .profile-reference-menu-item:hover,
.dark .profile-reference-menu-item:hover,
html.dark .profile-reference-menu-item:focus,
.dark .profile-reference-menu-item:focus,
html.dark .profile-reference-menu-item:active,
.dark .profile-reference-menu-item:active {
    background: rgba(148, 163, 184, 0.14) !important;
    color: #f8fafc !important;
}



/* ========================================================================
   FINAL: KATIL BUTONUNUN SAGINDA UC NOKTA AYARLAR MENUSU
   ======================================================================== */
.profile-reference-actions-inline {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 8px !important;
    flex-wrap: nowrap !important;
}

.profile-reference-actions-inline form {
    margin: 0 !important;
    display: inline-flex !important;
}

.profile-reference-more {
    position: relative !important;
    display: inline-flex !important;
    flex: 0 0 auto !important;
}

.profile-reference-more > summary {
    list-style: none !important;
}

.profile-reference-more > summary::-webkit-details-marker {
    display: none !important;
}

.profile-reference-menu-summary {
    display: inline-flex !important;
    width: 38px !important;
    height: 38px !important;
    align-items: center !important;
    justify-content: center !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: #f3f4f6 !important;
    color: #111827 !important;
    padding: 0 !important;
    cursor: pointer !important;
    box-shadow: none !important;
    -webkit-tap-highlight-color: transparent !important;
}

.profile-reference-menu-summary svg {
    width: 19px !important;
    height: 19px !important;
    display: block !important;
}

.profile-reference-menu-summary:hover,
.profile-reference-menu-summary:focus,
.profile-reference-menu-summary:active,
.profile-reference-more[open] > .profile-reference-menu-summary {
    background: #e5e7eb !important;
    color: #111827 !important;
    outline: none !important;
}

.profile-reference-more-panel {
    position: absolute !important;
    top: calc(100% + 8px) !important;
    right: 0 !important;
    z-index: 120 !important;
    display: none !important;
    min-width: 168px !important;
    padding: 8px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 14px !important;
    background: #ffffff !important;
    color: #111827 !important;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.16) !important;
}

.profile-reference-more[open] > .profile-reference-more-panel {
    display: block !important;
}

.profile-reference-menu-item {
    display: flex !important;
    width: 100% !important;
    min-height: 38px !important;
    align-items: center !important;
    gap: 10px !important;
    border: 0 !important;
    border-radius: 10px !important;
    background: transparent !important;
    color: #111827 !important;
    padding: 0 10px !important;
    font-family: "Poppins", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1 !important;
    text-align: left !important;
    text-decoration: none !important;
    cursor: pointer !important;
}

.profile-reference-menu-item:hover,
.profile-reference-menu-item:focus,
.profile-reference-menu-item:active {
    background: #f3f4f6 !important;
    color: #111827 !important;
    outline: none !important;
}

.profile-reference-menu-item svg {
    width: 16px !important;
    height: 16px !important;
    flex: 0 0 auto !important;
    color: currentColor !important;
}

@media (max-width: 640px) {
    .profile-reference-actions-inline {
        gap: 8px !important;
    }

    .profile-reference-menu-summary {
        width: 38px !important;
        height: 38px !important;
    }

    .profile-reference-more-panel {
        right: 0 !important;
        min-width: 164px !important;
    }
}

html.dark .profile-reference-menu-summary,
.dark .profile-reference-menu-summary {
    background: rgba(148, 163, 184, 0.16) !important;
    color: #f8fafc !important;
}

html.dark .profile-reference-menu-summary:hover,
html.dark .profile-reference-menu-summary:focus,
html.dark .profile-reference-menu-summary:active,
html.dark .profile-reference-more[open] > .profile-reference-menu-summary,
.dark .profile-reference-menu-summary:hover,
.dark .profile-reference-menu-summary:focus,
.dark .profile-reference-menu-summary:active,
.dark .profile-reference-more[open] > .profile-reference-menu-summary {
    background: rgba(148, 163, 184, 0.24) !important;
    color: #f8fafc !important;
}

html.dark .profile-reference-more-panel,
.dark .profile-reference-more-panel {
    background: #111827 !important;
    border-color: #1f2937 !important;
    color: #f8fafc !important;
}

html.dark .profile-reference-menu-item,
.dark .profile-reference-menu-item {
    color: #f8fafc !important;
}

html.dark .profile-reference-menu-item:hover,
html.dark .profile-reference-menu-item:focus,
html.dark .profile-reference-menu-item:active,
.dark .profile-reference-menu-item:hover,
.dark .profile-reference-menu-item:focus,
.dark .profile-reference-menu-item:active {
    background: rgba(148, 163, 184, 0.14) !important;
    color: #f8fafc !important;
}



/* ========================================================================
   FINAL FIX: UC NOKTA MENUSU KATIL BUTONUNUN SAGINDA ZORLA GORUNSUN
   Onceki body.route-category .profile-reference-menu-summary { display:none }
   kuralini daha guclu selector ile ezer.
   ======================================================================== */
body.route-category .profile-reference-actions-wrapper .profile-reference-more,
body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-more,
body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-more,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-more {
    position: relative !important;
    display: inline-flex !important;
    flex: 0 0 auto !important;
    visibility: visible !important;
    opacity: 1 !important;
}

body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary,
body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary,
body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary {
    display: inline-flex !important;
    width: 38px !important;
    height: 38px !important;
    min-width: 38px !important;
    min-height: 38px !important;
    align-items: center !important;
    justify-content: center !important;
    border: 0 !important;
    border-radius: 999px !important;
    background: #f3f4f6 !important;
    color: #111827 !important;
    padding: 0 !important;
    margin: 0 !important;
    cursor: pointer !important;
    box-shadow: none !important;
    visibility: visible !important;
    opacity: 1 !important;
    pointer-events: auto !important;
    -webkit-tap-highlight-color: transparent !important;
}

body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary svg,
body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary svg,
body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary svg,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary svg {
    display: block !important;
    width: 19px !important;
    height: 19px !important;
    color: currentColor !important;
}

body.route-category .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:focus,
body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:focus,
body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary:focus,
body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary:focus,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary:active {
    background: #e5e7eb !important;
    color: #111827 !important;
    outline: none !important;
}

body.route-category .profile-reference-actions-wrapper .profile-reference-more-panel,
body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-more-panel,
body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-more-panel,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-more-panel {
    position: absolute !important;
    top: calc(100% + 8px) !important;
    right: 0 !important;
    z-index: 9999 !important;
    display: none !important;
    min-width: 168px !important;
    padding: 8px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 14px !important;
    background: #ffffff !important;
    color: #111827 !important;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.16) !important;
    visibility: visible !important;
    opacity: 1 !important;
}

body.route-category .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-more-panel,
body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-more-panel,
body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-more-panel,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-more-panel {
    display: block !important;
}

body.route-category .profile-reference-actions-wrapper .profile-reference-actions-inline,
body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-actions-inline,
body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-actions-inline,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-actions-inline {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 8px !important;
    flex-wrap: nowrap !important;
}

html.dark body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary,
.dark body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary,
html.dark body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary,
.dark body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary,
html.dark body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary,
.dark body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary,
html.dark .profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary,
.dark .profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary {
    background: rgba(148, 163, 184, 0.16) !important;
    color: #f8fafc !important;
}

html.dark body.route-category .profile-reference-actions-wrapper .profile-reference-more-panel,
.dark body.route-category .profile-reference-actions-wrapper .profile-reference-more-panel,
html.dark body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-more-panel,
.dark body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-more-panel,
html.dark body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-more-panel,
.dark body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-more-panel,
html.dark .profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-more-panel,
.dark .profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-more-panel {
    background: #111827 !important;
    border-color: #1f2937 !important;
    color: #f8fafc !important;
}



/* ========================================================================
   FINAL FIX: UC NOKTA ARKA PLANI SADECE TIKLANINCA GRI OLSUN
   ======================================================================== */
body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary,
body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary,
body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary {
    background: transparent !important;
    color: #111827 !important;
}

body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary:hover {
    background: transparent !important;
    color: #111827 !important;
}

body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
body.route-category .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
.profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary {
    background: #f3f4f6 !important;
    color: #111827 !important;
}

html.dark body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary,
.dark body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary,
html.dark body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary,
.dark body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary,
html.dark body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary,
.dark body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary,
html.dark .profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary,
.dark .profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary {
    background: transparent !important;
    color: #f8fafc !important;
}

html.dark body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
.dark body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
html.dark body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
.dark body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
html.dark body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
.dark body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
html.dark .profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
.dark .profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary:hover {
    background: transparent !important;
    color: #f8fafc !important;
}

html.dark body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
html.dark body.route-category .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
.dark body.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
.dark body.route-category .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
html.dark body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
html.dark body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
.dark body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
.dark body.alma-app.route-category .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
html.dark body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
html.dark body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
.dark body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
.dark body:has(.category-reference-card) .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
html.dark .profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
html.dark .profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
.dark .profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
.dark .profile-reference-page.category-reference-page .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary {
    background: rgba(148, 163, 184, 0.16) !important;
    color: #f8fafc !important;
}



/* ========================================================================
   FINAL: AYARLAR VE 3 NOKTA SADECE KATEGORI SAHIBINE GORUNUR
   Markup zaten auth kontrolu ve $canManage ile sarildi. Bu blok sadece menu stilini korur.
   ======================================================================== */
.profile-reference-actions-wrapper .profile-reference-more {
    position: relative !important;
    display: inline-flex !important;
    flex: 0 0 auto !important;
}

.profile-reference-actions-wrapper .profile-reference-menu-summary {
    background: transparent !important;
}

.profile-reference-actions-wrapper .profile-reference-menu-summary:hover {
    background: transparent !important;
}

.profile-reference-actions-wrapper .profile-reference-menu-summary:active,
.profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary {
    background: #f3f4f6 !important;
}

html.dark .profile-reference-actions-wrapper .profile-reference-menu-summary {
    background: transparent !important;
}

html.dark .profile-reference-actions-wrapper .profile-reference-menu-summary:hover,
.dark .profile-reference-actions-wrapper .profile-reference-menu-summary:hover {
    background: transparent !important;
}

html.dark .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
html.dark .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary,
.dark .profile-reference-actions-wrapper .profile-reference-menu-summary:active,
.dark .profile-reference-actions-wrapper .profile-reference-more[open] > .profile-reference-menu-summary {
    background: rgba(148, 163, 184, 0.16) !important;
}



/* ========================================================================
   FINAL: 3 NOKTA ICONU SADECE GIRIS YAPMIS KATEGORI SAHIBINDE GORUNUR
   HTML tarafinda auth kontrolu ve $canManage ile sarildi.
   ======================================================================== */
.profile-reference-actions-wrapper .profile-reference-more {
    position: relative !important;
    display: inline-flex !important;
}

</style>
    <script>
        (() => {
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
                if (!button) {
                    return;
                }

                const original = button.dataset.originalLabel || button.textContent.trim();
                button.dataset.originalLabel = original;
                button.textContent = label;

                window.clearTimeout(button.__labelTimer);
                button.__labelTimer = window.setTimeout(() => {
                    button.textContent = original;
                }, 1400);
            };

            shareButtons.forEach((button) => {
                button.addEventListener('click', async () => {
                    const url = button.getAttribute('data-share-url') || window.location.href;
                    const title = button.getAttribute('data-share-title') || document.title;

                    try {
                        if (navigator.share) {
                            await navigator.share({ title, url });
                            flashButtonLabel(button, '{{ app()->getLocale() === 'tr' ? 'Paylasildi' : 'Shared' }}');
                            return;
                        }

                        await copyText(url);
                        flashButtonLabel(button, '{{ app()->getLocale() === 'tr' ? 'Kopyalandi' : 'Copied' }}');
                    } catch (error) {
                        if (error?.name === 'AbortError') {
                            return;
                        }
                    }
                });
            });

            const bindSheet = (openSelector, sheetId, panelSelector, closeSelector, mobileOnly = false) => {
                const sheet = document.getElementById(sheetId);
                const panel = sheet?.querySelector(panelSelector);
                const openButton = document.querySelector(openSelector);
                const closeButtons = sheet?.querySelectorAll(closeSelector);
                let hideTimer = null;

                const syncScrollLock = () => {
                    const hasOpenSheet = Array.from(document.querySelectorAll('[aria-hidden="false"]')).some((item) => item.id === 'profile-avatar-sheet' || item.id === 'profile-actions-sheet');
                    document.documentElement.classList.toggle('overflow-hidden', hasOpenSheet);
                    document.body.classList.toggle('overflow-hidden', hasOpenSheet);
                };

                const showSheet = () => {
                    if (!sheet || !panel) {
                        return;
                    }

                    if (mobileOnly && window.innerWidth >= 640) {
                        return;
                    }

                    if (hideTimer) {
                        clearTimeout(hideTimer);
                    }

                    sheet.classList.remove('pointer-events-none');
                    sheet.setAttribute('aria-hidden', 'false');
                    syncScrollLock();

                    requestAnimationFrame(() => {
                        panel.classList.remove('translate-y-full');
                    });
                };

                const hideSheet = () => {
                    if (!sheet || !panel || sheet.getAttribute('aria-hidden') !== 'false') {
                        return;
                    }

                    panel.classList.add('translate-y-full');
                    sheet.setAttribute('aria-hidden', 'true');

                    if (hideTimer) {
                        clearTimeout(hideTimer);
                    }

                    hideTimer = window.setTimeout(() => {
                        sheet.classList.add('pointer-events-none');
                        syncScrollLock();
                    }, 280);
                };

                openButton?.addEventListener('click', showSheet);
                closeButtons?.forEach((button) => button.addEventListener('click', hideSheet));

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        hideSheet();
                    }
                });
            };

            bindSheet('[data-profile-avatar-open]', 'profile-avatar-sheet', '[data-profile-avatar-panel]', '[data-profile-avatar-close]', true);
            bindSheet('[data-profile-actions-open]', 'profile-actions-sheet', '[data-profile-actions-panel]', '[data-profile-actions-close]');
        })();
    </script>
    @endpush
@endif


@push('scripts')
<script>
    document.addEventListener('click', function (event) {
        document.querySelectorAll('.profile-reference-more[open]').forEach(function (details) {
            if (!details.contains(event.target)) {
                details.removeAttribute('open');
            }
        });
    });
</script>
@endpush


@push('scripts')
<style>
/* ========================================================================
   KESIN COZUM: MOBILDE HEADER FIXED
   Sticky degil, direkt fixed kullanir. Parent overflow/transform olsa bile calisir.
   ======================================================================== */
@media (max-width: 768px) {
    :root {
        --ografi-fixed-mobile-header-height: 64px;
    }

    html {
        scroll-padding-top: var(--ografi-fixed-mobile-header-height) !important;
    }

    body.ografi-mobile-header-fixed-ready {
        padding-top: var(--ografi-fixed-mobile-header-height) !important;
    }

    body.ografi-mobile-header-fixed-ready > header,
    body.ografi-mobile-header-fixed-ready header,
    body.ografi-mobile-header-fixed-ready .app-header,
    body.ografi-mobile-header-fixed-ready .site-header,
    body.ografi-mobile-header-fixed-ready .main-header,
    body.ografi-mobile-header-fixed-ready .top-header,
    body.ografi-mobile-header-fixed-ready .alma-header,
    body.ografi-mobile-header-fixed-ready .navbar,
    body.ografi-mobile-header-fixed-ready .navigation,
    body.ografi-mobile-header-fixed-ready [data-app-header],
    body.ografi-mobile-header-fixed-ready [data-header] {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 2147483000 !important;
        width: 100% !important;
        max-width: 100% !important;
        background: rgba(255, 255, 255, 0.98) !important;
        border-bottom: 1px solid rgba(229, 231, 235, 0.95) !important;
        box-shadow: none !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        transform: none !important;
        translate: none !important;
        will-change: auto !important;
    }

    html.dark body.ografi-mobile-header-fixed-ready > header,
    html.dark body.ografi-mobile-header-fixed-ready header,
    html.dark body.ografi-mobile-header-fixed-ready .app-header,
    html.dark body.ografi-mobile-header-fixed-ready .site-header,
    html.dark body.ografi-mobile-header-fixed-ready .main-header,
    html.dark body.ografi-mobile-header-fixed-ready .top-header,
    html.dark body.ografi-mobile-header-fixed-ready .alma-header,
    html.dark body.ografi-mobile-header-fixed-ready .navbar,
    html.dark body.ografi-mobile-header-fixed-ready .navigation,
    html.dark body.ografi-mobile-header-fixed-ready [data-app-header],
    html.dark body.ografi-mobile-header-fixed-ready [data-header],
    body.dark.ografi-mobile-header-fixed-ready > header,
    body.dark.ografi-mobile-header-fixed-ready header,
    body.dark.ografi-mobile-header-fixed-ready .app-header,
    body.dark.ografi-mobile-header-fixed-ready .site-header,
    body.dark.ografi-mobile-header-fixed-ready .main-header,
    body.dark.ografi-mobile-header-fixed-ready .top-header,
    body.dark.ografi-mobile-header-fixed-ready .alma-header,
    body.dark.ografi-mobile-header-fixed-ready .navbar,
    body.dark.ografi-mobile-header-fixed-ready .navigation,
    body.dark.ografi-mobile-header-fixed-ready [data-app-header],
    body.dark.ografi-mobile-header-fixed-ready [data-header],
    .dark body.ografi-mobile-header-fixed-ready > header,
    .dark body.ografi-mobile-header-fixed-ready header,
    .dark body.ografi-mobile-header-fixed-ready .app-header,
    .dark body.ografi-mobile-header-fixed-ready .site-header,
    .dark body.ografi-mobile-header-fixed-ready .main-header,
    .dark body.ografi-mobile-header-fixed-ready .top-header,
    .dark body.ografi-mobile-header-fixed-ready .alma-header,
    .dark body.ografi-mobile-header-fixed-ready .navbar,
    .dark body.ografi-mobile-header-fixed-ready .navigation,
    .dark body.ografi-mobile-header-fixed-ready [data-app-header],
    .dark body.ografi-mobile-header-fixed-ready [data-header] {
        background: rgba(17, 24, 39, 0.98) !important;
        border-bottom-color: rgba(31, 41, 55, 0.95) !important;
    }
}
</style>

<script>
    (function () {
        const selectors = [
            'body > header',
            '[data-app-header]',
            '[data-header]',
            '.app-header',
            '.site-header',
            '.main-header',
            '.top-header',
            '.alma-header',
            '.navbar',
            '.navigation',
            'header'
        ];

        const isMobile = () => window.matchMedia('(max-width: 768px)').matches;

        const getHeader = () => {
            for (const selector of selectors) {
                const element = document.querySelector(selector);
                if (element && element.offsetParent !== null) {
                    return element;
                }
            }

            return null;
        };

        const clearFixedHeader = (header) => {
            document.body.classList.remove('ografi-mobile-header-fixed-ready');
            document.documentElement.style.removeProperty('--ografi-fixed-mobile-header-height');
            document.body.style.removeProperty('padding-top');

            if (!header) {
                return;
            }

            header.style.removeProperty('position');
            header.style.removeProperty('top');
            header.style.removeProperty('left');
            header.style.removeProperty('right');
            header.style.removeProperty('z-index');
            header.style.removeProperty('width');
            header.style.removeProperty('max-width');
            header.style.removeProperty('transform');
            header.style.removeProperty('translate');
        };

        const applyFixedHeader = () => {
            const header = getHeader();

            if (!isMobile()) {
                clearFixedHeader(header);
                return;
            }

            if (!header) {
                return;
            }

            const headerHeight = Math.max(Math.ceil(header.getBoundingClientRect().height || 0), 56);
            const heightValue = headerHeight + 'px';

            document.documentElement.style.setProperty('--ografi-fixed-mobile-header-height', heightValue);
            document.body.classList.add('ografi-mobile-header-fixed-ready');
            document.body.style.setProperty('padding-top', heightValue, 'important');

            header.style.setProperty('position', 'fixed', 'important');
            header.style.setProperty('top', '0', 'important');
            header.style.setProperty('left', '0', 'important');
            header.style.setProperty('right', '0', 'important');
            header.style.setProperty('z-index', '2147483000', 'important');
            header.style.setProperty('width', '100%', 'important');
            header.style.setProperty('max-width', '100%', 'important');
            header.style.setProperty('transform', 'none', 'important');
            header.style.setProperty('translate', 'none', 'important');
        };

        const run = () => {
            applyFixedHeader();
            window.setTimeout(applyFixedHeader, 80);
            window.setTimeout(applyFixedHeader, 300);
            window.setTimeout(applyFixedHeader, 900);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', run);
        } else {
            run();
        }

        window.addEventListener('resize', run, { passive: true });
        window.addEventListener('orientationchange', run, { passive: true });
        window.addEventListener('pageshow', run, { passive: true });
    })();
</script>
@endpush

