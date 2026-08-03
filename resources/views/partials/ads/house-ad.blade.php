@php
    $slotKey = (string) ($slotKey ?? '');
    $shape = (string) ($shape ?? 'rectangle');
    $creativeUrl = $shape === 'leaderboard'
        ? asset('images/ad-leaderboard.svg')
        : asset('images/ad-rectangle.svg');
    $creativeUrl .= '?v=20260803b';
@endphp

<a href="{{ route('advertise.create') }}" class="house-ad">
    <img src="{{ $creativeUrl }}" alt="Bu alan senin reklamın olabilir">
</a>
