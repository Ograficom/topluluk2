@php
    $slotKey = (string) ($slotKey ?? '');

    $sizes = [
        'ads_sidebar_top' => ['h' => 380, 'layout' => 'vertical'],
        'ads_feed_top' => ['h' => 369, 'layout' => 'wide'],
        'ads_feed_inline' => ['h' => 369, 'layout' => 'wide'],
        'ads_main_before_content' => ['h' => 220, 'layout' => 'compact'],
        'ads_main_after_content' => ['h' => 220, 'layout' => 'compact'],
        'ads_mobile_inline' => ['h' => 203, 'layout' => 'compact'],
    ];

    $size = $sizes[$slotKey] ?? ['h' => 220, 'layout' => 'compact'];
    $advertiseUrl = route('advertise.create');
    $logoUrl = asset('images/ografi-logo.png') . '?v=20260714a';
@endphp

<a
    href="{{ $advertiseUrl }}"
    class="house-ad house-ad--{{ $size['layout'] }}"
    style="min-height: {{ $size['h'] }}px;"
>
    @if ($size['layout'] === 'vertical')
        <div class="house-ad__body house-ad__body--vertical">
            <img src="{{ $logoUrl }}" alt="Ografi" class="house-ad__logo">
            <h3 class="house-ad__title">Bu alan senin<br>reklamın olabilir</h3>
            <p class="house-ad__text">Ografi kullanıcılarına bu alandan ulaş. Kurulum birkaç dakika sürer.</p>
            <span class="house-ad__cta">Reklam Ver</span>
        </div>
    @elseif ($size['layout'] === 'wide')
        <div class="house-ad__body house-ad__body--wide">
            <img src="{{ $logoUrl }}" alt="Ografi" class="house-ad__logo house-ad__logo--wide">
            <div class="house-ad__copy">
                <h3 class="house-ad__title">Bu alan senin reklamın olabilir</h3>
                <p class="house-ad__text">Ografi'de akış içinde, sağ sütunda veya sayfa üstünde - binlerce kullanıcıya ulaş.</p>
            </div>
            <span class="house-ad__cta house-ad__cta--wide">Reklam Ver</span>
        </div>
    @else
        <div class="house-ad__body house-ad__body--compact">
            <img src="{{ $logoUrl }}" alt="Ografi" class="house-ad__logo">
            <div class="house-ad__copy">
                <h3 class="house-ad__title house-ad__title--compact">Bu alan senin reklamın olabilir</h3>
                <span class="house-ad__cta">Reklam Ver</span>
            </div>
        </div>
    @endif
</a>

{{--
    Onemli: bu partial'in stili BURADA @once ile basilmaz - bu partial her zaman
    "kapatilabilir" bir @slot@ wrapper'inin ICINDE render ediliyor (bkz. slot.blade.php).
    O wrapper kapat (X) butonuyla DOM'dan kaldirilabiliyor; stil burada olsaydi ilk
    kutuyu kapatan kullanici, sayfadaki TUM house-ad kutularinin stilini de silmis
    olurdu. Bu yuzden ortak stil, hicbir zaman kaldirilmayan slot.blade.php'nin
    en alttaki @once bloguna tasindi.
--}}
