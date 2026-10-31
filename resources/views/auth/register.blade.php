@extends('layouts.repost')

@section('title', 'Üye ol — OgraFi')
@section('content')
@php($recaptcha = \App\Models\RecaptchaSetting::currentOrNull())
@php($requiresRecaptcha = $recaptcha?->isEnabledFor('register') ?? false)
<div class="repost-auth-wrap">
    <section class="card card-body repost-auth-card">
        <a href="{{ route('home') }}" class="repost-auth-brand">OgraFi</a>
        <span class="repost-eyebrow">TOPLULUĞA KATIL</span>
        <h1>Hesabını oluştur</h1>
        <p class="repost-auth-description">E-posta adresini doğrula, ardından profilini tamamla.</p>
        <x-validation-errors class="mb-4" />
        <form method="POST" action="{{ route('register.email') }}" class="repost-auth-form" id="repost-register-form">
            @csrf
            <label for="email">E-posta adresi</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            @error('email')<span class="repost-form-error">{{ $message }}</span>@enderror
            <input type="hidden" name="recaptcha_token" id="repost-register-recaptcha-token">
            <button type="submit" class="btn btn-primary btn-rounded">Doğrulama kodu gönder</button>
        </form>
        <div class="repost-auth-links"><a href="{{ route('login') }}">Zaten hesabın var mı? Giriş yap</a></div>
    </section>
</div>
@if($requiresRecaptcha && $recaptcha?->resolvedSiteKey())
    <script src="https://www.google.com/recaptcha/api.js?render={{ urlencode($recaptcha->resolvedSiteKey()) }}"></script>
    <script>
        document.getElementById('repost-register-form')?.addEventListener('submit', function (event) {
            if (this.dataset.recaptchaReady === '1') return;
            event.preventDefault();
            if (!window.grecaptcha) { this.submit(); return; }
            grecaptcha.ready(() => grecaptcha.execute(@js($recaptcha->resolvedSiteKey()), {action: 'register'}).then(token => {
                document.getElementById('repost-register-recaptcha-token').value = token;
                this.dataset.recaptchaReady = '1';
                this.submit();
            }));
        });
    </script>
@endif
@endsection
