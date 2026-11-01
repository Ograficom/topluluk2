@extends('layouts.repost')

@section('title', 'Tepkilerim — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">TOPLULUK ETKİLEŞİMİ</span><h1>Tepki yönetimi</h1><p>Gönderilerde kullanılabilecek tepkileri görüntüle ve yeni tepki öner.</p></div></div>
    @if($reactionUploadBlocked)
        <div class="card card-body">Yeni tepki ekleme yetkin şu anda kısıtlanmış.</div>
    @else
        <form method="POST" action="{{ route('dashboard.reactions.store') }}" enctype="multipart/form-data" class="card card-body repost-settings-form">
            @csrf
            <h2>Yeni tepki öner</h2>
            <label for="label">Tepki adı</label><input id="label" name="label" maxlength="100" value="{{ old('label') }}" required>
            <label for="emoji">Emoji (isteğe bağlı)</label><input id="emoji" name="emoji" maxlength="16" value="{{ old('emoji') }}">
            <label for="reaction_image">GIF veya görsel (isteğe bağlı)</label><input id="reaction_image" name="reaction_image" type="file" accept="image/gif,image/png,image/jpeg,image/webp">
            <label class="repost-settings-check"><input type="checkbox" name="policy_ack" value="1" required> Yüklediğim içeriğin kullanım hakkına sahip olduğumu onaylıyorum.</label>
            @error('label')<span class="repost-form-error">{{ $message }}</span>@enderror
            @error('emoji')<span class="repost-form-error">{{ $message }}</span>@enderror
            <button class="btn btn-primary btn-rounded" type="submit">Tepki öner</button>
        </form>
    @endif
    <section class="repost-discover-section"><h2>Aktif tepkiler</h2><div class="repost-reaction-grid">
        @foreach($reactionTypes as $reaction)
            <div class="card card-body repost-reaction-tile">@if($reaction->gif_url)<img src="{{ $reaction->gif_url }}" alt="" loading="lazy">@else<span>{{ $reaction->emoji }}</span>@endif<strong>{{ $reaction->label }}</strong></div>
        @endforeach
    </div></section>
    <section class="repost-discover-section"><h2>Önerdiklerim</h2><div class="repost-reaction-grid">
        @forelse($myReactionTypes as $reaction)
            <div class="card card-body repost-reaction-tile">@if($reaction->gif_url)<img src="{{ $reaction->gif_url }}" alt="">@else<span>{{ $reaction->emoji }}</span>@endif<strong>{{ $reaction->label }}</strong><small class="meta">{{ $reaction->is_active ? 'Aktif' : 'Pasif' }}</small></div>
        @empty
            <p class="meta">Henüz tepki önermedin.</p>
        @endforelse
    </div></section>
@endsection
