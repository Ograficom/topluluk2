@php
    $slotKey = (string) ($slotKey ?? $key ?? '');
    $hideAdSlot = request()->routeIs(
        'blog.post',
        'contact.*',
        'search',
        'pages.sss',
        'pages.show.short'
    ) || request()->is('p', 'p/*', 'contact', 'search');

    // Akis sayfalarinda (feed_top + her 3 postta bir reklam sistemi olan
    // sayfalarda) "icerik oncesi" kutusu, sayfanin kendi ust reklam kutusuyla
    // ust uste biniyor ve orantisiz gorunuyordu - bu sayfalarda tek kutu
    // (feed_top) yeterli, ikinci bir "once" kutusuna gerek yok.
    $isFeedPage = request()->routeIs(
        'home',
        'blog.index',
        'blog.posts',
        'blog.category',
        'blog.categories',
        'blog.popular',
        'blog.bookmarks',
        'discover',
        'users.show'
    );

    if ($slotKey === 'ads_main_before_content' && $isFeedPage) {
        $hideAdSlot = true;
    }
@endphp

@if(!$hideAdSlot && $slotKey !== '')
    @include('partials.ads.slot', [
        'slotKey' => $slotKey,
        'device' => $device ?? 'all',
        'wrapperClass' => $wrapperClass ?? '',
    ])
@endif
