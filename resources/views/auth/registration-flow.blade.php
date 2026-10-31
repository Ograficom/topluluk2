@extends('layouts.repost')

@section('title', ($stage ?? 'verify') === 'verify' ? 'E-posta doğrulama — OgraFi' : 'Hesabını tamamla — OgraFi')
@section('content')
<div class="repost-auth-wrap">
    <section class="card card-body repost-auth-card">
        <a href="{{ route('home') }}" class="repost-auth-brand">OgraFi</a>
        @if(($stage ?? 'verify') === 'verify')
            <span class="repost-eyebrow">E-POSTA DOĞRULAMA</span>
            <h1>Kodunu kontrol et</h1>
            <p class="repost-auth-description"><strong>{{ $pending->email }}</strong> adresine gönderilen 6 haneli kodu gir.</p>
            <x-validation-errors class="mb-4" />
            <form method="POST" action="{{ route('register.verify.submit') }}" class="repost-auth-form">
                @csrf
                <label for="code">Doğrulama kodu</label>
                <input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus>
                @error('code')<span class="repost-form-error">{{ $message }}</span>@enderror
                <button type="submit" class="btn btn-primary btn-rounded">E-postayı doğrula</button>
            </form>
        @else
            <span class="repost-eyebrow">SON ADIM</span>
            <h1>Profilini tamamla</h1>
            <p class="repost-auth-description">{{ $pending->email }} adresi doğrulandı. Adını ve şifreni belirle.</p>
            <x-validation-errors class="mb-4" />
            <form method="POST" action="{{ route('register.complete.submit') }}" class="repost-auth-form">
                @csrf
                <label for="name">Ad soyad</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" maxlength="255" required autofocus>
                <label for="password">Şifre</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">
                <label for="password_confirmation">Şifre tekrar</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                <button type="submit" class="btn btn-primary btn-rounded">Hesabı oluştur</button>
            </form>
        @endif
    </section>
</div>
@endsection
