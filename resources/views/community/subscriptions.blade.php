@extends('layouts.app')

@section('title', 'Abonelikler')

@section('content')
    <article class="feed subscriptions">
        <section class="subscriptions__section card card-body">
            <header>
                <h1 class="h4">Kanal abonelikleri ({{ $channels->count() }})</h1>
            </header>
            @if ($channels->isNotEmpty())
                <ul class="subscriptions__channels">
                    @foreach ($channels as $channel)
                        <li>
                            <article class="subchannel">
                                <a class="subchannel__icon" href="{{ route('channels.show', $channel) }}" aria-label="{{ $channel->name }} kanalı">
                                    <x-repost-icon name="image" size="18" />
                                </a>
                                <a class="subchannel__name" href="{{ route('channels.show', $channel) }}">{{ $channel->name }}</a>
                                <div class="subchannel__count">{{ number_format($channel->subscribers_count) }} abone</div>
                                <form action="{{ route('channels.subscribe', $channel) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-secondary btn-sm btn-rounded" type="submit">Abonelikten çık</button>
                                </form>
                            </article>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="small color-gray">Henüz bir kanala abone değilsin.</p>
            @endif
            <div class="subscriptions__action">
                <a class="link-accent" href="{{ route('channels.index') }}">Tüm kanalları keşfet</a>
            </div>
        </section>

        <section class="subscriptions__section card card-body">
            <header>
                <h2 class="h4">Yazar abonelikleri ({{ $authors->count() }})</h2>
            </header>
            @if ($authors->isNotEmpty())
                <ul class="subscriptions__users">
                    @foreach ($authors as $author)
                        <li class="user-card user-card-sub">
                            <a class="user-box" href="{{ route('profiles.show', $author) }}">
                                <span class="avatar">
                                    <img src="{{ $author->avatarUrl() }}" alt="" width="40" height="40" loading="lazy">
                                </span>
                                <span class="user-box-content">
                                    <strong class="name">
                                        <span class="name-text">{{ $author->displayName() }}</span>
                                        @if ($author->hasVerifiedBadge())
                                            <span class="verify" role="img" aria-label="Doğrulanmış hesap"><x-repost-icon name="verify" size="14" /></span>
                                        @endif
                                    </strong>
                                    <span class="meta">{{ number_format($author->posts_count) }} yazı</span>
                                </span>
                            </a>
                            <form action="{{ route('users.follow', $author) }}" method="POST">
                                @csrf
                                <button class="btn btn-secondary btn-sm btn-rounded user-card__subscribe" type="submit">Takibi bırak</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="small color-gray">Henüz bir yazarı takip etmiyorsun.</p>
            @endif
            <div class="subscriptions__action">
                <a class="link-accent" href="{{ route('members.index') }}">Yazarları keşfet</a>
            </div>
        </section>
    </article>
@endsection
