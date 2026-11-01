@extends('layouts.repost')

@section('title', 'Mesajlar — OgraFi')
@section('content')
    <div data-ografi-layout-container="messages_index">
        <section data-ografi-layout-section="messages_header" class="repost-feed-heading"><div><span class="repost-eyebrow">ÖZEL İLETİŞİM</span><h1>Mesajlar</h1><p>Konuşmaların burada görünür.</p></div><a href="{{ route('messages.contacts') }}" class="btn btn-secondary btn-rounded">Kişiler</a></section>
        <div data-ografi-layout-section="messages_threads" class="card repost-message-list">
            @forelse($threads as $thread)
                @php($person = $thread['user'])
                @php($last = $thread['last_message'])
                <div class="repost-message-row">
                    <a href="{{ route('messages.show', $person->username) }}" class="user-box"><span class="avatar"><img src="{{ $person->profile_photo_url }}" alt="" width="48" height="48"></span><span class="user-box-content"><strong class="name">{{ $person->name }}</strong><span class="meta">{{ \Illuminate\Support\Str::limit($last->body ?: ($last->attachment_name ?? 'Dosya'), 75) }}</span><time class="meta">{{ $last->created_at?->diffForHumans() }}</time></span></a>
                    <div class="repost-message-row-actions">
                        @if($thread['unread'] > 0)<span class="repost-unread-count">{{ $thread['unread'] }}</span>@endif
                        <form method="POST" action="{{ route('messages.pin', $person->username) }}">@csrf<button class="btn btn-secondary btn-sm" type="submit">{{ $thread['pinned'] ? 'Sabitlemeyi kaldır' : 'Sabitle' }}</button></form>
                        <form method="POST" action="{{ route('messages.delete', $person->username) }}">@csrf<button class="btn btn-secondary btn-sm" type="submit">Sil</button></form>
                    </div>
                </div>
            @empty
                <div class="repost-empty-state"><h2>Henüz mesajın yok</h2><p>Takip ettiğin kişiler arasından bir konuşma başlatabilirsin.</p><a href="{{ route('messages.contacts') }}" class="btn btn-primary btn-rounded">Kişileri görüntüle</a></div>
            @endforelse
        </div>
    </div>
@endsection
