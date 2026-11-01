@extends('layouts.app')

@section('title', $user->name.' — OgraFi')
@section('meta_description', $user->name.' adlı kullanıcının OgraFi profili ve paylaşımları.')

@section('content')
    <div data-ografi-layout-container="profile">
    <section class="profile card card-body repost-profile">
        <div class="repost-profile-cover" @if($user->cover_photo_url) style="background-image:url('{{ $user->cover_photo_url }}')" @endif></div>
        <div class="repost-profile-main" data-ografi-layout-section="profile_header">
            <div class="repost-profile-avatar"><img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" width="104" height="104"></div>
            <div class="repost-profile-actions">
                @if(auth()->check() && auth()->id() !== $user->id)
                    <form method="POST" action="{{ route('users.follow', $user->username) }}">@csrf<button class="btn {{ $isFollowing ? 'btn-secondary' : 'btn-primary' }} btn-rounded" type="submit">{{ $isFollowing ? 'Takip ediliyor' : 'Takip et' }}</button></form>
                    @if($canStartChat)<a class="btn btn-secondary btn-rounded" href="{{ url('/messages/'.$user->username) }}">Mesaj gönder</a>@endif
                @elseif(auth()->id() === $user->id)
                    <a class="btn btn-secondary btn-rounded" href="{{ url('/dashboard/profile') }}">Profili düzenle</a>
                @endif
            </div>
            <h1 class="repost-profile-name">{{ $user->name }} @if($user->is_verified)<span class="verify" aria-label="Doğrulanmış hesap"><svg width="16" height="16"><use href="#verify"/></svg></span>@endif</h1>
            <p class="color-gray">{{ '@'.$user->username }}</p>
            @if($user->bio)<p class="repost-profile-bio">{{ $user->bio }}</p>@endif
            <div class="repost-profile-stats">
                <span><strong>{{ number_format((int)$postsCount) }}</strong> gönderi</span>
                <span><strong>{{ number_format((int)$user->followers_count) }}</strong> takipçi</span>
                <span><strong>{{ number_format((int)$user->followings_count) }}</strong> takip</span>
                <span><strong>{{ number_format((int)$totalViews) }}</strong> görüntülenme</span>
            </div>
        </div>
        <nav class="repost-profile-tabs" data-ografi-layout-section="profile_tabs" aria-label="Profil bölümleri">
            <a class="{{ $activeTab === 'stories' ? 'is-active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'stories']) }}">Gönderiler</a>
            <a class="{{ $activeTab === 'comments' ? 'is-active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'comments']) }}">Yorumlar</a>
            <a class="{{ $activeTab === 'followers' ? 'is-active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'followers']) }}">Takipçiler</a>
            @if($canViewFollowings)<a class="{{ $activeTab === 'followings' ? 'is-active' : '' }}" href="{{ request()->fullUrlWithQuery(['tab' => 'followings']) }}">Takip edilenler</a>@endif
        </nav>
    </section>

    <div data-ografi-layout-section="profile_content">
    @if($hasBlockedUser || $isBlockedByUser)
        <section class="card card-body"><p>Bu profil şu anda görüntülenemiyor.</p></section>
    @elseif($activeTab === 'stories')
        @forelse($posts as $post)
            @include('partials.post-card', ['post' => $post])
        @empty
            <section class="card card-body"><p class="color-gray">Henüz paylaşım yok.</p></section>
        @endforelse
    @elseif($activeTab === 'comments')
        <section class="card card-body">
            <h2 class="h5">Yorumlar</h2>
            @forelse($comments as $comment)
                <article class="repost-comment"><p>{{ strip_tags($comment->content) }}</p><a href="{{ $comment->post ? url('/tr/'.$comment->post->slug) : '#' }}">{{ $comment->post?->title ?? 'Gönderi' }}</a></article>
            @empty<p class="color-gray">Henüz yorum yok.</p>@endforelse
        </section>
    @elseif($activeTab === 'followers')
        <section class="card card-body"><h2 class="h5">Takipçiler</h2><ul class="widget__list">@forelse($followers as $follower)<li class="user-card"><a class="user-box" href="{{ url('/u/'.$follower->username) }}"><span class="avatar"><img src="{{ $follower->profile_photo_url }}" alt="" width="40" height="40"></span><span class="user-box-content"><strong class="name">{{ $follower->name }}</strong><span class="meta">@{{ $follower->username }}</span></span></a></li>@empty<li class="color-gray">Henüz takipçi yok.</li>@endforelse</ul></section>
    @elseif($activeTab === 'followings')
        <section class="card card-body"><h2 class="h5">Takip edilenler</h2><ul class="widget__list">@forelse($followings as $following)<li class="user-card"><a class="user-box" href="{{ url('/u/'.$following->username) }}"><span class="avatar"><img src="{{ $following->profile_photo_url }}" alt="" width="40" height="40"></span><span class="user-box-content"><strong class="name">{{ $following->name }}</strong><span class="meta">@{{ $following->username }}</span></span></a></li>@empty<li class="color-gray">Henüz takip edilen üye yok.</li>@endforelse</ul></section>
    @endif
    </div>
    </div>
@endsection
