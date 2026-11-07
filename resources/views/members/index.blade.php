@extends('layouts.app')

@section('title', 'Üyeler')

@section('content')
    <article class="card card-body">
        <header class="card-header"><h1 class="h3 card-title">Yazar sıralaması</h1><p class="small color-gray">Topluluğun en aktif yazarları</p></header>
        <ol class="user-rating__list rating-users">
            @forelse ($members as $member)
                <li class="user-card">
                    <a href="{{ route('profiles.show', $member) }}" class="user-box">
                        <div class="avatar">
                            <img src="{{ $member->avatarUrl() }}" alt="" width="40" height="40" loading="lazy" decoding="async">
                        </div>
                        <div class="user-box-content">
                            <span class="name">
                                <span class="name-text">{{ $member->displayName() }}</span>
                                @if ($member->hasVerifiedBadge())
                                    <span class="verify" role="img" aria-label="Doğrulanmış profil" data-tooltip="Doğrulanmış profil">
                                        <x-repost-icon name="verify" size="14" />
                                    </span>
                                @endif
@if ($member)
    <x-company-verified-badge size="14" />
@endif
                            </span>
                            <span class="meta">{{ number_format($member->posts_count) }} yazı</span>
                        </div>
                    </a>
                </li>
            @empty
                <li class="user-card"><span class="small color-gray">Henüz üye yok.</span></li>
            @endforelse
        </ol>
        <div class="card-footer">
            <div class="allert">Sayfada en çok yazı yayımlayan 50 üye gösterilir.</div>
        </div>
        @include('layouts._pagination', ['paginator' => $members])
    </article>
@endsection
