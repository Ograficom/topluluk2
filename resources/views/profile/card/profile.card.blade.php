{{--
    Dosya yolu:
    resources/views/profile/card/profile.card.profil-card.blade.php

    Include ile kullanım:
    {!! view()->file(resource_path('views/profile/card/profile.card.profil-card.blade.php'), [
        'user' => $post->user ?? $user ?? null,
        'placement' => 'down',
    ])->render() !!}
--}}

@php
    $profileUser = $user ?? $profileUser ?? null;
    $placement = $placement ?? 'down';
    $triggerClass = $triggerClass ?? '';
    $cardClass = $cardClass ?? '';

    if ($profileUser && method_exists($profileUser, 'getKey')) {
        try {
            $profileUser = \App\Models\User::query()
                ->whereKey($profileUser->getKey())
                ->withCount([
                    'followers as ografi_followers_count' => function ($query) {},
                    'following as ografi_following_count' => function ($query) {},
                ])
                ->first() ?? $profileUser;
        } catch (\Throwable $e) {
            try {
                $profileUser = \App\Models\User::query()
                    ->whereKey($profileUser->getKey())
                    ->withCount([
                        'followers as ografi_followers_count' => function ($query) {},
                    ])
                    ->first() ?? $profileUser;
            } catch (\Throwable $e) {
                $profileUser = $user ?? $profileUser;
            }
        }
    }

    $name = $profileUser?->name ?? __('Kullanıcı');
    $username = $profileUser?->username ?? null;

    $avatar = $profileUser?->profile_photo_url
        ?? $profileUser?->avatar_url
        ?? $profileUser?->photo_url
        ?? $profileUser?->profile_photo_path
        ?? null;

    if ($avatar && !\Illuminate\Support\Str::startsWith($avatar, ['http://', 'https://', '/'])) {
        try {
            $avatar = asset('storage/' . ltrim($avatar, '/'));
        } catch (\Throwable $e) {
            $avatar = null;
        }
    }

    $avatar = $avatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=f1f5f9&color=111827&size=160';

    $cover = $profileUser?->cover_photo_url
        ?? $profileUser?->cover_image_url
        ?? $profileUser?->cover_url
        ?? $profileUser?->cover_image
        ?? $profileUser?->cover_photo_path
        ?? null;

    if ($cover && !\Illuminate\Support\Str::startsWith($cover, ['http://', 'https://', '/'])) {
        try {
            $cover = asset('storage/' . ltrim($cover, '/'));
        } catch (\Throwable $e) {
            $cover = null;
        }
    }

    $bio = $profileUser?->bio
        ?? $profileUser?->about
        ?? $profileUser?->description
        ?? __('Bu kullanıcı henüz açıklama eklemedi.');

    $createdAt = $profileUser?->created_at ?? null;
    $joinedText = null;

    if ($createdAt) {
        try {
            $joinedText = $createdAt->translatedFormat('F Y');
        } catch (\Throwable $e) {
            try {
                $joinedText = \Carbon\Carbon::parse($createdAt)->translatedFormat('F Y');
            } catch (\Throwable $e) {
                $joinedText = null;
            }
        }
    }

    $followersCount = $profileUser?->ografi_followers_count
        ?? $profileUser?->followers_count
        ?? 0;

    $followingCount = $profileUser?->ografi_following_count
        ?? $profileUser?->following_count
        ?? $profileUser?->followings_count
        ?? 0;

    $isVerified = (bool) (
        $profileUser?->is_verified
        ?? $profileUser?->verified
        ?? false
    );

    $verifiedSvg = $profileUser?->verification_badge_svg ?? null;

    $profileUrl = '#';

    if ($profileUser) {
        try {
            if (\Illuminate\Support\Facades\Route::has('profile.show')) {
                $profileUrl = route('profile.show', $profileUser->username ?? $profileUser->id);
            } elseif (\Illuminate\Support\Facades\Route::has('profile.public')) {
                $profileUrl = route('profile.public', $profileUser->username ?? $profileUser->id);
            } elseif ($username) {
                $profileUrl = url('/' . ltrim($username, '@'));
            }
        } catch (\Throwable $e) {
            $profileUrl = '#';
        }
    }

    $followAction = null;

    if ($profileUser) {
        try {
            if (\Illuminate\Support\Facades\Route::has('users.follow')) {
                $followAction = route('users.follow', $profileUser);
            } elseif (\Illuminate\Support\Facades\Route::has('profile.follow')) {
                $followAction = route('profile.follow', $profileUser);
            }
        } catch (\Throwable $e) {
            $followAction = null;
        }
    }

    $isFollowing = (bool) ($profileUser?->is_followed ?? $profileUser?->is_following ?? false);
    $isOwnProfile = auth()->check() && $profileUser && auth()->id() === $profileUser?->id;

    $placementClass = $placement === 'up'
        ? 'ografi-profile-popover-wrap--up'
        : 'ografi-profile-popover-wrap--down';

    $safeName = e($name);
@endphp

<span class="ografi-profile-popover-wrap {{ $placementClass }} {{ $triggerClass }}">
    <span class="ografi-profile-popover-trigger">
        @if(isset($slot) && trim((string) $slot) !== '')
            {{ $slot }}
        @elseif(isset($triggerHtml) && trim((string) $triggerHtml) !== '')
            {!! $triggerHtml !!}
        @else
            <a href="{{ $profileUrl }}" class="ografi-profile-popover-trigger__link" aria-label="{{ $name }} profiline git">
                <img src="{{ $avatar }}" alt="{{ $name }}" class="ografi-profile-popover-trigger__avatar" loading="lazy">
                <span class="ografi-profile-popover-trigger__name">{{ $name }}</span>
            </a>
        @endif
    </span>

    <span class="ografi-profile-popover {{ $cardClass }}" role="dialog" aria-label="{{ $name }} profil kartı">
        <span
            class="ografi-profile-popover__cover"
            @if($cover) style="background-image: url('{{ $cover }}');" @endif
        ></span>

        <span class="ografi-profile-popover__body">
            <a href="{{ $profileUrl }}" class="ografi-profile-popover__avatar-link" aria-label="{{ $name }} profiline git">
                <img src="{{ $avatar }}" alt="{{ $name }}" class="ografi-profile-popover__avatar" loading="lazy">
            </a>

            <span class="ografi-profile-popover__name-row">
                <a href="{{ $profileUrl }}" class="ografi-profile-popover__name">
                    {{ $name }}
                </a>

                @if($isVerified)
                    <span class="ografi-profile-popover__verified" title="Doğrulanmış kullanıcı" aria-label="Doğrulanmış kullanıcı">
                        @if($verifiedSvg)
                            {!! $verifiedSvg !!}
                        @else
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M12 2.25l2.02 1.48 2.5-.05 1.3 2.14 2.28 1.06-.35 2.48 1.08 2.27-1.79 1.76-.44 2.47-2.42.67-1.87 1.68L12 17.25l-2.31.96-1.87-1.68-2.42-.67-.44-2.47-1.79-1.76 1.08-2.27-.35-2.48 2.28-1.06 1.3-2.14 2.5.05L12 2.25z" />
                                <path d="M10.55 13.75l5.18-5.18 1.06 1.06-6.24 6.24-3.34-3.34 1.06-1.06 2.28 2.28z" />
                            </svg>
                        @endif
                    </span>
                @endif
            </span>

            @if($username)
                <a href="{{ $profileUrl }}" class="ografi-profile-popover__username">@{{ $username }}</a>
            @endif

            @if($joinedText)
                <span class="ografi-profile-popover__date">{{ $joinedText }}</span>
            @endif

            @if($bio)
                <span class="ografi-profile-popover__bio">
                    {{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $bio)), 128) }}
                </span>
            @endif

            <span class="ografi-profile-popover__stats">
                <span><strong>{{ number_format((int) $followersCount) }}</strong> follower</span>
                <span><strong>{{ number_format((int) $followingCount) }}</strong> following</span>
            </span>

            @if(!$isOwnProfile)
                @auth
                    @if($followAction)
                        <form method="POST" action="{{ $followAction }}" class="ografi-profile-popover__follow-form">
                            @csrf
                            <button type="submit" class="ografi-profile-popover__follow {{ $isFollowing ? 'is-following' : '' }}">
                                {{ $isFollowing ? __('Following') : __('Follow') }}
                            </button>
                        </form>
                    @endif
                @else
                    @if(\Illuminate\Support\Facades\Route::has('login'))
                        <a href="{{ route('login') }}" class="ografi-profile-popover__follow">
                            {{ __('Follow') }}
                        </a>
                    @endif
                @endauth
            @endif
        </span>
    </span>
</span>

@once
    <style>
        .ografi-profile-popover-wrap,
        .ografi-profile-popover-wrap * {
            box-sizing: border-box;
        }

        .ografi-profile-popover-wrap {
            position: relative;
            display: inline-flex;
            vertical-align: middle;
            max-width: 100%;
            isolation: isolate;
        }

        .ografi-profile-popover-trigger {
            display: inline-flex;
            align-items: center;
            min-width: 0;
            max-width: 100%;
            cursor: pointer;
        }

        .ografi-profile-popover-trigger__link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            max-width: 100%;
            color: inherit;
            text-decoration: none;
        }

        .ografi-profile-popover-trigger__avatar {
            display: block;
            width: 34px;
            height: 34px;
            flex: 0 0 auto;
            object-fit: cover;
            border-radius: 999px;
            background: #f1f5f9;
        }

        .ografi-profile-popover-trigger__name {
            display: block;
            min-width: 0;
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.25;
        }

        .ografi-profile-popover {
            position: absolute;
            left: 0;
            z-index: 2147483000;
            display: block;
            width: min(288px, calc(100vw - 26px));
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, .11);
            border-radius: 6px;
            background: #ffffff;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .18);
            color: #111827;
            text-align: left;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translate3d(0, 8px, 0);
            transition: opacity .16s ease, visibility .16s ease, transform .16s ease;
        }

        .ografi-profile-popover-wrap--down .ografi-profile-popover {
            top: calc(100% + 8px);
        }

        .ografi-profile-popover-wrap--up .ografi-profile-popover {
            bottom: calc(100% + 8px);
            transform: translate3d(0, -8px, 0);
        }

        .ografi-profile-popover-wrap:hover .ografi-profile-popover,
        .ografi-profile-popover-wrap:focus-within .ografi-profile-popover {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translate3d(0, 0, 0);
        }

        .ografi-profile-popover__cover {
            display: block;
            width: 100%;
            height: 82px;
            background-color: #f8fafc;
            background-image: radial-gradient(#111827 0.72px, transparent 0.72px);
            background-size: 4px 4px;
            background-position: center;
            background-repeat: repeat;
        }

        .ografi-profile-popover__body {
            position: relative;
            display: block;
            padding: 48px 16px 16px;
            background: #ffffff;
        }

        .ografi-profile-popover__avatar-link {
            position: absolute;
            top: -43px;
            left: 16px;
            display: block;
            width: 72px;
            height: 72px;
            padding: 4px;
            border-radius: 999px;
            background: #ffffff;
            text-decoration: none;
        }

        .ografi-profile-popover__avatar {
            display: block;
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 999px;
            background: #f1f5f9;
        }

        .ografi-profile-popover__name-row {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }

        .ografi-profile-popover__name {
            display: block;
            max-width: 214px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #111827;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            line-height: 1.25;
        }

        .ografi-profile-popover__verified {
            display: inline-flex;
            width: 17px;
            height: 17px;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            color: #1d9bf0;
        }

        .ografi-profile-popover__verified svg {
            display: block;
            width: 17px;
            height: 17px;
        }

        .ografi-profile-popover__verified svg path:first-child {
            fill: currentColor;
        }

        .ografi-profile-popover__verified svg path:last-child {
            fill: #ffffff;
        }

        .ografi-profile-popover__username,
        .ografi-profile-popover__date {
            display: block;
            margin-top: 4px;
            color: #6b7280;
            text-decoration: none;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.35;
        }

        .ografi-profile-popover__bio {
            display: block;
            margin-top: 12px;
            color: #1f2937;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.35;
        }

        .ografi-profile-popover__stats {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-top: 10px;
            color: #111827;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.35;
        }

        .ografi-profile-popover__stats strong {
            font-weight: 500;
        }

        .ografi-profile-popover__follow-form {
            display: block;
            margin: 13px 0 0;
        }

        .ografi-profile-popover__follow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 72px;
            min-height: 36px;
            padding: 0 16px;
            border: 0;
            border-radius: 7px;
            background: #009f6b;
            color: #ffffff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            line-height: 1;
            cursor: pointer;
            appearance: none;
        }

        .ografi-profile-popover__follow:hover,
        .ografi-profile-popover__follow:focus-visible {
            background: #00885c;
            color: #ffffff;
            outline: none;
        }

        .ografi-profile-popover__follow.is-following {
            background: #e5e7eb;
            color: #111827;
        }

        @media (max-width: 640px) {
            .ografi-profile-popover {
                left: 50%;
                width: min(288px, calc(100vw - 20px));
                transform: translate3d(-50%, 8px, 0);
            }

            .ografi-profile-popover-wrap--up .ografi-profile-popover {
                transform: translate3d(-50%, -8px, 0);
            }

            .ografi-profile-popover-wrap:hover .ografi-profile-popover,
            .ografi-profile-popover-wrap:focus-within .ografi-profile-popover {
                transform: translate3d(-50%, 0, 0);
            }
        }

        .dark .ografi-profile-popover,
        [data-theme="dark"] .ografi-profile-popover {
            border-color: rgba(255, 255, 255, .10);
            background: #111827;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .42);
            color: #f9fafb;
        }

        .dark .ografi-profile-popover__body,
        [data-theme="dark"] .ografi-profile-popover__body {
            background: #111827;
        }

        .dark .ografi-profile-popover__cover,
        [data-theme="dark"] .ografi-profile-popover__cover {
            background-color: #0f172a;
            background-image: radial-gradient(rgba(255, 255, 255, .42) 0.72px, transparent 0.72px);
        }

        .dark .ografi-profile-popover__avatar-link,
        [data-theme="dark"] .ografi-profile-popover__avatar-link {
            background: #111827;
        }

        .dark .ografi-profile-popover-trigger__name,
        .dark .ografi-profile-popover__name,
        .dark .ografi-profile-popover__stats,
        [data-theme="dark"] .ografi-profile-popover-trigger__name,
        [data-theme="dark"] .ografi-profile-popover__name,
        [data-theme="dark"] .ografi-profile-popover__stats {
            color: #f9fafb;
        }

        .dark .ografi-profile-popover__username,
        .dark .ografi-profile-popover__date,
        [data-theme="dark"] .ografi-profile-popover__username,
        [data-theme="dark"] .ografi-profile-popover__date {
            color: #9ca3af;
        }

        .dark .ografi-profile-popover__bio,
        [data-theme="dark"] .ografi-profile-popover__bio {
            color: #e5e7eb;
        }
    </style>
@endonce
