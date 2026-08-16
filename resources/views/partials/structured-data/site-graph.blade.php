@php
    $appUrl = rtrim(url('/'), '/');
    $siteName = trim((string) config('app.name', 'ografi'));
    $organizationId = $appUrl . '/#organization';
    $websiteId = $appUrl . '/#website';
    $logoUrl = asset('images/ografi-logo.png') . '?v=20260714a';
    $locale = str_replace('_', '-', app()->getLocale());

    $organization = [
        '@type' => 'Organization',
        '@id' => $organizationId,
        'name' => $siteName,
        'alternateName' => 'Ografi.com',
        'legalName' => 'Eabodur Medya Prodüksiyon Limited Şirketi',
        'url' => $appUrl,
        'description' => 'Ografi, kullanıcıların haber, makale ve topluluk içerikleri yayımlamasına, keşfetmesine ve bu içeriklerle etkileşim kurmasına olanak tanıyan dijital içerik ve topluluk platformudur.',
        'foundingDate' => '2025-12-25',
        'founder' => [
            '@type' => 'Person',
            'name' => 'Enes Bodur',
        ],
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $logoUrl,
            'name' => $siteName . ' logo',
            'caption' => $siteName . ' logosu',
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
        'https://www.youtube.com/@ograficom',
        'https://www.instagram.com/ograficom',
        'https://github.com/Ograficom',
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

    /*
        SiteNavigationElement: Google'a sitenin ana gezinme yapisini bildirir.
        Bu, arama sonuclarindaki "sitelinks" (ana sonucun altinda cikan alt
        baglantilar) icin bir SINYALDIR - Google bu baglantilari tamamen kendi
        algoritmasiyla secer, hicbir site sahibi bunlari elle belirleyemez.
        Duzgun bir SiteNavigationElement + acik site yapisi, zaman icinde
        Google'in daha anlamli sitelinks secmesine yardimci olabilir.
    */
    $navigationLinks = collect([
        ['name' => 'Ana Sayfa', 'route' => 'home'],
        ['name' => 'Kesfet', 'route' => 'discover'],
        ['name' => 'Populer Yazilar', 'route' => 'blog.popular'],
        ['name' => 'Uye Ol', 'route' => 'register'],
        ['name' => 'SSS', 'route' => 'pages.sss'],
    ])
        ->filter(fn ($link) => app('router')->has($link['route']))
        ->map(fn ($link) => [
            '@type' => 'SiteNavigationElement',
            'name' => $link['name'],
            'url' => route($link['route']),
        ])
        ->values()
        ->all();

    $graph = [
        '@context' => 'https://schema.org',
        '@graph' => array_merge(
            [$organization, $website],
            $navigationLinks
        ),
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) !!}
</script>
