@if ($data['stretched'])
    <figure class="content-block-image">
        <img src="{{ $data['file']->url }}" data-zoom-src="{{ $data['file']->url }}"
            class="content-block-image{{ $data['stretched'] ? ' content-block-image--stretched' : '' }}"
            @if (isset($data['caption'])) alt="{{ strip_tags($data['caption']) }}" @endif loading="lazy">
        @if (isset($data['caption']) && $data['caption'])
            <figcaption class="content-block-image__caption">{!! $data['caption'] !!}</figcaption>
        @endif
    </figure>
@else
    <figure class="w-content content-block-image">
        <img src="{{ $data['file']->url }}" data-zoom-src="{{ $data['file']->url }}"
            class="content-block-image"
            @if (isset($data['caption'])) alt="{{ strip_tags($data['caption']) }}" @endif loading="lazy">
        @if (isset($data['caption']) && $data['caption'])
            <figcaption class="content-block-image__caption">{!! $data['caption'] !!}</figcaption>
        @endif
    </figure>
@endif
