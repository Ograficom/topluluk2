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
                    <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('members.index') }}">Üye bul</a></li>
                    <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('posts.index') }}">Akışa dön</a></li>
                </ul>
            </div>
        </header>

        <div class="pm__list-wrap">
            <div class="pm-list">
                @forelse ($conversations as $conversation)
                    @php
                        $person = $conversation->participants->first();
                        $lastMessage = $conversation->latestMessage;
                        $isUnread = $lastMessage && (int) $lastMessage->user_id !== (int) auth()->id();
                    @endphp
                    <article class="pm-item {{ $isUnread ? 'is-unread' : 'is-read' }}">
                        <a class="pm-item__link" href="{{ route('messenger.show', $conversation) }}" aria-label="{{ $person?->displayName() ?? 'Üye' }} ile konuşmayı aç"></a>
                        <a class="pm-item__avatar" href="{{ $person ? route('profiles.show', $person) : route('messenger.index') }}" aria-label="{{ $person?->displayName() ?? 'Üye' }} profili">
                            <img src="{{ $person?->avatarUrl() ?? asset('assets/img/noavatar.png') }}" alt="" width="44" height="44" loading="lazy">
                        </a>
                        <div class="pm-item__body">
                            <a class="pm-item__name" href="{{ route('messenger.show', $conversation) }}">{{ $person?->displayName() ?? 'OgraFi üyesi' }}</a>
                            <div class="pm-item__preview">
                                <span class="pm-item__subject">Özel konuşma</span>
                                <span class="pm-item__message">{{ $lastMessage?->body ? \Illuminate\Support\Str::limit($lastMessage->body, 100) : 'Henüz mesaj yok' }}</span>
                                @if ($lastMessage)
                                    <time datetime="{{ $lastMessage->created_at->toAtomString() }}">{{ $lastMessage->created_at->diffForHumans() }}</time>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="card-body empty-state">
                        <h2 class="h4">Henüz konuşman yok</h2>
                        <p class="color-gray">Bir üyenin profiline girip Mesaj gönder düğmesini kullanabilirsin.</p>
                        <a href="{{ route('members.index') }}" class="btn btn-primary btn-md">Üyeleri keşfet</a>
                    </div>
                @endforelse
            </div>
            <label class="pm-unread-filter form-check form-switch">
                <span class="pm-unread-filter__label">Sadece okunmamış mesajları göster</span>
                <input class="form-check-input pm-unread-filter__input" type="checkbox" data-pm-unread-filter role="switch" aria-label="Sadece okunmamış mesajları göster">
            </label>
            <span class="pm-no-messages small color-gray" data-pm-empty hidden>Okunmamış mesaj yok</span>
        </div>
        {{ $conversations->links() }}
    </article>
@endsection

@push('scripts')
    <script>
        (() => {
            const filter = document.querySelector('[data-pm-unread-filter]');
            const items = Array.from(document.querySelectorAll('.pm-list .pm-item'));
            const empty = document.querySelector('[data-pm-empty]');

            if (!filter || !items.length) return;

            const applyFilter = () => {
                let visibleCount = 0;

                items.forEach((item) => {
                    const visible = !filter.checked || item.classList.contains('is-unread');
                    item.hidden = !visible;
                    if (visible) visibleCount += 1;
                });

                if (empty) empty.hidden = !filter.checked || visibleCount > 0;
            };

            filter.addEventListener('change', applyFilter);
            applyFilter();
        })();
    </script>
@endpush
