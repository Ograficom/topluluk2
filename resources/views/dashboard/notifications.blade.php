@extends('layouts.repost')

@section('title', 'Bildirimler — OgraFi')
@section('content')
    @php($prefs = session('dashboard_notifications', []))
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">BİLDİRİMLER</span><h1>Bildirim tercihleri</h1><p>Hangi etkinliklerde bildirim almak istediğini seç.</p></div></div>
    <form method="POST" action="{{ route('dashboard.notifications.update') }}" class="card card-body repost-settings-form">
        @csrf
        @method('PUT')
        @foreach(['comments' => 'Gönderilerime yapılan yorumlar', 'replies' => 'Yorumlarıma verilen yanıtlar', 'likes' => 'Beğeniler ve tepkiler', 'followers' => 'Yeni takipçiler', 'mentions' => 'Bahsedildiğim içerikler'] as $field => $label)
            <label class="repost-settings-check"><input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $prefs[$field] ?? true))> {{ $label }}</label>
        @endforeach
        <label class="repost-settings-check"><input type="checkbox" name="daily_digest_enabled" value="1" @checked(old('daily_digest_enabled', auth()->user()->daily_digest_enabled))> Günlük e-posta özeti al</label>
        <button class="btn btn-primary btn-rounded" type="submit">Bildirim tercihlerini kaydet</button>
    </form>
    <form method="POST" action="{{ route('dashboard.notifications.digest-email') }}" class="card card-body repost-settings-form" style="margin-top:14px">
        @csrf
        @method('PUT')
        <label for="daily_digest_email">Günlük özet e-posta adresi</label>
        <input id="daily_digest_email" type="email" name="daily_digest_email" value="{{ old('daily_digest_email', auth()->user()->daily_digest_email ?: auth()->user()->email) }}" required>
        <button class="btn btn-secondary btn-rounded" type="submit">E-posta adresini güncelle</button>
    </form>
@endsection
