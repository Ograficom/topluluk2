@extends('layouts.app')

@section('title', 'Üyeler')

@push('head')
    <style>
        .members-ranking-card {
            position: relative;
        }

        .members-ranking-card .card-header {
            padding-right: 72px;
        }

        .members-ranking-card__tasks {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 2;
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border: 1px solid #e6ebf2;
            border-radius: 12px;
            background: #fff;
            color: #1677ff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, .08);
            text-decoration: none;
            transition: transform .18s ease, background-color .18s ease, box-shadow .18s ease;
        }

        .members-ranking-card__tasks:hover,
        .members-ranking-card__tasks:focus-visible {
            background: #f3f8ff;
            color: #0b63d1;
            box-shadow: 0 6px 18px rgba(22, 119, 255, .16);
            transform: translateY(-1px);
        }

        .members-ranking-card__tasks:focus-visible {
            outline: 3px solid rgba(22, 119, 255, .22);
            outline-offset: 2px;
        }

        @media (max-width: 575.98px) {
            .members-ranking-card__tasks {
                top: 16px;
                right: 16px;
                width: 40px;
                height: 40px;
            }

            .members-ranking-card .card-header {
                padding-right: 64px;
            }
        }
    </style>
@endpush

@section('content')
    <article class="card card-body members-ranking-card">
        <a href="{{ route('tasks.index') }}" class="members-ranking-card__tasks" aria-label="Görevler ve liderlik" title="Görevler ve liderlik">
            <svg viewBox="0 0 24 24" width="25" height="25" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M18 9h1.5a1 1 0 0 0 0-5H18"/>
                <path d="M6 9H4.5a1 1 0 0 1 0-5H6"/>
                <path d="M6 9a6 6 0 0 0 12 0V3a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1Z"/>
                <path d="M10 14.66v1.626a2 2 0 0 1-.976 1.696A5 5 0 0 0 7 21.978"/>
                <path d="M14 14.66v1.626a2 2 0 0 0 .976 1.696A5 5 0 0 1 17 21.978"/>
                <path d="M4 22h16"/>
            </svg>
        </a>
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
@if ($member->hasVerifiedCompanyBadge())
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
