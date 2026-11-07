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
                <button class="pm-dialog__identity" type="button" data-open-user-info aria-label="Kullanıcı bilgilerini aç">
                    <img class="pm-dialog__identity-avatar" src="{{ $other?->avatarUrl() ?? asset('assets/img/noavatar.png') }}" alt="{{ $other?->displayName() ?? 'Kullanıcı' }}">
                    <span class="pm-dialog__identity-copy">
                        <strong>{{ $other?->displayName() ?? 'OgraFi üyesi' }}</strong>
                        <span class="pm-dialog__identity-status"><i class="{{ $other?->isOnline() ? 'is-online' : '' }}"></i>{{ $other?->isOnline() ? 'Şu anda çevrimiçi' : 'Özel konuşma' }}</span>
                    </span>
                </button>
                @if ($other)
                    <button class="pm-dialog__settings-button" type="button" data-open-user-info aria-label="Kullanıcı bilgileri ve ayarları">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.7 2.94-.08-.02a1.7 1.7 0 0 0-1.8.68l-.05.07h-3.4l-.03-.08a1.7 1.7 0 0 0-1.48-1.12 1.7 1.7 0 0 0-.9.2l-.08.04-2.94-1.7.02-.08a1.7 1.7 0 0 0-.68-1.8l-.07-.05v-3.4l.08-.03a1.7 1.7 0 0 0 1.12-1.48 1.7 1.7 0 0 0-.2-.9l-.04-.08 1.7-2.94.08.02a1.7 1.7 0 0 0 1.8-.68l.05-.07h3.4l.03.08a1.7 1.7 0 0 0 1.48 1.12 1.7 1.7 0 0 0 .9-.2l.08-.04 2.94 1.7-.02.08a1.7 1.7 0 0 0 .68 1.8l.07.05v3.4l-.08.03a1.7 1.7 0 0 0-1.12 1.48z"></path></svg>
                    </button>
                @endif
                @if ($other)
                    <section class="pm-user-info" data-user-info hidden aria-label="Kullanıcı bilgileri">
                        <div class="pm-user-info__top">
                            <button type="button" data-close-user-info aria-label="Kapat" class="pm-user-info__icon">
                                <svg viewBox="0 0 24 24"><path d="m6 6 12 12M18 6 6 18"/></svg>
                            </button>
                            <strong>Kullanıcı Bilgisi</strong>
                            <a href="{{ route('profiles.show', $other) }}" class="pm-user-info__icon" aria-label="Profili aç">
                                <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 7a4 4 0 1 0 0 8 4 4 0 0 0 0-8ZM19 8v6M16 11h6"/></svg>
                            </a>
                        </div>
                        <div class="pm-user-info__profile">
                            <img src="{{ $other->avatarUrl() }}" alt="{{ $other->displayName() }}">
                            <h3>{{ $other->displayName() }}</h3>
                            <p>{{ '@'.$other->username }}</p>
                            <span class="pm-dialog__status {{ $other->isOnline() ? 'is-online' : '' }}"><i class="pm-dialog__status-dot"></i>{{ $other->isOnline() ? 'Çevrimiçi' : 'OgraFi üyesi' }}</span>
                        </div>
                        <div class="pm-user-info__details">
                            <div><span class="pm-user-info__detail-icon">☎</span><span><strong>{{ '@'.$other->username }}</strong><small>Kullanıcı adı</small></span></div>
                            <div><span class="pm-user-info__detail-icon is-blue">●</span><span><strong>Bildirimler</strong><small>Sohbet bildirimleri</small></span><span class="pm-user-info__static-switch" aria-hidden="true"></span></div>
                        </div>
                        <nav class="pm-user-info__tabs" aria-label="Paylaşılan içerik türleri">
                            <button type="button" class="is-active" data-info-tab="media">Medya</button>
                            <button type="button" data-info-tab="files">Dosyalar</button>
                            <button type="button" data-info-tab="links">Bağlantılar</button>
                            <button type="button" data-info-tab="music">Müzik</button>
                            <button type="button" data-info-tab="gif">GIF</button>
                            <button type="button" data-info-tab="voice">Ses</button>
                        </nav>
                        <div class="pm-user-info__empty" data-info-empty>Henüz paylaşılan medya yok</div>
                    </section>
                @endif
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
