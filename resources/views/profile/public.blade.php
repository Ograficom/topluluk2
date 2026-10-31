@extends('layouts.repost')

@section('title', $user->name . ' — OgraFi')
@section('meta_description', $user->bio ?: $user->name . ' kullanıcısının OgraFi profili.')

@section('content')
    <section class="card repost-profile-card">
        <div class="repost-profile-cover" style="background-image:url('{{ $user->cover_photo_url }}')"></div>
        <div class="card-body repost-profile-body">
            <div class="repost-profile-avatar">
                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" width="104" height="104">
            </div>
            <div class="repost-profile-heading">
                <div>
                    <h1>{{ $user->name }}</h1>
                    <p class="repost-profile-username">@{{ $user->username }}</p>
                </div>
                <div class="repost-profile-actions">
                    @if($isOwnProfile)
                        <a href="{{ route('profile.edit') }}" class="btn btn-secondary btn-rounded">Profili düzenle</a>
                    @elseif(!$hasBlockedUser && !$isBlockedByUser)
                        @auth
                            <form method="POST" action="{{ route('users.follow', $user->username) }}">
                                @csrf
                                <button class="btn {{ $isFollowing ? 'btn-secondary' : 'btn-primary' }} btn-rounded" type="submit">{{ $isFollowing ? 'Takip ediliyor' : 'Takip et' }}</button>
                            </form>
                            @if($canStartChat)
                                <a href="{{ route('messages.show', $user->username) }}" class="btn btn-secondary btn-rounded">Mesaj gönder</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary btn-rounded">Takip et</a>
                        @endauth
                    @endif
                    @auth
                        @if(!$isOwnProfile)
                            <div class="dropdown dropdown-md">
                                <button type="button" class="btn btn-secondary btn-circle" data-dropdown-toggle aria-expanded="false" aria-label="Profil seçenekleri">•••</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="dropdown-menu-item"><a href="{{ route('users.report.form', $user->username) }}">Profili bildir</a></li>
                                    <li class="dropdown-menu-item">
                                        <form method="POST" action="{{ route('users.block', $user->username) }}">
                                            @csrf
                                            <button type="submit">{{ $hasBlockedUser ? 'Engeli kaldır' : 'Kullanıcıyı engelle' }}</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>

            @if(filled($user->bio))
                <p class="repost-profile-bio">{{ $user->bio }}</p>
            @endif
            <div class="repost-profile-meta">
                @if(filled($user->location))<span>{{ $user->location }}</span>@endif
                @if(filled($user->company))<span>{{ $user->company }}</span>@endif
                @if(filled($user->website_url))<a href="{{ $user->website_url }}" target="_blank" rel="noopener noreferrer">{{ parse_url($user->website_url, PHP_URL_HOST) ?: $user->website_url }}</a>@endif
            </div>

            <div class="repost-profile-stats">
                <span><strong>{{ number_format((int) $postsCount) }}</strong> gönderi</span>
                <span><strong>{{ number_format((int) $user->followers_count) }}</strong> takipçi</span>
                <span><strong>{{ number_format((int) $user->followings_count) }}</strong> takip</span>
                <span><strong>{{ number_format((int) $totalViews) }}</strong> görüntülenme</span>
            </div>

            @if($earnedBadges?->isNotEmpty())
                <div class="repost-profile-badges">
                    @foreach($earnedBadges as $badge)
                        <span>{{ $badge->name ?? $badge->title ?? 'Rozet' }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if($hasBlockedUser || $isBlockedByUser)
        <div class="card card-body repost-empty-state">
            <h2>Bu profil şu anda görüntülenemiyor</h2>
            <p>Engelleme ayarları nedeniyle profil içerikleri gösterilmiyor.</p>
        </div>
    @else
        <nav class="repost-profile-tabs" aria-label="Profil sekmeleri">
            <a href="{{ route('users.show', [$user->username, 'tab' => 'stories']) }}" class="{{ $activeTab === 'stories' ? 'is-active' : '' }}">Gönderiler</a>
            <a href="{{ route('users.show', [$user->username, 'tab' => 'comments']) }}" class="{{ $activeTab === 'comments' ? 'is-active' : '' }}">Yorumlar</a>
            <a href="{{ route('users.show', [$user->username, 'tab' => 'followers']) }}" class="{{ $activeTab === 'followers' ? 'is-active' : '' }}">Takipçiler</a>
            @if($canViewFollowings)
                <a href="{{ route('users.show', [$user->username, 'tab' => 'followings']) }}" class="{{ $activeTab === 'followings' ? 'is-active' : '' }}">Takip ettikleri</a>
            @endif
        </nav>

        @if($activeTab === 'stories')
            @forelse($posts as $post)
                @include('components.repost-post-card', ['post' => $post])
            @empty
                <div class="card card-body repost-empty-state">Henüz gönderi paylaşılmamış.</div>
            @endforelse
        @elseif($activeTab === 'comments')
            @forelse($comments as $comment)
                <article class="card card-body repost-comment">
                    <p>{{ $comment->content }}</p>
                    @if($comment->post)
                        <a href="{{ route('blog.post', $comment->post->slug) }}">{{ $comment->post->title }}</a>
                    @endif
                    <time class="meta">{{ $comment->created_at?->diffForHumans() }}</time>
                </article>
            @empty
                <div class="card card-body repost-empty-state">Henüz yorum yok.</div>
            @endforelse
        @elseif($activeTab === 'followers')
            <div class="repost-people-list">
                @forelse($followers as $person)
                    <a class="card card-body user-box" href="{{ route('users.show', $person->username) }}">
                        <span class="avatar"><img src="{{ $person->profile_photo_url }}" alt="" width="42" height="42"></span>
                        <span class="user-box-content"><strong class="name">{{ $person->name }}</strong><span class="meta">@{{ $person->username }}</span></span>
                    </a>
                @empty
                    <div class="card card-body repost-empty-state">Henüz takipçisi yok.</div>
                @endforelse
            </div>
        @elseif($activeTab === 'followings')
            <div class="repost-people-list">
                @forelse($followings as $person)
                    <a class="card card-body user-box" href="{{ route('users.show', $person->username) }}">
                        <span class="avatar"><img src="{{ $person->profile_photo_url }}" alt="" width="42" height="42"></span>
                        <span class="user-box-content"><strong class="name">{{ $person->name }}</strong><span class="meta">@{{ $person->username }}</span></span>
                    </a>
                @empty
                    <div class="card card-body repost-empty-state">Takip edilen kullanıcı bulunmuyor.</div>
                @endforelse
            </div>
        @endif
    @endif
@endsection
