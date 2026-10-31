@extends('layouts.repost')

@section('title', 'Tercihler — OgraFi')
@section('content')
    @php($prefs = session('dashboard_preferences', []))
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">KİŞİSELLEŞTİRME</span><h1>Tercihler</h1><p>Akış ve dil tercihlerini düzenle.</p></div></div>
    <form method="POST" action="{{ route('dashboard.preferences.update') }}" class="card card-body repost-settings-form">
        @csrf
        @method('PUT')
        <label class="repost-settings-check"><input type="checkbox" name="show_mature" value="1" @checked(old('show_mature', $prefs['show_mature'] ?? false))> Hassas içerikleri göster</label>
        <label class="repost-settings-check"><input type="checkbox" name="blur_mature" value="1" @checked(old('blur_mature', $prefs['blur_mature'] ?? true))> Hassas içerikleri bulanıklaştır</label>
        <label class="repost-settings-check"><input type="checkbox" name="open_new_tab" value="1" @checked(old('open_new_tab', $prefs['open_new_tab'] ?? false))> Dış bağlantıları yeni sekmede aç</label>
        <label for="preferred_locale">Dil</label>
        <select id="preferred_locale" name="preferred_locale" required>
            <option value="auto" @selected(old('preferred_locale', auth()->user()->preferred_locale ?? 'auto') === 'auto')>Otomatik</option>
            @foreach(config('app.available_locales', []) as $locale => $localeName)
                <option value="{{ $locale }}" @selected(old('preferred_locale', auth()->user()->preferred_locale ?? 'auto') === $locale)>{{ $localeName }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary btn-rounded" type="submit">Tercihleri kaydet</button>
    </form>
@endsection
