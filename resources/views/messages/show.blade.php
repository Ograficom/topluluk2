@extends('layouts.repost')

@section('title', $otherUser->name . ' ile mesajlar — OgraFi')
@section('content')
    <div class="repost-feed-heading">
        <div class="user-box">
            <span class="avatar"><img src="{{ $otherUser->profile_photo_url }}" alt="" width="48" height="48"></span>
            <span class="user-box-content"><h1>{{ $otherUser->name }}</h1><span class="meta">@{{ $otherUser->username }}</span></span>
        </div>
        <a href="{{ route('messages.index') }}" class="btn btn-secondary btn-rounded">Gelen kutusu</a>
    </div>
    <section class="card repost-conversation">
        <div class="repost-conversation-messages" id="repost-conversation-messages">
            @forelse($messages as $message)
                @php($mine = (int) $message->sender_id === (int) auth()->id())
                <article class="repost-message-bubble {{ $mine ? 'is-mine' : 'is-theirs' }}">
                    @if(filled($message->body))<p>{{ $message->body }}</p>@endif
                    @if($message->attachment_path)
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($message->attachment_path) }}" target="_blank" rel="noopener noreferrer">{{ $message->attachment_name ?: 'Ekli dosyayı görüntüle' }}</a>
                    @endif
                    <time>{{ $message->created_at?->format('d.m.Y H:i') }}</time>
                </article>
            @empty
                <p class="repost-empty-state">Henüz mesajlaşma başlamamış. İlk mesajı gönder.</p>
            @endforelse
        </div>
        @if($canMessage)
            <form method="POST" action="{{ route('messages.store', $otherUser->username) }}" enctype="multipart/form-data" class="repost-message-composer">
                @csrf
                <textarea name="body" rows="3" maxlength="2000" placeholder="Mesajını yaz...">{{ old('body') }}</textarea>
                @error('body')<span class="repost-form-error">{{ $message }}</span>@enderror
                <div class="repost-message-composer-actions"><label class="btn btn-secondary btn-rounded">Dosya ekle<input type="file" name="attachment" hidden accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,audio/mpeg,audio/mp3,audio/wav,audio/webm"></label><button class="btn btn-primary btn-rounded" type="submit">Gönder</button></div>
            </form>
        @else
            <div class="card-body repost-message-disabled">Bu kullanıcıya şu anda mesaj gönderemezsin.</div>
        @endif
    </section>
@endsection
