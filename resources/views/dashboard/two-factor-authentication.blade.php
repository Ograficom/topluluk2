@extends('layouts.repost')

@section('title', 'İki adımlı doğrulama — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">HESAP GÜVENLİĞİ</span><h1>İki adımlı doğrulama</h1><p>Hesabına ek bir güvenlik katmanı ekle.</p></div></div>
    <section class="card card-body repost-settings-form">
        @if(auth()->user()->two_factor_secret)
            <strong>İki adımlı doğrulama etkin</strong>
            <p class="meta">Hesabın doğrulama kodu gerektirecek şekilde korunuyor.</p>
            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">@csrf @method('DELETE')<button class="btn btn-secondary btn-rounded" type="submit">İki adımlı doğrulamayı kapat</button></form>
        @else
            <strong>İki adımlı doğrulama etkin değil</strong>
            <p class="meta">Etkinleştirmek için bir doğrulama uygulaması kullanılır. Güvenlik ayarları hesabın üzerinden yönetilir.</p>
            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">@csrf<button class="btn btn-primary btn-rounded" type="submit">Etkinleştir</button></form>
        @endif
    </section>
@endsection
