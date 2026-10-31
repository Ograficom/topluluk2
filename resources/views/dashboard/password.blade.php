@extends('layouts.repost')

@section('title', 'Şifre — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">GÜVENLİK</span><h1>Şifre değiştir</h1><p>Hesabını korumak için güçlü bir şifre kullan.</p></div></div>
    <form method="POST" action="{{ route('dashboard.password.update') }}" class="card card-body repost-settings-form">
        @csrf
        @method('PUT')
        <label for="current_password">Mevcut şifre</label><input id="current_password" type="password" name="current_password" required autocomplete="current-password">
        @error('current_password')<span class="repost-form-error">{{ $message }}</span>@enderror
        <label for="password">Yeni şifre</label><input id="password" type="password" name="password" required autocomplete="new-password">
        @error('password')<span class="repost-form-error">{{ $message }}</span>@enderror
        <label for="password_confirmation">Yeni şifre tekrar</label><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        <button class="btn btn-primary btn-rounded" type="submit">Şifreyi güncelle</button>
    </form>
@endsection
