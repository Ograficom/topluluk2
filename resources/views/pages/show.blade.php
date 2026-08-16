@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    $siteName = trim((string) config('app.name', 'Ografi'));
    $pageSmartSeo = $page->seo;
    $pageSeoTitle = trim((string) (($pageSmartSeo?->title) ?: $page->meta_title ?: $page->title ?: $siteName));
    $pageDescriptionSource = trim((string) (($pageSmartSeo?->description) ?: $page->meta_description ?: strip_tags((string) $page->content)));
    $pageDescriptionSource = html_entity_decode($pageDescriptionSource, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $pageDescriptionSource = preg_replace('/\s+/u', ' ', $pageDescriptionSource) ?? $pageDescriptionSource;
    $pageDescription = $pageDescriptionSource !== ''
        ? Str::limit(trim($pageDescriptionSource), 155)
        : ($siteName !== '' ? $page->title . ' - ' . $siteName : $page->title);
    $pageCanonicalUrl = route('pages.show', $page->slug);
    $pageOgImage = $page->ogImageUrl();
    $pageRobotsDirective = $page->noindex ? 'noindex, follow' : 'index, follow';
@endphp

@section('title', $pageSeoTitle)
@section('meta_description', $pageDescription)
@section('canonical_url', $pageCanonicalUrl)
@section('has_custom_seo', '1')

@push('seo')
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ e($siteName !== '' ? $siteName : 'Ografi') }}">
<meta property="og:title" content="{{ e($pageSeoTitle) }}">
<meta property="og:description" content="{{ e($pageDescription) }}">
<meta property="og:url" content="{{ e($pageCanonicalUrl) }}">
@if($pageOgImage)
<meta property="og:image" content="{{ e($pageOgImage) }}">
<meta property="og:image:secure_url" content="{{ e($pageOgImage) }}">
<meta property="og:image:alt" content="{{ e($pageSeoTitle) }}">
@endif
<meta name="twitter:card" content="{{ $pageOgImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ e($pageSeoTitle) }}">
<meta name="twitter:description" content="{{ e($pageDescription) }}">
@if($pageOgImage)
<meta name="twitter:image" content="{{ e($pageOgImage) }}">
@endif
<meta name="robots" content="{{ $pageRobotsDirective }}">
@if($page->meta_keywords)
<meta name="keywords" content="{{ e($page->meta_keywords) }}">
@endif
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => $pageSeoTitle,
    'description' => $pageDescription,
    'url' => $pageCanonicalUrl,
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => $siteName !== '' ? $siteName : 'Ografi',
        'url' => url('/'),
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

<style>
    /* Admin panelinden olusturulan tum sayfalarda Etiketler ust bolgesi. */
    body.alma-app.route-page .static-page-title.static-page-title {
        display: flex !important;
        box-sizing: border-box !important;
        width: 100% !important;
        min-height: 40px !important;
        align-items: center !important;
        gap: 0 !important;
        padding: 2px 12px !important;
        overflow: visible !important;
        border: 1px solid #e2e5ea !important;
        border-radius: 999px !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        background-image: none !important;
        box-shadow: none !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        filter: none !important;
        opacity: 1 !important;
    }

    body.alma-app.route-page .static-page-title > :not(.page-title-identity__edge-blur) {
        position: relative;
        z-index: 1;
    }

    body.alma-app.route-page .static-page-title .page-title-identity__nav {
        position: relative;
        display: inline-flex !important;
        flex: 0 0 auto !important;
        width: 34px !important;
        height: 30px !important;
        align-items: center !important;
        justify-content: flex-start !important;
        margin: 0 10px 0 0 !important;
        padding: 0 10px 0 2px !important;
        border: 0 !important;
        border-right: 1px solid #e5e7eb !important;
        border-radius: 0 !important;
        background: #ffffff !important;
        color: #111827 !important;
        transform: none !important;
        transition: color .15s ease !important;
    }

    body.alma-app.route-page .static-page-title .page-title-identity__nav:is(:hover, :focus-visible, :active) {
        background: #ffffff !important;
        color: #111827 !important;
        transform: none !important;
        outline: none !important;
    }

    body.alma-app.route-page .static-page-title .page-title-identity__nav svg {
        width: 17px !important;
        height: 17px !important;
        transform: none !important;
    }

    body.alma-app.route-page .static-page-title .page-title-identity__divider {
        display: none !important;
    }

    body.alma-app.route-page .static-page-title .page-title-identity__text {
        min-width: 0;
        margin: 0 !important;
        overflow: hidden;
        color: #111111 !important;
        font-size: 18px !important;
        font-weight: 500 !important;
        line-height: 1.2 !important;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Genel h1 kurali :not(#...) ile ID seviyesinde ozgulluk tasiyor. */
    html body.alma-app:not(#comments):not(#app).route-page .static-page-title .page-title-identity__text {
        font-size: 18px !important;
        font-weight: 500 !important;
        line-height: 1.2 !important;
    }

    body.alma-app.route-page .static-page-content.static-page-content {
        box-sizing: border-box !important;
        width: 100% !important;
        border: 1px solid rgba(226, 232, 240, .9) !important;
        border-radius: 14px !important;
        background: #ffffff !important;
        box-shadow: none !important;
        transition: filter .22s ease-out;
        will-change: filter;
    }

    html body.alma-app.route-page .static-page-content .prose :is(h1, h2, h3, h4, h5, h6) {
        margin-top: 1.5em !important;
        margin-bottom: .65em !important;
    }

    html body.alma-app.route-page .static-page-content .prose > :first-child {
        margin-top: 0 !important;
    }

    @media (max-width: 640px) {
        body.alma-app.route-page .site-header {
            will-change: transform;
        }

        body.alma-app.route-page .static-page-title.static-page-title {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 45;
            width: auto !important;
            margin: 0 !important;
        }

        body.alma-app.route-page [data-identity-spacer] {
            display: block;
            margin-top: 0 !important;
            background: transparent;
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
        }

        body.alma-app.route-page .static-page-title .page-title-identity__edge-blur {
            position: absolute;
            right: 0;
            left: 0;
            z-index: 0;
            display: block !important;
            height: 16px !important;
            background: rgba(246, 244, 240, .68) !important;
            backdrop-filter: blur(12px) saturate(115%);
            -webkit-backdrop-filter: blur(12px) saturate(115%);
            pointer-events: none;
            transition: backdrop-filter .22s ease-out, -webkit-backdrop-filter .22s ease-out;
        }

        body.alma-app.route-page .static-page-title .page-title-identity__edge-blur--top {
            bottom: 100%;
        }

        body.alma-app.route-page .static-page-title .page-title-identity__edge-blur--bottom {
            top: 100%;
        }
    }

    @media (min-width: 641px) {
        body.alma-app.route-page .static-page-title.static-page-title {
            position: sticky;
            top: 64px;
            z-index: 30;
        }

        body.alma-app.route-page .static-page-title.static-page-title::before {
            content: '';
            position: absolute;
            right: 0;
            bottom: 100%;
            left: 0;
            z-index: 0;
            width: 100%;
            height: 16px;
            background: rgba(246, 244, 240, .64);
            backdrop-filter: blur(12px) saturate(115%);
            -webkit-backdrop-filter: blur(12px) saturate(115%);
            pointer-events: none;
        }

        body.alma-app.route-page [data-identity-spacer] {
            display: none !important;
            height: 0 !important;
        }
    }

    html.dark body.alma-app.route-page .static-page-title.static-page-title,
    html.dark body.alma-app.route-page .static-page-title .page-title-identity__nav,
    .dark body.alma-app.route-page .static-page-title.static-page-title,
    .dark body.alma-app.route-page .static-page-title .page-title-identity__nav {
        border-color: #27272a !important;
        background: #18181b !important;
        background-color: #18181b !important;
        color: #f4f4f5 !important;
    }

    html.dark body.alma-app.route-page .static-page-title .page-title-identity__text,
    .dark body.alma-app.route-page .static-page-title .page-title-identity__text {
        color: #f4f4f5 !important;
    }

    html.dark body.alma-app.route-page .static-page-content.static-page-content,
    .dark body.alma-app.route-page .static-page-content.static-page-content {
        border-color: #27272a !important;
        background: #18181b !important;
    }

    @media (max-width: 640px) {
        html.dark body.alma-app.route-page .static-page-title .page-title-identity__edge-blur,
        .dark body.alma-app.route-page .static-page-title .page-title-identity__edge-blur {
            background: rgba(24, 24, 27, .72) !important;
        }
    }

    @media (prefers-reduced-transparency: reduce) {
        body.alma-app.route-page .static-page-title .page-title-identity__edge-blur,
        body.alma-app.route-page [data-identity-spacer] {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
    }
</style>

@section('content')
    <div class="space-y-4">
        <section class="space-y-4">
            <div class="page-title-identity static-page-title">
                <div class="page-title-identity__edge-blur page-title-identity__edge-blur--top" aria-hidden="true"></div>
                <button type="button" class="page-title-identity__nav" data-page-title-back aria-label="Geri git" title="Geri git">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 19l-7-7l7-7m7 7H5"></path>
                    </svg>
                </button>
                <span class="page-title-identity__divider" aria-hidden="true"></span>
                <h1 class="page-title-identity__text">{{ $page->title }}</h1>
                <div class="page-title-identity__edge-blur page-title-identity__edge-blur--bottom" aria-hidden="true"></div>
            </div>
            <div data-identity-spacer aria-hidden="true"></div>

            <div class="alma-panel p-5 sm:p-6 static-page-content" data-static-page-content>
                <div class="prose prose-slate max-w-none dark:prose-invert">
                    {!! $page->content !!}
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-page-title-back]').forEach((button) => {
            button.addEventListener('click', () => {
                let sameSiteReferrer = false;
                try {
                    sameSiteReferrer = document.referrer !== '' && new URL(document.referrer).host === window.location.host;
                } catch (error) {
                    sameSiteReferrer = false;
                }

                if (sameSiteReferrer && window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = @json(route('home'));
                }
            });
        });
    });
</script>
@endpush
