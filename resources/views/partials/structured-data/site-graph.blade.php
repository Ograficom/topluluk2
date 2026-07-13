@php
    $appUrl = rtrim(url('/'), '/');
    $siteName = trim((string) config('app.name', 'ografi'));
    $organizationId = $appUrl . '/#organization';
    $websiteId = $appUrl . '/#website';
    $logoUrl = asset('images/ografi-logo.png') . '?v=20260714';
    $locale = str_replace('_', '-', app()->getLocale());

    $organization = [
        '@type' => 'Organization',
        '@id' => $organizationId,
        'name' => $siteName,
        'url' => $appUrl,
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $logoUrl,
        ],
    ];

    $telephone = trim((string) config('seo.organization.telephone', '+905316419806'));
    if ($telephone !== '') {
        $organization['telephone'] = $telephone;
    }

    $streetAddress = trim((string) config('seo.organization.street_address', 'Saray Fatih Mahallesi Pri Reis Caddesi Elisa APT 6/A'));
    $addressLocality = trim((string) config('seo.organization.address_locality', 'Pursaklar'));
    $addressRegion = trim((string) config('seo.organization.address_region', 'Ankara'));
    $postalCode = trim((string) config('seo.organization.postal_code', '06145'));
    $addressCountry = trim((string) config('seo.organization.address_country', 'TR'));
    if ($streetAddress !== '' || $addressLocality !== '' || $addressRegion !== '' || $postalCode !== '' || $addressCountry !== '') {
        $organization['address'] = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $streetAddress !== '' ? $streetAddress : null,
            'addressLocality' => $addressLocality !== '' ? $addressLocality : null,
            'addressRegion' => $addressRegion !== '' ? $addressRegion : null,
            'postalCode' => $postalCode !== '' ? $postalCode : null,
            'addressCountry' => $addressCountry !== '' ? $addressCountry : null,
        ], fn ($value) => $value !== null);
    }

    $sameAs = collect(config('seo.organization.same_as', [
        'https://maps.app.goo.gl/WT29gjMdDGAA3ury5',
    ]))
        ->map(fn ($url) => trim((string) $url))
        ->filter()
        ->values()
        ->all();
    if (!empty($sameAs)) {
        $organization['sameAs'] = $sameAs;
    }

    $website = [
        '@type' => 'WebSite',
        '@id' => $websiteId,
        'url' => $appUrl,
        'name' => $siteName,
        'publisher' => [
            '@id' => $organizationId,
        ],
        'inLanguage' => $locale,
    ];

    if (app('router')->has('search')) {
        $website['potentialAction'] = [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => url('/search?q={search_term_string}'),
            ],
            'query-input' => 'required name=search_term_string',
        ];
    }

    $graph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            $organization,
            $website,
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) !!}
</script>
