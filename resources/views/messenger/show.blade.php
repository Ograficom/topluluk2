@extends('layouts.app')

@section('title', ($other?->displayName() ?? 'Mesajlaşma') . ' ile sohbet')

@section('content')
    <article class="pm card card-body">
        <header class="pm__header">
            <h1 class="h3"><a href="{{ route('messenger.index') }}">Mesajlar</a></h1>
            <div class="dropdown dropdown-md">
                <button class="btn btn-secondary btn-xs btn-rounded" type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" aria-controls="pmDialogMenu" aria-label="Sohbet menüsünü aç">
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
                        {{ $other?->isOnline() ? 'Şu anda çevrimiçi' : 'Özel konuşma' }}
                        @if ($other)
                            <span aria-hidden="true">·</span>
                            <a class="pm_list" href="{{ route('profiles.show', $other) }}">Profili görüntüle</a>
                        @endif
                    </div>
                </div>
            </header>

            <div class="pm-dialog__messages" id="pmMessages">
                @forelse ($messages as $message)
                    @php($isOwnMessage = (int) $message->user_id === (int) auth()->id())
                    <article class="pm-dialog__message {{ $isOwnMessage ? 'is-own' : 'is-incoming' }}" data-message-id="{{ $message->id }}">
                        <div class="pm-dialog__avatar">
                            <img src="{{ $message->user?->avatarUrl() ?? asset('assets/img/noavatar.png') }}" alt="{{ $message->user?->displayName() ?? 'Silinmiş kullanıcı' }}" width="40" height="40" loading="lazy">
                            @if ($message->user?->isOnline())
                                <span class="is-online" title="Çevrimiçi"></span>
                            @endif
                        </div>
                        <div class="pm-dialog__message-main">
                            <div class="pm-dialog__meta">
                                @if (! $isOwnMessage)
                                    <a class="pm-dialog__author pm_list" href="{{ $message->user ? route('profiles.show', $message->user) : route('messenger.index') }}">{{ $message->user?->displayName() ?? 'Silinmiş kullanıcı' }}</a>
                                @endif
                                <time datetime="{{ $message->created_at->toAtomString() }}">{{ $message->created_at->format('d.m.Y, H:i') }}</time>
                                @if ($isOwnMessage)
                                    <span class="pm-dialog__author">{{ $message->user?->displayName() ?? 'Sen' }}</span>
                                @endif
                                <div class="dropdown pm-dialog__actions">
                                    <button class="pm-dialog__more" type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" aria-label="Mesaj seçenekleri">
                                        <x-repost-icon name="more" size="18" />
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <div class="dropdown-menu-item dropdown-menu-item-sm">
                                            <button type="button" data-quote-message="{{ $message->body }}">Alıntıla</button>
                                        </div>
                                        @if ($message->user)
                                            <div class="dropdown-menu-item dropdown-menu-item-sm">
                                                <a href="{{ route('profiles.show', $message->user) }}">Profili görüntüle</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
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
                    <div class="pm-dialog__empty">
                        <p>Bu konuşmada henüz mesaj yok.</p>
                        <span>İlk mesajı göndererek sohbeti başlat.</span>
                    </div>
                @endforelse
            </div>

            {{ $messages->links() }}

            <form action="{{ route('messenger.messages.store', $conversation) }}" method="POST" class="pm-dialog__composer" id="dialog-message">
                @csrf
                <div class="pm-dialog__editor">
                    <label class="visually-hidden" for="message-body">Mesajın</label>
                    <textarea id="message-body" name="body" rows="3" maxlength="5000" required placeholder="Mesajını yaz...">{{ old('body') }}</textarea>
                </div>
                @error('body')
                    <p class="small color-red">{{ $message }}</p>
                @enderror
                <div class="pm-dialog__composer-actions">
                    <button class="btn btn-primary" type="submit">Gönder</button>
                </div>
            </form>
        </section>
    </article>
@endsection

@push('scripts')
    <script>
        (() => {
            const textarea = document.querySelector('#message-body');
            const messages = document.querySelector('#pmMessages');
            if (!textarea || !messages) return;

            messages.querySelectorAll('[data-quote-message]').forEach((button) => {
                button.addEventListener('click', () => {
                    const quote = button.getAttribute('data-quote-message') || '';
                    textarea.value = quote.split('\n').map((line) => '> ' + line).join('\n') + '\n\n';
                    textarea.focus();
                    textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                });
            });
        })();
    </script>
@endpush
