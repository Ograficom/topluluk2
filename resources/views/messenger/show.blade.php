@extends('layouts.app')

@section('title', ($other?->displayName() ?? 'Mesajlaşma') . ' ile sohbet')

@section('content')
    <article class="pm card card-body">
        <section class="pm-dialog">
            <header class="pm-dialog__header pm-dialog__header--conversation">
                <a class="pm-dialog__back" href="{{ route('messenger.index') }}" aria-label="Mesaj listesine dön">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                </a>
                <button class="pm-dialog__identity" type="button" data-open-user-info aria-label="Kullanıcı bilgilerini aç">
                    <img class="pm-dialog__identity-avatar" src="{{ $other?->avatarUrl() ?? asset('assets/img/noavatar.png') }}" alt="{{ $other?->displayName() ?? 'Kullanıcı' }}">
                    <span class="pm-dialog__identity-copy">
                        <strong>{{ $other?->displayName() ?? 'OgraFi üyesi' }}</strong>
                        <span class="pm-dialog__identity-status"><i class="{{ $other?->isOnline() ? 'is-online' : '' }}"></i>@if ($other?->isOnline()) Şu anda çevrimiçi @elseif ($other?->last_seen_at) {{ $other->last_seen_at->locale('tr')->diffForHumans() }} aktifti @else Aktiflik bilgisi yok @endif</span>
                    </span>
                </button>
                <div class="dropdown dropdown-md pm-dialog__header-menu">
                    <button class="pm-dialog__header-more" type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" aria-label="Sohbet seçenekleri"><svg class="pm-dialog__vertical-dots" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="5" r="1.6" fill="currentColor"/><circle cx="12" cy="12" r="1.6" fill="currentColor"/><circle cx="12" cy="19" r="1.6" fill="currentColor"/></svg></button>
                    <ul class="pm__dropdown dropdown-menu dropdown-menu-end">
                        <li class="dropdown-menu-item dropdown-menu-item-sm"><button type="button" data-open-user-info>Kullanıcı bilgisi</button></li>
                        <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="#dialog-message">Bir mesaj yazın</a></li>
                        <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('messenger.index') }}">Mesaj listesi</a></li>
                        <li class="pm__dropdown-limit"><div class="pm__limit-label">{{ $messageUsagePercent }}% / (50 mesaj)</div><div class="pm_progress_bar" role="progressbar" aria-label="Mesaj kullanım oranı" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $messageUsagePercent }}"><span style="width: {{ $messageUsagePercent }}%"></span></div></li>
                    </ul>
                </div>
            </header>

            <section class="pm-user-info" data-user-info hidden aria-label="Kullanıcı bilgileri">
    <div class="pm-user-info__top">
        <button type="button" data-close-user-info aria-label="Kapat" class="pm-user-info__icon"><svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
        <strong>Kullanıcı bilgisi</strong>
        <a href="{{ route('profiles.show', $other) }}" class="pm-user-info__icon" aria-label="Profili aç"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 7a4 4 0 1 0 0 8 4 4 0 0 0 0-8ZM19 8v6M16 11h6"/></svg></a>
    </div>
    <div class="pm-user-info__profile">
        <img src="{{ $other->avatarUrl() }}" alt="{{ $other->displayName() }}">
        <h3>{{ $other->displayName() }}</h3>
        <p>{{ '@'.$other->username }}</p>
        
    </div>
    <div class="pm-user-info__menu" aria-label="Sohbet seçenekleri">
        <a class="pm-user-info__menu-row" href="{{ route('profiles.show', $other) }}"><span class="pm-user-info__menu-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c.5-3.2 3-5 7-5s6.5 1.8 7 5"/></svg></span><span>Profili görüntüle</span><svg class="pm-user-info__chevron" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></a>
        <button class="pm-user-info__menu-row" type="button" data-info-tab="media"><span class="pm-user-info__menu-icon"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="3"/><circle cx="8.5" cy="9" r="1.5"/><path d="m21 15-5-5L5 20"/></svg></span><span>Paylaşılan medya</span><svg class="pm-user-info__chevron" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></button>
        <button class="pm-user-info__menu-row" type="button" data-info-tab="files"><span class="pm-user-info__menu-icon"><svg viewBox="0 0 24 24"><path d="M13 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10z"/><path d="M13 3v7h7M8 14h8M8 17h6"/></svg></span><span>Dosyalar</span><svg class="pm-user-info__chevron" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></button>
        <button class="pm-user-info__menu-row" type="button" data-info-tab="links"><span class="pm-user-info__menu-icon"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.1 0l3-3A5 5 0 0 0 13 2.9l-1.7 1.7M14 11a5 5 0 0 0-7.1 0l-3 3a5 5 0 0 0 7.1 7.1l1.7-1.7"/></svg></span><span>Bağlantılar</span><svg class="pm-user-info__chevron" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></button>
        <button class="pm-user-info__menu-row" type="button" data-info-tab="gif"><span class="pm-user-info__menu-icon"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="M9 10H7v4h2v-1M13 10v4M16 10h2"/></svg></span><span>GIF ve ses içerikleri</span><svg class="pm-user-info__chevron" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg></button>
    </div>
    <div class="pm-user-info__empty" data-info-empty>Henüz paylaşılan medya yok</div>
</section>

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

            <form action="{{ route('messenger.messages.store', $conversation) }}" method="POST" class="pm-dialog__composer pm-composer">
                @csrf
                <div class="pm-composer__bar">
                    <span class="pm-composer__attachment" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="m21.4 11.1-8.9 8.9a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7l-9.2 9.2a2 2 0 0 1-2.8-2.8l8.5-8.5"/></svg>
                    </span>
                    <label class="visually-hidden" for="dialog-message">Mesajın</label>
                    <textarea id="dialog-message" name="body" rows="1" maxlength="5000" required placeholder="Message">{{ old('body') }}</textarea>
                    <div class="pm-composer__emoji-wrap">
                        <button class="pm-composer__emoji" type="button" data-emoji-toggle aria-label="Emoji ekle">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M8.5 14.2s1.2 2 3.5 2 3.5-2 3.5-2M9 9.5h.01M15 9.5h.01"/></svg>
                        </button>
                        <div class="pm-composer__emoji-picker" data-emoji-picker hidden>
                            @foreach (['😀','😂','🥰','😍','😉','😊','🤔','👍','❤️','🔥','🎉','🙏'] as $emoji)
                                <button type="button" data-insert-emoji="{{ $emoji }}">{{ $emoji }}</button>
                            @endforeach
                        </div>
                    </div>
                    <button class="pm-composer__send" type="submit" aria-label="Mesajı gönder">
                        <svg viewBox="0 0 24 24"><path d="M12 3a7 7 0 0 0-7 7v4a3 3 0 0 0 3 3h1v-7H7v4a1 1 0 0 1-1-1v-3a6 6 0 0 1 12 0v3a1 1 0 0 1-1 1v-4h-2v7h1a3 3 0 0 0 3-3v-4a7 7 0 0 0-7-7Z"/><path d="M9 20h6"/></svg>
                    </button>
                </div>
                @error('body')
                    <p class="small color-red">{{ $message }}</p>
                @enderror
            </form>
        </section>
    </article>
@endsection

@push('scripts')
    <script>
        (() => {
            const userInfo = document.querySelector('[data-user-info]');
            document.querySelectorAll('[data-open-user-info]').forEach((button) => button.addEventListener('click', () => {
                if (userInfo) userInfo.hidden = false;
            }));
            document.querySelector('[data-close-user-info]')?.addEventListener('click', () => {
                if (userInfo) userInfo.hidden = true;
            });
            const infoTabLabels = { media: 'Henüz paylaşılan medya yok', files: 'Henüz paylaşılan dosya yok', links: 'Henüz paylaşılan bağlantı yok', music: 'Henüz paylaşılan müzik yok', gif: 'Henüz GIF paylaşılmamış', voice: 'Henüz sesli mesaj yok' };
            document.querySelectorAll('[data-info-tab]').forEach((tab) => tab.addEventListener('click', () => {
                document.querySelectorAll('[data-info-tab]').forEach((item) => item.classList.toggle('is-active', item === tab));
                const empty = document.querySelector('[data-info-empty]');
                if (empty) empty.textContent = infoTabLabels[tab.dataset.infoTab] || infoTabLabels.media;
            }));

            const textarea = document.querySelector('#dialog-message');
            const emojiPicker = document.querySelector('[data-emoji-picker]');
            document.querySelector('[data-emoji-toggle]')?.addEventListener('click', () => {
                if (emojiPicker) emojiPicker.hidden = !emojiPicker.hidden;
            });
            emojiPicker?.querySelectorAll('[data-insert-emoji]').forEach((button) => button.addEventListener('click', () => {
                if (!textarea) return;
                const emoji = button.dataset.insertEmoji || '';
                const start = textarea.selectionStart ?? textarea.value.length;
                const end = textarea.selectionEnd ?? start;
                textarea.setRangeText(emoji, start, end, 'end');
                textarea.focus();
                emojiPicker.hidden = true;
            }));
            document.addEventListener('click', (event) => {
                if (!event.target.closest('.pm-composer__emoji-wrap') && emojiPicker) emojiPicker.hidden = true;
            });
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
