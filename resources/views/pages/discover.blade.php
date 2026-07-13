@extends('layouts.app')

@section('title', __('site.discover_page.title'))
@section('meta_description', __('site.discover_page.meta_description'))
@section('no_container_padding')

@section('content')
    @php
        $communityBadgeColors = ['#ef4444', '#ec4899', '#f97316', '#06b6d4', '#8b5cf6', '#10b981'];
    @endphp

    <style>
        body:has(.discover-page-shell) .layout-main,
        body:has(.discover-page-shell) main,
        body:has(.discover-page-shell) .main-content,
        body:has(.discover-page-shell) .content,
        body:has(.discover-page-shell) .container,
        body:has(.discover-page-shell) .max-w-7xl,
        body:has(.discover-page-shell) .max-w-6xl,
        body:has(.discover-page-shell) .max-w-5xl,
        body:has(.discover-page-shell) .max-w-4xl,
        body:has(.discover-page-shell) .mx-auto {
            width: 100% !important;
            max-width: 100% !important;
        }

        body:has(.discover-page-shell) .layout-main,
        body:has(.discover-page-shell) main,
        body:has(.discover-page-shell) .main-content,
        body:has(.discover-page-shell) .content {
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .discover-page-shell {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .discover-section {
            width: 100% !important;
            max-width: 100% !important;
            border: 0 !important;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: none;
            overflow: hidden;
        }

        .dark .discover-section {
            background: rgb(2 6 23);
        }

        .discover-section__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 14px 18px 8px;
        }

        .discover-section__head .alma-page-title,
        .discover-section__head h2 {
            font-size: 16px !important;
            font-weight: 400 !important;
            line-height: 1.25 !important;
            letter-spacing: 0 !important;
        }

        .discover-section__head a {
            font-size: 13px !important;
            font-weight: 400 !important;
        }

        .discover-section__body {
            padding: 0 18px 10px;
        }

        .discover-compact-list {
            display: grid;
            gap: 0;
        }

        .discover-compact-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            min-height: 0;
            padding: 10px 0;
            border: 0 !important;
            background: transparent;
        }

        .discover-compact-link {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .discover-avatar {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 999px;
            object-fit: cover;
        }

        .discover-avatar-fallback {
            display: flex;
            width: 42px;
            height: 42px;
            min-width: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgb(239 246 255);
            color: #83A2FF;
            font-size: 13px;
            font-weight: 400;
        }

        .dark .discover-avatar-fallback {
            background: rgb(30 41 59);
        }

        .discover-item-name {
            font-size: 14px !important;
            font-weight: 400 !important;
            line-height: 1.2 !important;
            color: rgb(2 6 23);
        }

        .dark .discover-item-name {
            color: #ffffff;
        }

        .discover-item-meta {
            font-size: 12px !important;
            font-weight: 400 !important;
            line-height: 1.2 !important;
        }

        .discover-follow-btn,
        button.discover-follow-btn,
        a.discover-follow-btn {
            display: inline-flex !important;
            min-height: 34px !important;
            height: 34px !important;
            align-items: center !important;
            justify-content: center !important;
            border: 0 !important;
            border-radius: 9999px !important;
            background-color: #f1f5f9 !important;
            padding: 0 16px !important;
            color: #0f172a !important;
            font-size: 13px !important;
            font-weight: 400 !important;
            line-height: 1 !important;
            cursor: pointer !important;
            text-decoration: none !important;
            box-shadow: none !important;
            outline: none !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            transition: background-color .15s ease, color .15s ease, transform .12s ease !important;
        }

        .discover-follow-btn:hover,
        .discover-follow-btn:focus,
        button.discover-follow-btn:hover,
        button.discover-follow-btn:focus,
        a.discover-follow-btn:hover,
        a.discover-follow-btn:focus {
            background-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        .discover-follow-btn:active,
        button.discover-follow-btn:active,
        a.discover-follow-btn:active {
            background-color: #94a3b8 !important;
            color: #0f172a !important;
            transform: scale(.96) !important;
        }

        .dark .discover-follow-btn,
        .dark button.discover-follow-btn,
        .dark a.discover-follow-btn {
            background-color: #334155 !important;
            color: #ffffff !important;
        }

        .dark .discover-follow-btn:hover,
        .dark .discover-follow-btn:focus,
        .dark button.discover-follow-btn:hover,
        .dark button.discover-follow-btn:focus,
        .dark a.discover-follow-btn:hover,
        .dark a.discover-follow-btn:focus {
            background-color: #475569 !important;
            color: #ffffff !important;
        }

        .dark .discover-follow-btn:active,
        .dark button.discover-follow-btn:active,
        .dark a.discover-follow-btn:active {
            background-color: #64748b !important;
            color: #ffffff !important;
        }

        .discover-recommendations {
            width: 100% !important;
            max-width: 100% !important;
            margin: 16px 0 0 0 !important;
            padding: 0 !important;
            overflow: hidden;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: none;
        }

        .dark .discover-recommendations {
            background: rgb(2 6 23);
        }

        .discover-recommendations__head {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            min-height: 50px;
            border-bottom: 1px solid #e5e7eb;
            padding: 0 20px;
            background: #ffffff;
        }

        .dark .discover-recommendations__head {
            border-bottom-color: rgb(30 41 59);
            background: rgb(2 6 23);
        }

        .discover-recommendations__title {
            margin: 0;
            color: #000000;
            font-size: 16px;
            font-weight: 400;
            line-height: 1.2;
        }

        .dark .discover-recommendations__title {
            color: #ffffff;
        }

        .discover-recommendations__list {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .discover-recommendations__item {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        @media (max-width: 640px) {
            body:has(.discover-page-shell) .layout-main,
            body:has(.discover-page-shell) main,
            body:has(.discover-page-shell) .main-content,
            body:has(.discover-page-shell) .content,
            body:has(.discover-page-shell) .container,
            body:has(.discover-page-shell) .max-w-7xl,
            body:has(.discover-page-shell) .max-w-6xl,
            body:has(.discover-page-shell) .max-w-5xl,
            body:has(.discover-page-shell) .max-w-4xl,
            body:has(.discover-page-shell) .mx-auto {
                width: 100% !important;
                max-width: 100% !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .discover-page-shell {
                width: 100vw !important;
                max-width: 100vw !important;
                margin-left: calc(50% - 50vw) !important;
                margin-right: calc(50% - 50vw) !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .discover-section {
                width: 100% !important;
                max-width: 100% !important;
                border: 0 !important;
                border-radius: 0;
            }

            .discover-section__head {
                padding: 12px 14px 6px;
            }

            .discover-section__head .alma-page-title,
            .discover-section__head h2 {
                font-size: 15px !important;
                font-weight: 400 !important;
            }

            .discover-section__head a {
                font-size: 12px !important;
                font-weight: 400 !important;
            }

            .discover-section__body {
                padding: 0 14px 8px;
            }

            .discover-compact-row {
                padding: 9px 0;
            }

            .discover-avatar,
            .discover-avatar-fallback {
                width: 40px;
                height: 40px;
                min-width: 40px;
            }

            .discover-item-name {
                font-size: 13px !important;
            }

            .discover-item-meta {
                font-size: 12px !important;
            }

            .discover-follow-btn,
            button.discover-follow-btn,
            a.discover-follow-btn {
                min-height: 32px !important;
                height: 32px !important;
                padding: 0 14px !important;
                font-size: 12px !important;
            }

            .discover-recommendations {
                width: 100% !important;
                max-width: 100% !important;
                margin-top: 16px !important;
                border-radius: 0 !important;
            }

            .discover-recommendations__head {
                min-height: 48px;
                padding: 0 16px;
            }

            .discover-recommendations__title {
                font-size: 15px;
            }
        }
    </style>

    <div class="discover-page-shell space-y-4">

        {{-- Arama Kutusu --}}
        <section class="discover-section">
            <div class="p-4 sm:p-5">
                <form action="{{ route('search') }}" method="GET" class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400 dark:text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
                    </span>

                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="{{ __('site.header.search_placeholder') }}..."
                        class="h-11 w-full rounded-[16px] border border-slate-200 bg-white pl-12 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-slate-300 focus:ring-0 dark:border-slate-800 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500 dark:focus:border-slate-700"
                        autocomplete="off"
                    >
                </form>
            </div>
        </section>

        {{-- Kullanıcılar --}}
        <section class="discover-section">
            <div class="discover-section__head">
                <h2 class="alma-page-title alma-page-title--compact-card text-slate-950 dark:text-white">
                    {{ __('site.search.users') }}
                </h2>

                <a href="{{ route('users.index') }}" class="shrink-0 text-slate-900 transition hover:text-slate-700 dark:text-slate-100 dark:hover:text-white">
                    {{ __('site.discover_page.view_all') }}
                </a>
            </div>

            <div class="discover-section__body">
                <div class="discover-compact-list">
                    @forelse($featuredUsers as $user)
                        @php
                            $userInitials = collect(preg_split('/\s+/', trim((string) $user->name), -1, PREG_SPLIT_NO_EMPTY))
                                ->take(2)
                                ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                ->implode('');

                            $userInitials = $userInitials !== '' ? $userInitials : 'AL';

                            $showVerified = (bool) (
                                $user->is_verified
                                || filled($user->verification_badge)
                                || filled($user->verification_badge_svg)
                            );

                            $userHandle = !empty($user->username)
                                ? '@' . $user->username
                                : __('site.users.followers', ['count' => number_format((int) $user->followers_count)]);

                            $isFollowingUser = (bool) ($user->is_followed ?? false);

                            if (!$isFollowingUser && auth()->check() && method_exists($user, 'followers')) {
                                $isFollowingUser = $user->followers()->where('follower_id', auth()->id())->exists();
                            }
                        @endphp

                        <div class="discover-compact-row">
                            <a href="{{ route('users.show', $user) }}" class="discover-compact-link">
                                @if($user->profile_photo_url)
                                    <img
                                        src="{{ $user->profile_photo_url }}"
                                        alt="{{ $user->name }}"
                                        class="discover-avatar ring-1 ring-slate-200 dark:ring-slate-700"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                @else
                                    <span class="discover-avatar-fallback">
                                        {{ $userInitials }}
                                    </span>
                                @endif

                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <p class="discover-item-name truncate">
                                            {{ $user->name }}
                                        </p>

                                        @if($showVerified)
                                            <x-verification-badge :user="$user" size="sm" class="text-[16px]" />
                                        @endif
                                    </div>

                                    <p class="discover-item-meta truncate text-[#4B6EA8] dark:text-blue-300">
                                        {{ $userHandle }}
                                    </p>
                                </div>
                            </a>

                            @auth
                                <form method="POST" action="{{ route('users.follow', ['user' => $user->username]) }}" class="m-0 shrink-0">
                                    @csrf
                                    <button type="submit" class="discover-follow-btn">
                                        {{ $isFollowingUser ? __('site.profile_page.following') : __('site.profile_page.follow') }}
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="discover-follow-btn shrink-0">
                                    {{ __('site.profile_page.follow') }}
                                </a>
                            @endauth
                        </div>
                    @empty
                        <div class="py-3 text-sm text-slate-500 dark:text-slate-400">
                            {{ __('site.users.empty') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Topluluklar --}}
        <section class="discover-section">
            <div class="discover-section__head">
                <h2 class="alma-page-title alma-page-title--compact-card text-slate-950 dark:text-white">
                    {{ __('site.common.communities') }}
                </h2>

                <a href="{{ route('blog.categories') }}" class="shrink-0 text-slate-900 transition hover:text-slate-700 dark:text-slate-100 dark:hover:text-white">
                    {{ __('site.discover_page.view_all') }}
                </a>
            </div>

            <div class="discover-section__body">
                <div class="discover-compact-list">
                    @forelse($featuredCommunities as $community)
                        @php
                            $communityName = (string) ($community->name ?? '');
                            $communityInitials = mb_strtoupper(mb_substr($communityName, 0, 2));
                            $communityInitials = $communityInitials !== '' ? $communityInitials : 'TP';
                            $communityColor = $communityBadgeColors[$loop->index % count($communityBadgeColors)];

                            $communityMeta = (int) $community->followers_count > 0
                                ? number_format((int) $community->followers_count) . ' ' . __('site.category_page.members')
                                : __('site.discover_page.posts_count', ['count' => number_format((int) $community->posts_count)]);

                            $isCommunityJoined = (bool) ($community->is_joined ?? false);

                            if (!$isCommunityJoined && auth()->check() && method_exists($community, 'followers')) {
                                $isCommunityJoined = $community->followers()->where('users.id', auth()->id())->exists();
                            }
                        @endphp

                        <div class="discover-compact-row">
                            <a href="{{ route('blog.category', $community) }}" class="discover-compact-link">
                                @if($community->profile_image_url)
                                    <img
                                        src="{{ $community->profile_image_url }}"
                                        alt="{{ $communityName }}"
                                        class="discover-avatar ring-1 ring-slate-200 dark:ring-slate-700"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                @else
                                    <span
                                        class="flex discover-avatar items-center justify-center text-sm font-normal text-white"
                                        style="background-color: {{ $communityColor }};"
                                    >
                                        {{ $communityInitials }}
                                    </span>
                                @endif

                                <div class="min-w-0">
                                    <p class="discover-item-name truncate">
                                        {{ $communityName }}
                                    </p>

                                    <p class="discover-item-meta truncate text-slate-500 dark:text-slate-400">
                                        {{ $communityMeta }}
                                    </p>
                                </div>
                            </a>

                            @auth
                                <form method="POST" action="{{ route('blog.category.join', $community) }}" class="m-0 shrink-0">
                                    @csrf
                                    <button type="submit" class="discover-follow-btn">
                                        {{ $isCommunityJoined ? __('site.category_page.joined') : __('site.category_page.join') }}
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="discover-follow-btn shrink-0">
                                    {{ __('site.category_page.join') }}
                                </a>
                            @endauth
                        </div>
                    @empty
                        <div class="py-3 text-sm text-slate-500 dark:text-slate-400">
                            {{ __('site.common.empty') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Öneriler --}}
        <section class="discover-recommendations">
            <div class="discover-recommendations__head">
                <h2 class="discover-recommendations__title">
                    {{ __('site.post_show.recommendations') }}
                </h2>
            </div>

            <div class="discover-recommendations__list space-y-0">
                @forelse($recommendedPosts as $post)
                    <div class="discover-recommendations__item">
                        @include('blog.post-card', [
                            'post' => $post,
                        ])
                    </div>

                    @include('partials.ads.feed-breaks', [
                        'iteration' => $loop->iteration,
                        'isLast' => $loop->last,
                    ])
                @empty
                    <div class="pb-4 pt-4 text-center text-sm text-slate-500 dark:text-slate-400">
                        {{ __('site.profile_page.empty_posts') }}
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection