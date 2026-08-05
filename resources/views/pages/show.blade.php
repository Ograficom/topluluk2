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

@section('content')
    <div class="space-y-4">
        <section class="space-y-4">
            @include('partials.page-title-identity', ['title' => $page->title])
            <div class="alma-panel p-5 sm:p-6">
            <div class="prose prose-slate max-w-none dark:prose-invert">
                {!! $page->content !!}
            </div>
            </div>
        </section>
    </div>
@endsection



