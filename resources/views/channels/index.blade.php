@extends('layouts.app')

@section('title', 'Kanallar')

@section('content')
    <section class="subscriptions__section card card-body">
        <header class="subscriptions__header"><h1 class="h3">Kanallar</h1><p class="small color-gray">Topluluk ilgi alanlarını keşfet, kanalları incele.</p></header>
        @if ($channels->isNotEmpty())
            <ul class="subscriptions__channels">
                @foreach ($channels as $channel)
                    <li>
                        <article class="subchannel">
                            <a href="{{ route('channels.show', $channel) }}" class="subchannel__icon" aria-label="{{ $channel->name }} kategorisi">
                                <x-repost-icon name="image" size="18" />
                            </a>
                            <a href="{{ route('channels.show', $channel) }}" class="subchannel__name">{{ $channel->name }}</a>
                            <div class="subchannel__count">{{ number_format($channel->posts_count) }} yazı</div>
                        </article>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="allert">Henüz kategori yok.</div>
        @endif
        @if (auth()->user()?->is_admin)
            <div class="subscriptions__action">
                <a href="{{ route('channels.create') }}" class="link-accent">Kategori oluştur</a>
            </div>
        @elseif (auth()->check())
            <div class="allert">Kategori oluşturma yalnızca yöneticilere açıktır.</div>
        @endif
        @include('layouts._pagination', ['paginator' => $channels])
    </section>
@endsection
