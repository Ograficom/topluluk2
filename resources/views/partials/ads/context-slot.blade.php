@php
    $slotKey = (string) ($slotKey ?? $key ?? '');
    $hideAdSlot = request()->routeIs(
        'blog.post',
        'contact.*',
        'search',
        'pages.sss',
        'pages.show.short'
    ) || request()->is('p', 'p/*', 'contact', 'search');
@endphp

@if(!$hideAdSlot && $slotKey !== '')
    @include('partials.ads.slot', [
        'slotKey' => $slotKey,
        'device' => $device ?? 'all',
        'wrapperClass' => $wrapperClass ?? '',
    ])
@endif
