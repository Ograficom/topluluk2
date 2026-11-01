@extends('layouts.repost')

@section('title', 'Yeni şifre belirle — OgraFi')
@section('content')
<div class="repost-auth-wrap">
    <section class="card card-body repost-auth-card">
        <a href="{{ route('home') }}" class="repost-auth-brand">OgraFi</a>
        <span class="repost-eyebrow">HESAP GÜVENLİĞİ</span>
        <h1>Yeni şifreni belirle</h1>
        <x-validation-errors class="mb-4" />
        <form method="POST" action="{{ route('password.update') }}" class="repost-auth-form">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <label for="email">E-posta adresi</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autocomplete="email">
            <label for="password">Yeni şifre</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
            <label for="password_confirmation">Yeni şifre tekrar</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            <button type="submit" class="btn btn-primary btn-rounded">Şifreyi güncelle</button>
        </form>
    </section>
</div>
@endsection
