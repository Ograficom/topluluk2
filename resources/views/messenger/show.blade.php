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
                    <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="#dialog-message">Bir mesaj yazın</a></li>
                    <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('messenger.index') }}">Mesaj listesi</a></li>
                    <li class="pm__dropdown-limit">
                        <div class="pm__limit-label">{{ $messageUsagePercent }}% / (50 mesaj)</div>
                        <div class="pm_progress_bar" role="progressbar" aria-label="Mesaj kullanım oranı" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $messageUsagePercent }}"><span style="width: {{ $messageUsagePercent }}%"></span></div>
                    </li>
                </ul>
            </div>
        </header>

        <section class="pm-dialog">
            <header class="pm-dialog__header">
                <div class="pm-dialog__heading">
                    <h2>{{ $other?->displayName() ?? 'OgraFi üyesi' }}</h2>
                    <div class="pm-dialog__recipient">
                        <span class="pm-dialog__status {{ $other?->isOnline() ? 'is-online' : '' }}">
                            <span class="pm-dialog__status-dot" aria-hidden="true"></span>
                            {{ $other?->isOnline() ? 'Şu anda çevrimiçi' : 'Özel konuşma' }}
                        </span>
                        @if ($other)
                            <a class="pm-dialog__profile-link" href="{{ route('profiles.show', $other) }}">
                                Profili görüntüle
                                <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M6 3h7v7M13 3 6.5 9.5M11 9v4H3V5h4" /></svg>
                            </a>
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
                                @if ($isOwnMessage)
                                    <div class="dropdown pm-dialog__actions">
                                        <button class="pm-dialog__more" type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" aria-label="Mesaj seçenekleri">
                                            <x-repost-icon name="more" size="18" />
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <div class="dropdown-menu-item dropdown-menu-item-sm"><button type="button" data-quote-message="{{ $message->body }}">Alıntıla</button></div>
                                            <div class="dropdown-menu-item dropdown-menu-item-sm"><button type="button" data-edit-message="{{ $message->id }}">Düzenle</button></div>
                                            <div class="dropdown-menu-item dropdown-menu-item-sm is-danger">
                                                <form action="{{ route('messenger.messages.delete', [$conversation, $message]) }}" method="POST" data-delete-message>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit">Sil</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <time datetime="{{ $message->created_at->toAtomString() }}">{{ $message->created_at->format('d.m.Y, H:i') }}</time>
                                    <span class="pm-dialog__author">{{ $message->user?->displayName() ?? 'Sen' }}</span>
                                @else
                                    <a class="pm-dialog__author pm_list" href="{{ $message->user ? route('profiles.show', $message->user) : route('messenger.index') }}">{{ $message->user?->displayName() ?? 'Silinmiş kullanıcı' }}</a>
                                    <time datetime="{{ $message->created_at->toAtomString() }}">{{ $message->created_at->format('d.m.Y, H:i') }}</time>
                                    <div class="dropdown pm-dialog__actions">
                                        <button class="pm-dialog__more" type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" aria-label="Mesaj seçenekleri">
                                            <x-repost-icon name="more" size="18" />
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <div class="dropdown-menu-item dropdown-menu-item-sm"><button type="button" data-quote-message="{{ $message->body }}">Alıntıla</button></div>
                                            @if ($message->user)
                                                <div class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('profiles.show', $message->user) }}">Profili görüntüle</a></div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="pm-dialog__bubble" data-message-bubble><div class="pm-dialog__text">{{ $message->body }}</div></div>
                            @if ($isOwnMessage)
                                <form class="pm-dialog__edit-form" action="{{ route('messenger.messages.update', [$conversation, $message]) }}" method="POST" data-edit-form hidden>
                                    @csrf
                                    @method('PATCH')
                                    <textarea name="body" rows="3" maxlength="5000" required>{{ $message->body }}</textarea>
                                    <div class="pm-dialog__edit-actions">
                                        <button class="btn btn-primary btn-sm" type="submit">Kaydet</button>
                                        <button class="btn btn-secondary btn-sm" type="button" data-cancel-edit>Vazgeç</button>
                                    </div>
                                </form>
                            @endif
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

            <form action="{{ route('messenger.messages.store', $conversation) }}" method="POST" class="pm-dialog__composer">
                @csrf
                <div class="pm-dialog__editor">
                    <label class="visually-hidden" for="message-body">Mesajın</label>
                    <textarea id="dialog-message" name="body" rows="3" maxlength="5000" required placeholder="Mesajını yaz...">{{ old('body') }}</textarea>
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
            const textarea = document.querySelector('#dialog-message');
            const messages = document.querySelector('#pmMessages');
            if (!messages) return;

            messages.querySelectorAll('[data-quote-message]').forEach((button) => {
                button.addEventListener('click', () => {
                    if (!textarea) return;
                    const quote = button.getAttribute('data-quote-message') || '';
                    textarea.value = quote.split('\n').map((line) => '> ' + line).join('\n') + '\n\n';
                    textarea.focus();
                    textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                    document.querySelector('#dialog-message')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            });

            messages.querySelectorAll('[data-edit-message]').forEach((button) => {
                button.addEventListener('click', () => {
                    const article = button.closest('[data-message-id]');
                    const form = article?.querySelector('[data-edit-form]');
                    const bubble = article?.querySelector('[data-message-bubble]');
                    if (!form || !bubble) return;
                    form.hidden = false;
                    bubble.hidden = true;
                    form.querySelector('textarea')?.focus();
                });
            });

            messages.querySelectorAll('[data-cancel-edit]').forEach((button) => {
                button.addEventListener('click', () => {
                    const article = button.closest('[data-message-id]');
                    const form = article?.querySelector('[data-edit-form]');
                    const bubble = article?.querySelector('[data-message-bubble]');
                    if (form) form.hidden = true;
                    if (bubble) bubble.hidden = false;
                });
            });

            messages.querySelectorAll('[data-delete-message]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    if (!window.confirm('Bu mesajı silmek istediğine emin misin?')) event.preventDefault();
                });
            });
        })();
    </script>
@endpush
