@extends('layouts.app')

@section('title', 'Kanallar')

@section('content')
    <section class="subscriptions__section card card-body">
        <h1 class="h3">Kanallar</h1>

        @if ($channels->isNotEmpty())
            <ul class="subscriptions__channels">
                @foreach ($channels as $channel)
                    <li>
                        <article class="subchannel">
                            <a
                                href="{{ route('channels.show', $channel) }}"
                                class="subchannel__icon"
                                aria-label="{{ $channel->name }} kanalı"
                                title="{{ $channel->name }}"
                            >
                                @if ($channel->image_path)
                                    <img
                                        src="{{ asset('storage/'.$channel->image_path) }}"
                                        alt=""
                                        width="40"
                                        height="40"
                                        loading="lazy"
                                    >
                                @else
                                    <x-repost-icon name="image" size="18" />
                                @endif
                            </a>

                            <a href="{{ route('channels.show', $channel) }}" class="subchannel__name">
                                {{ $channel->name }}
                            </a>

                            <div class="subchannel__count">
                                {{ number_format($channel->subscribers_count ?? 0) }} abone
                            </div>

                            @auth
                                <form class="subchannel__subscribe-form" action="{{ route('channels.subscribe', $channel) }}" method="POST">
                                    @csrf
                                    <button
                                        class="btn {{ ($channel->is_subscribed ?? false) ? 'btn-secondary' : 'btn-primary' }} btn-sm btn-rounded"
                                        type="submit"
                                    >
                                        {{ ($channel->is_subscribed ?? false) ? 'Abonelikten çık' : 'Abone ol' }}
                                    </button>
                                </form>
                            @else
                                <a class="btn btn-secondary btn-sm btn-rounded subchannel__subscribe-link" href="{{ route('login') }}">
                                    Abone ol
                                </a>
                            @endauth
                        </article>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="subscriptions__none">Henüz kanal bulunmuyor.</div>
        @endif

        @include('layouts._pagination', ['paginator' => $channels])
    </section>
@endsection
