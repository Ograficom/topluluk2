@extends('layouts.repost')

@section('title', 'Şifremi unuttum — OgraFi')
@section('content')
<div class="repost-auth-wrap">
    <section class="card card-body repost-auth-card">
        <a href="{{ route('home') }}" class="repost-auth-brand">OgraFi</a>
        <span class="repost-eyebrow">HESAP GÜVENLİĞİ</span>
        <h1>Şifreni sıfırla</h1>
        <p class="repost-auth-description">E-posta adresini gir. Şifre sıfırlama bağlantısı gönderelim.</p>
        @if(session('status'))<div class="repost-auth-status">{{ session('status') }}</div>@endif
        <x-validation-errors class="mb-4" />
        <form method="POST" action="{{ route('password.email') }}" class="repost-auth-form">
            @csrf
            <label for="email">E-posta adresi</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            <button type="submit" class="btn btn-primary btn-rounded">Sıfırlama bağlantısı gönder</button>
        </form>
        <div class="repost-auth-links"><a href="{{ route('login') }}">Giriş sayfasına dön</a></div>
    </section>
</div>
@endsection
