@extends('layouts.app')

@section('title', 'Mesajlar')

@section('content')
    <article class="pm card card-body">
        <header class="pm__header">
            <h1 class="h3">Mesajlar</h1>
            <div class="dropdown dropdown-md">
                <button class="btn btn-secondary btn-xs btn-rounded" type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" aria-controls="pmMenu" aria-label="Mesaj menüsünü aç">
                    <x-repost-icon name="more" size="18" class="color-gray" />
                </button>
                <ul class="pm__dropdown dropdown-menu dropdown-menu-end" id="pmMenu">
                    <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('members.index') }}">Yeni mesaj başlat</a></li>
                    <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.index') }}">Akışa dön</a></li>
                </ul>
            </div>
        </header>

        <div class="pm__list-wrap">
            <form action="{{ route('messenger.index') }}" id="pmlist" method="GET">
                <div class="pm-list">
                    @forelse ($conversations as $conversation)
                        @php
                            $person = $conversation->participants->first();
                            $lastMessage = $conversation->latestMessage;
                            $isUnread = (int) $conversation->unread_count > 0;
                            $preview = $lastMessage?->body
                                ? \Illuminate\Support\Str::limit($lastMessage->body, 110)
                                : 'Henüz mesaj gönderilmedi';
                        @endphp
                        <article class="pm-item {{ $isUnread ? 'is-unread' : 'is-read' }}" data-pm-id="{{ $conversation->id }}">
                            <a class="pm-item__link" href="{{ route('messenger.show', $conversation) }}" aria-label="{{ $person?->displayName() ?? 'Üye' }} ile konuşmayı aç"></a>
                            <a class="pm-item__avatar" href="{{ $person ? route('profiles.show', $person) : route('messenger.index') }}" aria-label="{{ $person?->displayName() ?? 'Üye' }} profili">
                                <img src="{{ $person?->avatarUrl() ?? asset('assets/img/noavatar.png') }}" alt="{{ $person?->displayName() ?? 'Üye' }}" width="44" height="44" loading="lazy">
                                @if ($person?->isOnline())
                                    <span class="pm-item__online is-online" title="Çevrimiçi"></span>
                                @endif
                            </a>
                            <div class="pm-item__body">
                                <a class="pm-item__name" href="{{ route('messenger.show', $conversation) }}">{{ $person?->displayName() ?? 'OgraFi üyesi' }}</a>
                                <div class="pm-item__preview">
                                    <span class="pm-item__subject">Özel mesaj</span>
                                    <span class="pm-item__message">
                                        @if ($lastMessage && (int) $lastMessage->user_id === (int) auth()->id())
                                            Sen: 
                                        @endif
                                        {{ $preview }}
                                    </span>
                                    @if ($lastMessage)
                                        <time datetime="{{ $lastMessage->created_at->toAtomString() }}">{{ $lastMessage->created_at->format('d.m.Y, H:i') }}</time>
                                    @endif
                                </div>
                            </div>
                            <div class="dropdown pm-item__dropdown">
                                <button class="pm-item__more" type="button" data-dropdown-toggle aria-expanded="false" aria-haspopup="menu" aria-label="Konuşma seçenekleri">
                                    <x-repost-icon name="more" size="18" />
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('messenger.show', $conversation) }}">Konuşmayı aç</a></li>
                                    @if ($person)
                                        <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('profiles.show', $person) }}">Profili görüntüle</a></li>
                                    @endif
                                </ul>
                            </div>
                            @if ($isUnread)
                                <span class="pm-item__unread" aria-label="{{ $conversation->unread_count }} okunmamış mesaj"></span>
                            @endif
                        </article>
                    @empty
                        <div class="card-body empty-state">
                            <h2 class="h4">Henüz konuşman yok</h2>
                            <p class="color-gray">Bir üyenin profiline girip Mesaj gönder düğmesini kullanabilir veya yeni bir konuşma başlatabilirsin.</p>
                            <a href="{{ route('members.index') }}" class="btn btn-primary btn-md">Üyeleri keşfet</a>
                        </div>
                    @endforelse
                </div>

                <span class="pm-no-messages" data-pm-empty hidden>Okunmamış mesaj yok</span>
                <label class="pm-unread-filter form-check form-switch">
                    <span class="pm-unread-filter__label">
                        <x-repost-icon name="message" size="20" />
                        <span>Sadece okunmamışlar</span>
                    </span>
                    <input class="form-check-input pm-unread-filter__input" type="checkbox" data-pm-unread-filter role="switch" aria-label="Sadece okunmamış mesajları göster">
                </label>
            </form>
        </div>
        {{ $conversations->links() }}
    </article>
@endsection

@push('scripts')
    <script>
        (() => {
            const filter = document.querySelector('[data-pm-unread-filter]');
            const list = document.querySelector('.pm-list');
            const empty = document.querySelector('[data-pm-empty]');
            if (!filter || !list) return;

            const items = Array.from(list.querySelectorAll('.pm-item'));
            const applyFilter = () => {
                let visible = 0;
                items.forEach((item) => {
                    const show = !filter.checked || item.classList.contains('is-unread');
                    item.hidden = !show;
                    if (show) visible++;
                });
                if (empty) empty.hidden = !filter.checked || visible > 0;
            };
            filter.addEventListener('change', applyFilter);
            applyFilter();
        })();
    </script>
@endpush
