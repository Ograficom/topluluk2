@extends('layouts.repost')

@section('title', 'Hesap bilgileri — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">HESAP</span><h1>Hesap bilgileri</h1><p>Adını, kullanıcı adını ve e-posta adresini güncelle.</p></div></div>
    <form method="POST" action="{{ route('dashboard.account.update') }}" class="card card-body repost-settings-form">
        @csrf
        @method('PUT')
        <label for="name">Ad soyad</label><input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required maxlength="255">
        @error('name')<span class="repost-form-error">{{ $message }}</span>@enderror
        <label for="username">Kullanıcı adı</label><input id="username" name="username" value="{{ old('username', auth()->user()->username) }}" required maxlength="255">
        @error('username')<span class="repost-form-error">{{ $message }}</span>@enderror
        <label for="email">E-posta</label><input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required maxlength="255">
        @error('email')<span class="repost-form-error">{{ $message }}</span>@enderror
        <button class="btn btn-primary btn-rounded" type="submit">Bilgileri kaydet</button>
    </form>
@endsection
