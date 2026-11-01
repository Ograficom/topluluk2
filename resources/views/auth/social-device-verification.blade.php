@extends('layouts.repost')

@section('title', 'Cihaz doğrulama — OgraFi')
@section('content')
    <div class="repost-auth-wrap">
        <section class="card card-body repost-auth-card">
            <a href="{{ route('home') }}" class="repost-auth-brand">OgraFi</a>
            <span class="repost-eyebrow">HESAP GÜVENLİĞİ</span>
            <h1>Yeni cihazı doğrula</h1>
            <p class="repost-auth-description">Güvenliğin için e-posta adresine gönderilen 6 haneli kodu gir.</p>
            <x-validation-errors />
            <form method="POST" action="{{ route('social.device.verify.submit') }}" class="repost-auth-form">
                @csrf
                <label for="device_verification_code">Doğrulama kodu</label>
                <input id="device_verification_code" name="device_verification_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus>
                <button class="btn btn-primary btn-rounded" type="submit">Cihazı doğrula</button>
            </form>
            <a href="{{ route('login') }}" class="repost-auth-links">Giriş sayfasına dön</a>
        </section>
    </div>
@endsection
