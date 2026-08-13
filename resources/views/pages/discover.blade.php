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

        /* Tailwind'in dark: varyanti bu projede derlenmiyor (build/purge
           konfigurasyonu sadece bu dosyada kullanilan diger dark: siniflari
           uretmemis) - sitenin geri kalani gibi el yazimi .dark kurallariyla
           yaziliyor. Aksi halde baslik koyu modda #030712 gibi neredeyse
           siyah kaliyor, arka planla okunmuyordu (Vibrancy ilkesi ihlali). */
        .discover-section__head .alma-page-title,
        .discover-section__head h2 {
            font-size: 16px !important;
            font-weight: 600 !important;
            line-height: 1.25 !important;
            letter-spacing: 0 !important;
            color: rgb(2 6 23);
        }

        html.dark .discover-section__head .alma-page-title,
        html.dark .discover-section__head h2 {
            color: #ffffff;
        }

        /* "Tumunu Gor": dinlenme -> hover'da griye doner -> basinca daha
           koyu grilesir (Response ilkesi - anlik, surekli geri bildirim,
           hover ve basma ayri, okunabilir adimlar). */
        .discover-section__head a {
            font-size: 13px !important;
            font-weight: 400 !important;
            color: rgb(15 23 42) !important;
            transition: color .15s ease;
        }

        .discover-section__head a:hover,
        .discover-section__head a:focus-visible {
            color: #64748b !important;
            outline: none;
        }

        .discover-section__head a:active {
            color: #334155 !important;
        }

        html.dark .discover-section__head a {
            color: #e2e8f0 !important;
        }

        html.dark .discover-section__head a:hover,
        html.dark .discover-section__head a:focus-visible {
            color: #94a3b8 !important;
        }

        html.dark .discover-section__head a:active {
            color: #64748b !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .discover-section__head a {
                transition: none;
            }
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
            font-weight: 600 !important;
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
            padding: 0 16px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            line-height: 1 !important;
            cursor: pointer !important;
            text-decoration: none !important;
            box-shadow: none !important;
            outline: none !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            transition: background-color .15s ease, color .15s ease, transform .1s ease-out !important;
        }

        /* Zaten yapilmis (Takiptesin/Katildin) - notr gri, geri cekilmis */
        .discover-follow-btn--done,
        button.discover-follow-btn--done,
        a.discover-follow-btn--done {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }

        .discover-follow-btn--done:hover,
        .discover-follow-btn--done:focus,
        button.discover-follow-btn--done:hover,
        button.discover-follow-btn--done:focus,
        a.discover-follow-btn--done:hover,
        a.discover-follow-btn--done:focus {
            background-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        .discover-follow-btn--done:active,
        button.discover-follow-btn--done:active,
        a.discover-follow-btn--done:active {
            background-color: #94a3b8 !important;
            color: #0f172a !important;
        }

        .dark .discover-follow-btn--done,
        .dark button.discover-follow-btn--done,
        .dark a.discover-follow-btn--done {
            background-color: #334155 !important;
            color: #ffffff !important;
        }

        .dark .discover-follow-btn--done:hover,
        .dark .discover-follow-btn--done:focus,
        .dark button.discover-follow-btn--done:hover,
        .dark button.discover-follow-btn--done:focus,
        .dark a.discover-follow-btn--done:hover,
        .dark a.discover-follow-btn--done:focus {
            background-color: #475569 !important;
            color: #ffffff !important;
        }

        .dark .discover-follow-btn--done:active,
        .dark button.discover-follow-btn--done:active,
        .dark a.discover-follow-btn--done:active {
            background-color: #64748b !important;
            color: #ffffff !important;
        }

        /* Henuz yapilmamis (Takip et/Katil) - marka mavisi, eylemi cagirir.
           Ayni renk hem acik hem koyu temada kalir (site geneli birincil
           buton rengiyle tutarli - bkz. Kullanicilar sayfasi Takip et). */
        .discover-follow-btn--action,
        button.discover-follow-btn--action,
        a.discover-follow-btn--action {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }

        .discover-follow-btn--action:hover,
        .discover-follow-btn--action:focus,
        button.discover-follow-btn--action:hover,
        button.discover-follow-btn--action:focus,
        a.discover-follow-btn--action:hover,
        a.discover-follow-btn--action:focus {
            background-color: #1d4ed8 !important;
            color: #ffffff !important;
        }

        .discover-follow-btn--action:active,
        button.discover-follow-btn--action:active,
        a.discover-follow-btn--action:active {
            background-color: #1e40af !important;
            color: #ffffff !important;
        }

        .discover-follow-btn:active,
        button.discover-follow-btn:active,
        a.discover-follow-btn:active {
            transform: scale(.96) !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .discover-follow-btn,
            button.discover-follow-btn,
            a.discover-follow-btn {
                transition: background-color .15s ease, color .15s ease !important;
            }

            .discover-follow-btn:active,
            button.discover-follow-btn:active,
            a.discover-follow-btn:active {
                transform: none !important;
            }
        }

        /* Site genelindeki "body.alma-app :where(input,textarea,select)
           :not(#comments *) {background:var(--ui-surface-muted) !important}"
           resetiyle ayni ozgulluk sorunu (bkz. Kullanicilar sayfasi arama
           kutusu) - input#id + iki class + input turu = (id:1, class:2,
           type:1), o kuralin (id:1, class:1, type:1) class katmaninda
           gececek sekilde eziyor. Odakta (Response ilkesi - aninda, surekli
           geri bildirim) marka mavisiyle halka belirir. */
        input#discover-search-input.discover-search-input.discover-search-input {
            background: #ffffff !important;
            border-color: #e2e8f0 !important;
            color: #334155 !important;
            box-shadow: none !important;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        input#discover-search-input.discover-search-input.discover-search-input:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .15) !important;
        }

        html.dark input#discover-search-input.discover-search-input.discover-search-input {
            background: rgb(15 23 42) !important;
            border-color: rgb(30 41 59) !important;
            color: #ffffff !important;
        }

        html.dark input#discover-search-input.discover-search-input.discover-search-input:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .2) !important;
        }

        @media (prefers-reduced-motion: reduce) {
            input#discover-search-input.discover-search-input.discover-search-input {
                transition: none;
            }
        }

        /* Oneriler artik Kullanicilar/Topluluklar ile AYNI .discover-section
           kabini kullaniyor (ayni 18px kose, ayni baslik/govde) - once
           kendi ayri 10px kose + alt-cizgili baslik deseni vardi, uc kutu
           birbirine hic benzemiyordu (Tutarlilik/Familiarity ilkesi). Post
           kartlari kendi kenarligini zaten tasidigi icin sadece aralarina
           16px bosluk (sitenin geri kalanindaki space-y-4 ile ayni) yeterli -
           once margin/padding 0 !important ile tamamen bastirilmisti. */
        .discover-feed-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* [data-post-card-shell]'in kendi stil bloğunda (blog/post-card.blade.php,
           6500+ satır) aynı selector için üst üste yazılmış birbiriyle
           çelişen birden fazla !important kural var (18px yumuşak gölge,
           10px ince kenarlık, 8px sert reset... hepsi ayrı satırlarda) -
           kazanan hicbiri degil, en yuksek ozgullukteki genel tema reset'i
           (radius:8px, kenarlik:1px gri, golge:none) - kart bu yuzden diger
           sayfalardaki gibi duz/eski gorunuyordu. Bu sadece discover
           sayfasina ozel, body.alma-app.route-discover ile hem .alma-app
           hem .route-discover class'ini ayni anda tasiyan tek body'yi
           hedefleyip o kuralin ozgulluk katmanini (2 class) asiyor.

           Ayrica: 640-960px arasinda [data-post-card-shell] sabit
           genislikte (--main-col, 656px) ve "calc(50% + 8px - 50vw)"
           gibi viewport-goreli negatif margin ile tasiyordu - bu formul
           kartin SAYFA GENELINDE ortalanmis, tam genislikte bir ana sutun
           icinde oldugunu varsayiyor (blog/profil sayfalarindaki gibi).
           Discover'da kart, kendi ic bosluklu (.discover-section__body)
           kabinin icine GOMULU - viewport'a gore degil, o kabin
           genisligine gore hizalanmali. Sonuc: kart konteynerin disina
           tasip sola/saga kayik goruntyordu (Spatial consistency ilkesi
           ihlali - kartin nerede durdugu tahmin edilebilir olmali).

           Son duzeltme: kart kendi ic dolgusunu (18-24px) TASIRKEN,
           .discover-section__body'nin PAYLASILAN 18px yan dolgusu da
           ustune binince Kullanicilar/Topluluklar'daki tek katmanli 18px
           girintiden 2 kat fazla bosluk olusuyor, "kutu icinde kutu"
           gorunuyordu ve iki ayri beyaz yuzey ust uste biniyordu. Simdi
           feed govdesinin (--feed) yan dolgusu sifirlandi - kart ARTIK
           konteynerin kendi kenariyla birlesip TEK yuzey gibi duruyor,
           kendi ic dolgusu digger kutulardaki 18px'e denk bir bosluk
           birakiyor. Golge kaldirildi (.discover-section'in overflow:
           hidden'i kenarda kesip yarim golge birakirdi); mobilde kose de
           konteynerin kendisi gibi sifirlaniyor - boylece mobilde sag/sol
           ic bosluk hic yok, kart ekran kenarina kadar uzaniyor. */
        body.alma-app.route-discover [data-post-card-shell] {
            border: 0 !important;
            border-radius: 18px !important;
            box-shadow: none !important;
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .discover-section__body--feed {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        @media (max-width: 640px) {
            body.alma-app.route-discover [data-post-card-shell] {
                border-radius: 0 !important;
            }
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
                        id="discover-search-input"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="{{ __('site.header.search_placeholder') }}..."
                        class="discover-search-input h-11 w-full rounded-[16px] border pl-12 pr-4 text-sm text-slate-700 outline-none placeholder:text-slate-400 dark:text-white dark:placeholder:text-slate-500"
                        autocomplete="off"
                    >
                </form>
            </div>
        </section>

        @include('partials.ads.slot', [
            'slotKey' => 'ads_feed_top',
        ])

        {{-- Kullanıcılar --}}
        <section class="discover-section">
            <div class="discover-section__head">
                <h2 class="alma-page-title alma-page-title--compact-card text-slate-950 dark:text-white">
                    {{ __('site.search.users') }}
                </h2>

                <a href="{{ route('users.index') }}" class="shrink-0">
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
                            <div class="discover-compact-link">
                                <a href="{{ route('users.show', $user) }}" class="shrink-0">
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
                                </a>

                                <div class="min-w-0">
                                    <a href="{{ route('users.show', $user) }}" class="flex w-fit max-w-full items-center gap-1.5">
                                        <p class="discover-item-name truncate">
                                            {{ $user->name }}
                                        </p>

                                        @if($showVerified)
                                            <x-verification-badge :user="$user" size="sm" class="text-[16px]" />
                                        @endif
                                    </a>

                                    <p class="discover-item-meta truncate text-[#4B6EA8] dark:text-blue-300">
                                        {{ $userHandle }}
                                    </p>
                                </div>
                            </div>

                            @auth
                                <form method="POST" action="{{ route('users.follow', ['user' => $user->username]) }}" class="m-0 shrink-0">
                                    @csrf
                                    <button type="submit" class="discover-follow-btn {{ $isFollowingUser ? 'discover-follow-btn--done' : 'discover-follow-btn--action' }}">
                                        {{ $isFollowingUser ? __('site.profile_page.following') : __('site.profile_page.follow') }}
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="discover-follow-btn discover-follow-btn--action shrink-0">
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

                <a href="{{ route('blog.categories') }}" class="shrink-0">
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
                            <div class="discover-compact-link">
                                <a href="{{ route('blog.category', $community) }}" class="shrink-0">
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
                                </a>

                                <div class="min-w-0">
                                    <a href="{{ route('blog.category', $community) }}" class="block w-fit max-w-full">
                                        <p class="discover-item-name truncate">
                                            {{ $communityName }}
                                        </p>
                                    </a>

                                    <p class="discover-item-meta truncate text-slate-500 dark:text-slate-400">
                                        {{ $communityMeta }}
                                    </p>
                                </div>
                            </div>

                            @auth
                                <form method="POST" action="{{ route('blog.category.join', $community) }}" class="m-0 shrink-0">
                                    @csrf
                                    <button type="submit" class="discover-follow-btn {{ $isCommunityJoined ? 'discover-follow-btn--done' : 'discover-follow-btn--action' }}">
                                        {{ $isCommunityJoined ? __('site.category_page.joined') : __('site.category_page.join') }}
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="discover-follow-btn discover-follow-btn--action shrink-0">
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
        <section class="discover-section">
            <div class="discover-section__head">
                <h2 class="alma-page-title alma-page-title--compact-card">
                    {{ __('site.post_show.recommendations') }}
                </h2>
            </div>

            <div class="discover-section__body discover-section__body--feed">
                <div class="discover-feed-list">
                    @forelse($recommendedPosts as $post)
                        @include('blog.post-card', [
                            'post' => $post,
                        ])

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
            </div>
        </section>
    </div>
@endsection