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
        </header>

        <h1 class="post__title-full">{{ $advertisement->title }}</h1>

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
