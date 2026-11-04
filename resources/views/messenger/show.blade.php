@extends('layouts.app')

@section('title', $other?->displayName() ?? 'Mesajlaşma')

@section('content')
    <article class="pm card card-body">
        <header class="pm__header">
            <h1 class="h3"><a href="{{ route('messenger.index') }}">Mesajlar</a></h1>
            <div class="dropdown dropdown-md">
                <button class="btn btn-secondary btn-xs btn-rounded" type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" aria-controls="pmDialogMenu" aria-label="Mesaj menüsünü aç">
                    <x-repost-icon name="more" size="18" class="color-gray" />
                </button>
                <ul class="pm__dropdown dropdown-menu dropdown-menu-end" id="pmDialogMenu">
                    <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('messenger.index') }}">Mesaj listesi</a></li>
                    @if ($other)
                        <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('profiles.show', $other) }}">Profili görüntüle</a></li>
                    @endif
                </ul>
            </div>
        </header>

        <section class="pm-dialog">
            <header class="pm-dialog__header">
                <div class="pm-dialog__heading">
                    <h2>{{ $other?->displayName() ?? 'OgraFi üyesi' }}</h2>
                    <div class="pm-dialog__recipient">
                        Konuştuğun kişi:
                        @if ($other)
                            <a class="pm_list" href="{{ route('profiles.show', $other) }}">{{ $other->displayName() }}</a>
                        @else
                            <span>Silinmiş kullanıcı</span>
                        @endif
                    </div>
                </div>
                @if ($other)
                    <a class="btn btn-secondary btn-sm" href="{{ route('profiles.show', $other) }}">Profili gör</a>
                @endif
            </header>

            <div class="pm-dialog__messages">
                @forelse ($messages as $message)
                    @php($isOwnMessage = (int) $message->user_id === (int) auth()->id())
                    <article class="pm-dialog__message {{ $isOwnMessage ? 'is-own' : 'is-incoming' }}">
                        <div class="pm-dialog__avatar">
                            <img
                                src="{{ $message->user?->avatarUrl() ?? asset('assets/img/noavatar.png') }}"
                                alt="{{ $message->user?->displayName() ?? 'Silinmiş kullanıcı' }}"
                                width="40"
                                height="40"
                                loading="lazy"
                            >
                        </div>
                        <div class="pm-dialog__message-main">
                            <div class="pm-dialog__meta">
                                @if (! $isOwnMessage)
                                    <a class="pm-dialog__author pm_list" href="{{ $message->user ? route('profiles.show', $message->user) : route('messenger.index') }}">
                                        {{ $message->user?->displayName() ?? 'Silinmiş kullanıcı' }}
                                    </a>
                                @endif
                                <time datetime="{{ $message->created_at->toAtomString() }}">{{ $message->created_at->format('d.m.Y H:i') }}</time>
                                @if ($isOwnMessage)
                                    <span class="pm-dialog__author">{{ $message->user?->displayName() ?? 'Sen' }}</span>
                                @endif
                            </div>
                            <div class="pm-dialog__bubble">
                                <div class="pm-dialog__text">{{ $message->body }}</div>
                            </div>
                            @if ($isOwnMessage && $message->read_at)
                                <span class="small color-gray">Okundu</span>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="color-gray">Bu konuşmadaki ilk mesajı sen gönderebilirsin.</p>
                @endforelse
            </div>

            {{ $messages->links() }}

            <form action="{{ route('messenger.messages.store', $conversation) }}" method="POST" class="pm-dialog__composer" id="dialog-message">
                @csrf
                <div class="pm-dialog__editor">
                    <label class="visually-hidden" for="message-body">Mesajın</label>
                    <textarea id="message-body" name="body" rows="5" maxlength="5000" required placeholder="Mesajını yaz...">{{ old('body') }}</textarea>
                </div>
                @error('body')
                    <p class="small color-red">{{ $message }}</p>
                @enderror
                <div class="pm-dialog__composer-actions">
                    <button class="btn btn-primary" type="submit">Mesaj gönder</button>
                </div>
            </form>
        </section>
    </article>
@endsection
