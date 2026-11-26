@if (count($urls))
    <div class="post-inline-gallery" aria-label="Yazı içi görseller">
        @foreach ($urls as $url)
            <figure>
                <a href="{{ $url }}" data-fancybox="post-images-{{ $post->id }}" @unless($post->is_adult && ! auth()->user()?->show_adult_content) data-ografi-panorama-candidate="{{ $post->is_panorama ? 'explicit' : 'auto' }}" @endunless aria-label="Görseli büyüt">
                    <img src="{{ $url }}" alt="{{ $post->title }} - {{ $loop->iteration }}" loading="lazy" decoding="async">
                </a>
            </figure>
        @endforeach
    </div>
@endif
