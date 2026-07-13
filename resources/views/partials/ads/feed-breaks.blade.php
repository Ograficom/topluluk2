@php
    $iteration = (int) ($iteration ?? 0);
    $isLast = (bool) ($isLast ?? false);
@endphp

@if($iteration === 1)
    @include('partials.ads.slot', [
        'slotKey' => 'ads_mobile_inline',
        'device' => 'mobile',
        'wrapperClass' => 'mt-4',
    ])
@endif

@if($iteration > 0 && $iteration % 3 === 0)
    @include('partials.ads.slot', [
        'slotKey' => 'ads_feed_inline',
        'wrapperClass' => 'mt-4 alma-ad-slot--cover',
    ])
@endif
