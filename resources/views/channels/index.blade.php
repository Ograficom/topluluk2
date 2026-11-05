@extends('layouts.app')

@section('title', 'Kanallar')

@section('content')
    <article class="feed">
        <section class="subscriptions__section card card-body">
            <h1 class="h3">Kanallar</h1>
            <ul class="subscriptions__channels">
                @forelse ($channels as $channel)
                    <li>
                        <article class="subchannel">
                            <a href="{{ route('channels.show', $channel) }}" class="subchannel__icon" aria-label="{{ $channel->name }} kanalı" title="{{ $channel->name }}">
                                @if ($channel->image_path)
                                    <img src="{{ asset('storage/'.$channel->image_path) }}" alt="" width="50" height="50" loading="lazy">
                                @else
                                    <x-repost-icon name="image" size="18" />
                                @endif
                            </a>
                            <a href="{{ route('channels.show', $channel) }}" class="subchannel__name">{{ $channel->name }}</a>
                            <div class="subchannel__count">
                                {{ number_format($channel->subscribers_count ?? 0) }} {{ ($channel->subscribers_count ?? 0) === 1 ? 'abone' : 'abone' }}
                            </div>

                            @auth
                                <form id="channel-subscribe-{{ $channel->id }}" action="{{ route('channels.subscribe', $channel) }}" method="POST" hidden>
                                    @csrf
                                </form>
                                <button form="channel-subscribe-{{ $channel->id }}" class="btn {{ ($channel->is_subscribed ?? false) ? 'btn-secondary' : 'btn-primary' }} btn-sm btn-rounded" type="submit">
                                    {{ ($channel->is_subscribed ?? false) ? 'Abonelikten çık' : 'Abone ol' }}
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-secondary btn-sm btn-rounded">Abone ol</a>
                            @endauth
                        </article>
                    </li>
                @empty
                    <li class="subscriptions__none">Henüz kanal bulunmuyor.</li>
                @endforelse
            </ul>

            @if (auth()->user()?->is_admin)
                <div class="subscriptions__action">
                    <a href="{{ route('channels.create') }}" class="link-accent">Kanal oluştur</a>
                </div>
            @endif

            @include('layouts._pagination', ['paginator' => $channels])
        </section>
    </article>
@endsection
