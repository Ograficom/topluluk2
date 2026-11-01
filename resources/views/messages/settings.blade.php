@extends('layouts.repost')

@section('title', 'Mesaj ayarları — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">GİZLİLİK</span><h1>Mesaj ayarları</h1><p>Kimlerin sana mesaj gönderebileceğini belirle.</p></div></div>
    <form method="POST" action="{{ route('messages.settings.update') }}" class="card card-body repost-message-settings">
        @csrf
        <label><input type="checkbox" name="allow_messages" value="1" @checked($preferences->allow_messages)> Üyelerden mesaj al</label>
        <label><input type="checkbox" name="allow_following_only" value="1" @checked($preferences->allow_following_only)> Yalnızca takip ettiklerim mesaj gönderebilsin</label>
        <p class="meta">Site genelindeki mesajlaşma kuralları bu tercihleri sınırlandırabilir.</p>
        <button type="submit" class="btn btn-primary btn-rounded">Ayarları kaydet</button>
    </form>
@endsection
