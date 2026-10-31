@extends('layouts.repost')

@section('title', 'Engellenen kullanıcılar — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">GİZLİLİK</span><h1>Engellenen kullanıcılar</h1><p>Engelini kaldırmak istediğin üyeleri buradan yönet.</p></div></div>
    <div class="card repost-message-list">
        @forelse(auth()->user()->blockedUsers as $person)
            <div class="repost-message-row">
                <a href="{{ route('users.show', $person->username) }}" class="user-box"><span class="avatar"><img src="{{ $person->profile_photo_url }}" alt="" width="42" height="42"></span><span class="user-box-content"><strong class="name">{{ $person->name }}</strong><span class="meta">@{{ $person->username }}</span></span></a>
                <form method="POST" action="{{ route('users.block', $person->username) }}">@csrf<button class="btn btn-secondary btn-rounded" type="submit">Engeli kaldır</button></form>
            </div>
        @empty
            <div class="repost-empty-state">Engellediğin kullanıcı bulunmuyor.</div>
        @endforelse
    </div>
@endsection
