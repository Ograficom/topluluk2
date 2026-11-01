@extends('layouts.repost')

@section('title', 'Giriş yap — OgraFi')
@section('content')
@php($recaptcha = \App\Models\RecaptchaSetting::currentOrNull())
@php($requiresRecaptcha = $recaptcha?->isEnabledFor('login') ?? false)
<div class="repost-auth-wrap">
    <section class="card card-body repost-auth-card">
        <a href="{{ route('home') }}" class="repost-auth-brand">OgraFi</a>
        <span class="repost-eyebrow">TEKRAR HOŞ GELDİN</span>
        <h1>Hesabına giriş yap</h1>
        <p class="repost-auth-description">Topluluğa kaldığın yerden devam et.</p>
        @if(session('status'))<div class="repost-auth-status">{{ session('status') }}</div>@endif
        <x-validation-errors class="mb-4" />
        <form method="POST" action="{{ route('login') }}" class="repost-auth-form" id="repost-login-form">
            @csrf
            <label for="email">E-posta adresi</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')<span class="repost-form-error">{{ $message }}</span>@enderror
            <label for="password">Şifre</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            @error('password')<span class="repost-form-error">{{ $message }}</span>@enderror
            <label class="repost-auth-check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Oturumu açık tut</label>
            <input type="hidden" name="recaptcha_token" id="repost-login-recaptcha-token">
            <button type="submit" class="btn btn-primary btn-rounded">Giriş yap</button>
        </form>
        <div class="repost-auth-links"><a href="{{ route('password.request') }}">Şifremi unuttum</a><a href="{{ route('register') }}">Yeni hesap oluştur</a></div>
    </section>
</div>
@if($requiresRecaptcha && $recaptcha?->resolvedSiteKey())
    <script src="https://www.google.com/recaptcha/api.js?render={{ urlencode($recaptcha->resolvedSiteKey()) }}"></script>
    <script>
        document.getElementById('repost-login-form')?.addEventListener('submit', function (event) {
            if (this.dataset.recaptchaReady === '1') return;
            event.preventDefault();
            if (!window.grecaptcha) { this.submit(); return; }
            grecaptcha.ready(() => grecaptcha.execute(@js($recaptcha->resolvedSiteKey()), {action: 'login'}).then(token => {
                document.getElementById('repost-login-recaptcha-token').value = token;
                this.dataset.recaptchaReady = '1';
                this.submit();
            }));
        });
    </script>
@endif
@endsection
