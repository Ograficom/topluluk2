@extends('layouts.app')

@section('title', $stage === 'verify' ? 'E-posta doğrulama — OgraFi' : 'Hesabını tamamla — OgraFi')

@section('content')
    <section class="card card-body repost-auth-card">
        @if($stage === 'verify')
            <span class="repost-eyebrow">E-POSTA DOĞRULAMA</span>
            <h1 class="h3">Doğrulama kodunu gir</h1>
            <p class="color-gray">{{ $pending->email }} adresine gönderilen 6 haneli kodu yaz.</p>
            <form method="POST" action="{{ route('register.verify.submit') }}" class="repost-auth-form">
                @csrf
                <label for="verification-code">6 haneli kod</label>
                <input id="verification-code" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required>
                @error('code')<p class="color-red">{{ $message }}</p>@enderror
                <button type="submit" class="btn btn-primary btn-rounded">E-postayı doğrula</button>
            </form>
        @else
            <span class="repost-eyebrow">SON ADIM</span>
            <h1 class="h3">Hesabını tamamla</h1>
            <p class="color-gray">{{ $pending->email }} adresi doğrulandı.</p>
            <form method="POST" action="{{ route('register.complete.submit') }}" class="repost-auth-form">
                @csrf
                <label for="register-name">Adın</label>
                <input id="register-name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required>
                @error('name')<p class="color-red">{{ $message }}</p>@enderror
                <label for="register-password">Şifre</label>
                <input id="register-password" type="password" name="password" autocomplete="new-password" required>
                @error('password')<p class="color-red">{{ $message }}</p>@enderror
                <label for="register-password-confirmation">Şifreyi tekrar gir</label>
                <input id="register-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                <button type="submit" class="btn btn-primary btn-rounded">Hesabı oluştur</button>
            </form>
        @endif
    </section>
@endsection
