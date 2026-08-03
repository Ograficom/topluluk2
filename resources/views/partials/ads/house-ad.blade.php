@php
    $slotKey = (string) ($slotKey ?? '');
    $adImageUrl = asset('images/reklam-ver-banner.svg') . '?v=20260803a';
    $boxId = 'reklamKutusu-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $slotKey) . '-' . substr(md5($slotKey . microtime()), 0, 8);
@endphp

<div class="reklam-alani" id="{{ $boxId }}">
    <div class="reklam-ust">
        <span>Reklam</span>
        <button class="kapat-btn" type="button" data-reklam-kapat="{{ $boxId }}">X</button>
    </div>

    <div class="reklam-icerik">
        <img src="{{ $adImageUrl }}" alt="Reklam Görseli" class="reklam-resim">
    </div>
</div>

{{--
    Stil (.reklam-alani/.reklam-ust/.kapat-btn/.reklam-icerik/.reklam-resim) ve kapatma
    script'i slot.blade.php'nin en alttaki @once blogunda - bu partial sayfada birden
    fazla reklam alaninda tekrar tekrar render edildigi icin stil/script burada olsaydi
    her tekrarda kopyalanirdi.
--}}
