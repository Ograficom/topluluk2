<aside class="post-quote-preview" aria-label="Alıntılanan gönderi">
    <div class="post-quote-preview__label color-gray">Yeniden paylaşılan gönderi</div>
    @if ($source)
        <a class="post-quote-preview__link" href="{{ route('posts.show', $source) }}">
            <div class="post-quote-preview__content">
                <span class="small color-gray">{{ $source->user?->displayName() ?? 'OgraFi yazarı' }}</span>
                <strong class="color-title">{{ $source->title }}</strong>
                @if (filled($source->excerpt))<span class="text-clamp-3 color-text">{{ $source->excerpt }}</span>@endif
            </div>
            @if ($sourceImage = $source->socialPreviewImageUrl())
                <img class="post-quote-preview__image" src="{{ $sourceImage }}" alt="" width="80" height="80" loading="lazy">
            @endif
        </a>
    @else
        <p class="small color-gray">Orijinal gönderi artık görüntülenemiyor.</p>
    @endif
</aside>
