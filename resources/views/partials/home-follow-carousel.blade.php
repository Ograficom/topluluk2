@php
    $suggestedUsers = collect($suggestedUsers ?? [])->values();
    $suggestedCommunities = collect($suggestedCommunities ?? [])->values();

    $items = collect();
    $maxCount = max($suggestedUsers->count(), $suggestedCommunities->count());

    for ($i = 0; $i < $maxCount; $i++) {
        if ($user = $suggestedUsers->get($i)) {
            $items->push(['type' => 'user', 'model' => $user]);
        }

        if ($community = $suggestedCommunities->get($i)) {
            $items->push(['type' => 'community', 'model' => $community]);
        }
    }
@endphp

@if ($items->isNotEmpty())
<section class="home-follow-box" data-follow-box>

    <div class="home-follow-box__header">
        <h2 class="home-follow-box__title">
            Takip edebileceğin kişiler ve topluluklar
        </h2>

        <button type="button" class="home-follow-box__close" data-close-box>
            <iconify-icon icon="lucide:x"></iconify-icon>
        </button>
    </div>

    <div class="home-follow-box__grid">
        @foreach ($items as $item)

            @php $entry = $item['model']; @endphp

            @if ($item['type'] === 'user')
                @php
                    $user = $entry;
                    $name = $user->name ?? '';
                    $username = $user->username ? '@'.$user->username : '';
                    $followers = number_format($user->followers_count ?? 0);
                    $isFollowing = $user->is_followed_by_viewer ?? false;
                @endphp

                <div class="home-follow-card">

                    <a href="{{ route('users.show', $user) }}" class="home-follow-card__profile">

                        <img src="{{ $user->profile_photo_url }}" class="home-follow-card__avatar">

                        <div class="home-follow-card__name">{{ $name }}</div>

                        <div class="home-follow-card__row">
                            <span class="home-follow-card__meta">{{ $username }}</span>
                            <span class="home-follow-card__followers">{{ $followers }} takipçi</span>
                        </div>

                    </a>

                    @auth
                        <form method="POST" action="{{ route('users.follow', $user->username) }}">
                            @csrf
                            <button class="home-follow-card__btn">
                                {{ $isFollowing ? 'Takip ediliyor' : 'Takip et' }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="home-follow-card__btn">
                            Takip et
                        </a>
                    @endauth

                </div>

            @else
                @php
                    $community = $entry;
                    $name = $community->name ?? '';
                    $members = number_format($community->followers_count ?? 0);
                    $isJoined = $community->is_joined_by_viewer ?? false;
                @endphp

                <div class="home-follow-card">

                    <a href="{{ route('blog.category', $community->slug) }}" class="home-follow-card__profile">

                        <img src="{{ $community->profile_image_url }}" class="home-follow-card__avatar">

                        <div class="home-follow-card__name">{{ $name }}</div>

                        <div class="home-follow-card__row">
                            <span class="home-follow-card__meta">Topluluk</span>
                            <span class="home-follow-card__followers">{{ $members }} üye</span>
                        </div>

                    </a>

                    @auth
                        <form method="POST" action="{{ route('blog.category.join', $community->slug) }}">
                            @csrf
                            <button class="home-follow-card__btn">
                                {{ $isJoined ? 'Katıldın' : 'Katıl' }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="home-follow-card__btn">
                            Katıl
                        </a>
                    @endauth

                </div>
            @endif

        @endforeach
    </div>
</section>

@push('head')
<style>
.home-follow-box {
    padding: 20px;
}

.home-follow-box__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}

.home-follow-box__title {
    font-size: 16px;
    font-weight: 700;
}

.home-follow-box__close {
    width: 36px;
    height: 36px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.home-follow-box__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

/* CARD */

.home-follow-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    text-align: center;
}

/* PROFILE */

.home-follow-card__profile {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

/* AVATAR */

.home-follow-card__avatar {
    width: 60px;
    height: 60px;
    border-radius: 999px;
    object-fit: cover;
}

/* NAME */

.home-follow-card__name {
    font-weight: 700;
    font-size: 14px;
}

/* USERNAME + FOLLOWERS */

.home-follow-card__row {
    display: flex;
    gap: 6px;
    justify-content: center;
    flex-wrap: wrap;
}

.home-follow-card__meta {
    font-size: 12px;
    color: #475569;
}

.home-follow-card__followers {
    font-size: 12px;
    color: #94a3b8;
}

/* BUTTON */

.home-follow-card__btn {
    width: 100%;
    height: 40px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    background: #fff;
    font-weight: 600;
    cursor: pointer;
}

/* MOBILE SCROLL */

@media (max-width: 768px) {
    .home-follow-box__grid {
        display: flex;
        overflow-x: auto;
        gap: 12px;
    }

    .home-follow-card {
        min-width: 200px;
        flex: 0 0 200px;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-close-box]').forEach(btn => {
        btn.addEventListener('click', function () {
            const box = btn.closest('[data-follow-box]');
            if (box) box.remove();
        });
    });
});
</script>
@endpush

@endif