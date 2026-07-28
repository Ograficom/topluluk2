@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    $siteName = trim((string) config('app.name', 'Ografi'));
    $pageSeoTitle = trim((string) ($page->meta_title ?: $page->title ?: $siteName));
    $pageDescriptionSource = trim((string) ($page->meta_description ?: strip_tags((string) $page->content)));
    $pageDescriptionSource = html_entity_decode($pageDescriptionSource, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $pageDescriptionSource = preg_replace('/\s+/u', ' ', $pageDescriptionSource) ?? $pageDescriptionSource;
    $pageDescription = $pageDescriptionSource !== ''
        ? Str::limit(trim($pageDescriptionSource), 155)
        : ($siteName !== '' ? $page->title . ' - ' . $siteName : $page->title);
    $pageCanonicalUrl = route('pages.show', $page->slug);
@endphp

@section('title', $pageSeoTitle)
@section('meta_description', $pageDescription)
@section('canonical_url', $pageCanonicalUrl)

@push('seo')
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ e($siteName !== '' ? $siteName : 'Ografi') }}">
<meta property="og:title" content="{{ e($pageSeoTitle) }}">
<meta property="og:description" content="{{ e($pageDescription) }}">
<meta property="og:url" content="{{ e($pageCanonicalUrl) }}">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ e($pageSeoTitle) }}">
<meta name="twitter:description" content="{{ e($pageDescription) }}">
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

@push('head')
<style>
    .page-title-identity {
        display: flex;
        align-items: center;
        width: 100%;
        min-height: 38px;
        padding: 3px 17px;
        border: 1px solid #d9dde3;
        border-radius: 18px;
        background: #ffffff;
        color: #050505;
        font-size: 14px;
        font-weight: 600;
        line-height: 1;
        box-sizing: border-box;
        box-shadow: none;
    }

    html.dark .page-title-identity,
    .dark .page-title-identity {
        border-color: #27272a;
        background: #18181b;
        color: #fafafa;
    }

    @media (max-width: 640px) {
        .page-title-identity {
            width: 100vw;
            min-height: 34px;
            margin-right: calc(50% - 50vw);
            margin-left: calc(50% - 50vw);
            padding: 2px 14px;
            border-right: 0;
            border-left: 0;
            border-radius: 16px;
            font-size: 13px;
        }
    }
</style>
@endpush

@section('content')
    <div class="space-y-4">
        <section class="space-y-4">
            <h1 class="page-title-identity">{{ $page->title }}</h1>
            <div class="alma-panel p-5 sm:p-6">
            <div class="prose prose-slate max-w-none dark:prose-invert">
                {!! $page->content !!}
            </div>
            </div>
        </section>
    </div>
@endsection



