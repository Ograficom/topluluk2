@php
    $preview = $preview ?? null;
    if (is_object($preview)) {
        $preview = (array) $preview;
    }
    $preview = is_array($preview) ? $preview : [];

    $url = trim((string) ($preview['url'] ?? ''));
    $host = trim((string) ($preview['host'] ?? (parse_url($url, PHP_URL_HOST) ?: '')));
    $host = preg_replace('/^www\./i', '', $host) ?: $host;
    $siteName = trim((string) ($preview['site_name'] ?? ''));
    $title = trim((string) ($preview['title'] ?? ''));
    $description = trim((string) ($preview['description'] ?? ''));
    $imageUrl = trim((string) ($preview['image_url'] ?? ''));
    $iconUrl = trim((string) ($preview['icon_url'] ?? ''));
    $sourceLabel = \Illuminate\Support\Str::upper(trim((string) ($preview['source_label'] ?? 'Source')));
    $displayTitle = $title !== '' ? $title : ($siteName !== '' ? $siteName : $host);
    $displaySiteName = $siteName !== '' ? $siteName : $host;
    $displayDescription = $description !== '' ? $description : null;
    $siteInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr((string) $displaySiteName, 0, 1));
    $previewImageAlt = trim($displayTitle . ' sayfa onizleme gorseli');
    $previewLogoAlt = trim($displaySiteName . ' logosu');
@endphp

@if($url !== '')
    <a
        href="{{ $url }}"
        target="_blank"
        rel="nofollow noopener noreferrer"
        class="alma-link-preview"
        aria-label="{{ $displayTitle !== '' ? $displayTitle : $host }}"
    >
        @if($imageUrl !== '')
            <div class="alma-link-preview__media">
                <img src="{{ $imageUrl }}" alt="{{ $previewImageAlt !== '' ? $previewImageAlt : 'Sayfa onizleme gorseli' }}" loading="lazy" class="alma-link-preview__image" />
            </div>
        @endif

        <div class="alma-link-preview__body">
            <div class="alma-link-preview__row">
                <div class="alma-link-preview__source">
                    @if($iconUrl !== '')
                        <img src="{{ $iconUrl }}" alt="{{ $previewLogoAlt !== '' ? $previewLogoAlt : 'Site logosu' }}" loading="lazy" class="alma-link-preview__logo" referrerpolicy="no-referrer" />
                    @else
                        <span class="alma-link-preview__logo alma-link-preview__logo--fallback">{{ $siteInitial !== '' ? $siteInitial : 'S' }}</span>
                    @endif

                    <div class="alma-link-preview__source-copy">
                        <span class="alma-link-preview__eyebrow">{{ $sourceLabel }}</span>
                        <span class="alma-link-preview__site">{{ $displaySiteName }}</span>
                    </div>
                </div>

                <span class="alma-link-preview__icon" aria-hidden="true">
                    <iconify-icon icon="lucide:arrow-up-right"></iconify-icon>
                </span>
            </div>

            <div class="alma-link-preview__content">
                <span class="alma-link-preview__title">{{ $displayTitle }}</span>

                @if($displayDescription)
                    <p class="alma-link-preview__description">{{ $displayDescription }}</p>
                @endif

                @if($host !== '')
                    <span class="alma-link-preview__host">{{ $host }}</span>
                @endif
            </div>
        </div>
    </a>
@endif

@once
    @push('head')
        <link rel="stylesheet" href="{{ asset('css/link-preview.css') }}">
    @endpush
@endonce
