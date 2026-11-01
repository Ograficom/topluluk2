@extends('layouts.repost')

@section('title', 'Profil bilgileri — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">HESABIM</span><h1>Profil bilgileri</h1><p>Profilinde görünen bilgileri güncelle.</p></div><a href="{{ route('users.show', auth()->user()->username) }}" class="btn btn-secondary btn-rounded">Profili görüntüle</a></div>
    <form method="POST" action="{{ route('dashboard.profile.update') }}" class="card card-body repost-settings-form">
        @csrf
        @method('PUT')
        <label for="bio">Hakkımda</label>
        <textarea id="bio" name="bio" rows="4" maxlength="160">{{ old('bio', auth()->user()->bio) }}</textarea>
        @error('bio')<span class="repost-form-error">{{ $message }}</span>@enderror
        <label for="location">Konum</label>
        <input id="location" name="location" value="{{ old('location', auth()->user()->location) }}" maxlength="255">
        <label for="company">Şirket</label>
        <input id="company" name="company" value="{{ old('company', auth()->user()->company) }}" maxlength="255">
        <label for="education">Eğitim</label>
        <input id="education" name="education" value="{{ old('education', auth()->user()->education) }}" maxlength="255">
        <label for="website_url">Web sitesi</label>
        <input id="website_url" type="url" name="website_url" value="{{ old('website_url', auth()->user()->website_url) }}" maxlength="255">
        <label for="profile_type">Profil türü</label>
        <select id="profile_type" name="profile_type">
            <option value="person" @selected(old('profile_type', auth()->user()->profile_type) === 'person')>Kişisel</option>
            <option value="organization" @selected(old('profile_type', auth()->user()->profile_type) === 'organization')>Kurumsal</option>
        </select>
        <div class="repost-settings-grid">
            <div><label for="social_facebook">Facebook</label><input id="social_facebook" name="social_facebook" value="{{ old('social_facebook', auth()->user()->social_facebook) }}"></div>
            <div><label for="social_x">X</label><input id="social_x" name="social_x" value="{{ old('social_x', auth()->user()->social_x) }}"></div>
            <div><label for="social_instagram">Instagram</label><input id="social_instagram" name="social_instagram" value="{{ old('social_instagram', auth()->user()->social_instagram) }}"></div>
            <div><label for="social_tiktok">TikTok</label><input id="social_tiktok" name="social_tiktok" value="{{ old('social_tiktok', auth()->user()->social_tiktok) }}"></div>
            <div><label for="social_youtube">YouTube</label><input id="social_youtube" name="social_youtube" value="{{ old('social_youtube', auth()->user()->social_youtube) }}"></div>
        </div>
        <button type="submit" class="btn btn-primary btn-rounded">Değişiklikleri kaydet</button>
    </form>
@endsection
