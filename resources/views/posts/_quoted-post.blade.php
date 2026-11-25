<aside class="post-quote-preview" aria-label="Alıntılanan gönderi">
    @if ($source)
        <a class="post-quote-preview__link" href="{{ route('posts.show', $source) }}">
            <div class="post-quote-preview__content">
                <span class="post-quote-preview__author small color-gray">
                    <img class="post-quote-preview__avatar" src="{{ $source->user?->avatarUrl() ?? asset('assets/img/noavatar.png') }}" alt="" width="24" height="24" loading="lazy">
                    <span>{{ $source->user?->displayName() ?? 'OgraFi yazarı' }}</span>
                </span>
                <strong class="color-title">{{ $source->title }}</strong>
                @if (filled($source->excerpt))<span class="text-clamp-3 color-text">{{ $source->excerpt }}</span>@endif
            </div>
            @if ($sourceImage = $source->socialPreviewImageUrl())
                <img class="post-quote-preview__image" src="{{ $sourceImage }}" alt="" width="104" height="104" loading="lazy">
            @endif
        </a>
    @else
        <p class="small color-gray">Orijinal gönderi artık görüntülenemiyor.</p>
    @endif
</aside>
