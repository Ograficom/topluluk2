@extends('layouts.app')

@section('title', $other?->displayName() ?? 'Mesajlaşma')

@section('content')
    <article class="pm card card-body">
        <header class="card-header">
            <a href="{{ route('messenger.index') }}" class="link small">← Mesaj listesi</a>
            <div class="user-box mt-2">
                <span class="avatar"><img src="{{ $other?->avatarUrl() ?? asset('assets/img/noavatar.png') }}" alt="" width="44" height="44"></span>
                <div class="user-box-content"><h1 class="h5 name">{{ $other?->displayName() ?? 'OgraFi üyesi' }}</h1><span class="meta">Özel konuşma</span></div>
                @if ($other)<a href="{{ route('profiles.show', $other) }}" class="btn btn-secondary btn-sm">Profili gör</a>@endif
            </div>
        </header>
        <div class="dialog__messages">
            @forelse ($messages as $message)
                <article class="comment card card-body {{ $message->user_id === auth()->id() ? 'is-own' : '' }}">
                    <div class="user-box user-box-sm">
                        <span class="avatar"><img src="{{ $message->user?->avatarUrl() ?? asset('assets/img/noavatar.png') }}" alt="" width="36" height="36"></span>
                        <div class="user-box-content">
                            <strong class="name">{{ $message->user?->displayName() ?? 'Silinmiş kullanıcı' }}</strong>
                            <time class="meta" datetime="{{ $message->created_at->toAtomString() }}">{{ $message->created_at->format('d.m.Y H:i') }}</time>
                        </div>
                        @if ($message->read_at && $message->user_id === auth()->id())<span class="small color-gray">Okundu</span>@endif
                    </div>
                    <div class="comment__content"><p class="comment__text">{{ $message->body }}</p></div>
                </article>
            @empty
                <p class="color-gray">Bu konuşmadaki ilk mesajı sen gönderebilirsin.</p>
            @endforelse
            {{ $messages->links() }}
        </div>
        <form action="{{ route('messenger.messages.store', $conversation) }}" method="POST" class="addcomment">
            @csrf
            <label class="form-label" for="message-body">Mesajın</label>
            <textarea id="message-body" name="body" rows="4" maxlength="5000" required placeholder="Mesajını yaz...">{{ old('body') }}</textarea>
            @error('body')<p class="color-red small">{{ $message }}</p>@enderror
            <button class="btn btn-primary btn-md" type="submit">Mesaj gönder</button>
        </form>
    </article>
@endsection
