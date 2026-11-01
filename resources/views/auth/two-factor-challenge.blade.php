@extends('layouts.repost')

@section('title', 'İki adımlı giriş — OgraFi')
@section('content')
    <div class="repost-auth-wrap">
        <section class="card card-body repost-auth-card">
            <a href="{{ route('home') }}" class="repost-auth-brand">OgraFi</a>
            <span class="repost-eyebrow">HESAP GÜVENLİĞİ</span>
            <h1>Girişini doğrula</h1>
            <p class="repost-auth-description">Kimlik doğrulama uygulamandaki kodu veya kurtarma kodlarından birini gir.</p>
            <x-validation-errors />
            <form method="POST" action="{{ route('two-factor.login') }}" class="repost-auth-form">
                @csrf
                <label for="code">Doğrulama kodu</label>
                <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code">
                <label for="recovery_code">Kurtarma kodu</label>
                <input id="recovery_code" name="recovery_code" autocomplete="one-time-code">
                <button class="btn btn-primary btn-rounded" type="submit">Devam et</button>
            </form>
        </section>
    </div>
@endsection
