@extends('layouts.app')

@section('title', 'OgraFi’ye katıl')

@section('content')
    <section class="card card-body repost-auth-card">
        <span class="repost-eyebrow">OGRAFI TOPLULUĞU</span>
        <h1 class="h3">Hesap oluştur</h1>
        <p class="color-gray">OgraFi'ye katıl, toplulukları takip et ve kendi paylaşımlarını yayınla.</p>
        <form method="POST" action="{{ route('register.email') }}" class="repost-auth-form">
            @csrf
            <label for="register-email">E-posta adresi</label>
            <input id="register-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            @error('email')<p class="color-red">{{ $message }}</p>@enderror
            <button type="submit" class="btn btn-primary btn-rounded">Doğrulama kodu gönder</button>
        </form>
        <p class="small color-gray">Zaten hesabın var mı? <a href="{{ route('login') }}">Giriş yap</a></p>
    </section>
@endsection
