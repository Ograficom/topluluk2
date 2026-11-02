@extends('layouts.app')

@section('title', 'Mesajlar')

@section('content')
    <article class="pm card card-body">
        <header class="card-header"><h1 class="h3">Mesajlar</h1><p class="small color-gray">Üyelerle özel konuşmaların.</p></header>
        <div class="widget__content widget__content--bleed">
            <ul class="widget__list widget__list--separator">
                @forelse ($conversations as $conversation)
                    @php($person = $conversation->participants->first())
                    <li class="user-card">
                        <a class="user-box" href="{{ route('messenger.show', $conversation) }}">
                            <span class="avatar"><img src="{{ $person?->avatarUrl() ?? asset('assets/img/noavatar.png') }}" alt="" width="44" height="44"></span>
                            <span class="user-box-content">
                                <strong class="name">{{ $person?->displayName() ?? 'OgraFi üyesi' }}</strong>
                                <span class="meta">{{ $conversation->latestMessage?->body ? \Illuminate\Support\Str::limit($conversation->latestMessage->body, 90) : 'Henüz mesaj yok' }}</span>
                            </span>
                            <span class="small color-gray">{{ $conversation->latestMessage?->created_at?->diffForHumans() }}</span>
                        </a>
                    </li>
                @empty
                    <li class="card-body empty-state">
                        <h2 class="h4">Henüz konuşman yok</h2>
                        <p class="color-gray">Bir üyenin profiline girip Mesaj gönder düğmesini kullanabilirsin.</p>
                        <a href="{{ route('members.index') }}" class="btn btn-primary btn-md">Üyeleri keşfet</a>
                    </li>
                @endforelse
            </ul>
        </div>
        {{ $conversations->links() }}
    </article>
@endsection
