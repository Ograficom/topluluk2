@extends('layouts.app')

@section('title', $advertisement->title)
@section('description', $advertisement->description)

@section('content')
    <div class="speedbar">
        <a href="{{ route('posts.index') }}">OgraFi</a>
        / <span>Sponsorlu içerik</span>
        / <span>{{ $advertisement->title }}</span>
    </div>

    <article class="card card-body post">
        <header class="post__header">
            <div class="user-box">
                @if ($advertisement->user)
                    <a href="{{ route('profiles.show', $advertisement->user) }}" class="avatar">
                        <img src="{{ $advertisement->user->avatarUrl() }}" alt="" width="40" height="40">
                    </a>
                @endif
                <div class="user-box-content">
                    <span class="name">
                        <a class="name-text" href="{{ $advertisement->user ? route('profiles.show', $advertisement->user) : route('posts.index') }}">{{ $advertisement->user?->displayName() ?? 'OgraFi reklamvereni' }}</a>
                    </span>
                    <div class="meta">
                        <span>Sponsorlu içerik</span>
                        <time datetime="{{ $advertisement->created_at?->toAtomString() }}">{{ $advertisement->created_at?->diffForHumans() }}</time>
                    </div>
                </div>
            </div>
            @if (auth()->check() && (auth()->user()->is($advertisement->user) || auth()->user()->is_admin))
                <div class="dropdown">
                    <button class="btn btn-secondary-ghost btn-circle btn-xs" type="button" data-dropdown-toggle aria-expanded="false" aria-label="Reklam menüsünü aç"><x-repost-icon name="more" size="16" /></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-menu-item dropdown-menu-item-sm"><a href="{{ route('advertisements.edit', $advertisement) }}">Reklamı düzenle</a></li>
                    </ul>
                </div>
            @endif
        </header>

        @if ($advertisement->post && ($advertisement->post->coverImageUrl() || count($advertisement->post->galleryImageUrls()) > 0 || filled($advertisement->post->video_url)))
            <div class="post__media-full">
                @include('posts._media', ['post' => $advertisement->post])
            </div>
        @elseif ($advertisement->imageUrl())
            <div class="post__media-full">
                <div class="post__media">
                    <img src="{{ $advertisement->imageUrl() }}" alt="{{ $advertisement->title }}" class="post__media-image">
                </div>
            </div>
        @endif

        <h1 class="post__title-full">{{ $advertisement->title }}</h1>

        <section class="editor">
            @foreach (preg_split('/\R{2,}/', trim($advertisement->description)) ?: [] as $paragraph)
                @if (filled($paragraph))<p>{!! nl2br(e($paragraph)) !!}</p>@endif
            @endforeach
        </section>

        <footer class="post__footer post__footer-full">
            <span class="small color-gray">Sponsorlu içerik</span>
            @if (filled($advertisement->target_url))
                <a class="btn btn-primary" href="{{ route('advertisements.click', $advertisement) }}" rel="sponsored nofollow">{{ $advertisement->button_text ?: 'Daha fazla bilgi al' }}</a>
            @endif
        </footer>
    </article>
@endsection

@push('scripts')
<script>
(() => {
    let activeSeconds = 0;
    let lastTick = Date.now();
    let sent = false;

    const accountTime = () => {
        const now = Date.now();
        if (document.visibilityState === 'visible') {
            activeSeconds += Math.floor((now - lastTick) / 1000);
        }
        lastTick = now;
    };

    const sendRead = () => {
        accountTime();
        if (sent || activeSeconds < 15) return;
        sent = true;

        fetch(@json(route('advertisements.read', $advertisement)), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ seconds: activeSeconds })
        }).catch(() => { sent = false; });
    };

    window.setInterval(sendRead, 1000);
    document.addEventListener('visibilitychange', () => {
        accountTime();
        lastTick = Date.now();
    });
})();
</script>
@endpush
