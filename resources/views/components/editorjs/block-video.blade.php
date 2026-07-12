@if ($data['stretched'])
    <div class="content-block-image">
        <video controls
            class="player {{ $data['stretched'] ? ' content-block-image--stretched' : '' }}"
            @if (isset($data['caption'])) title="{{ strip_tags($data['caption']) }}" @endif>
            <source src="{{ $data['file']->url }}">
        </video>
        @if (isset($data['caption']) && $data['caption'])
            <div class="content-block-image__caption">{!! $data['caption'] !!}</div>
        @endif
    </div>
@else
    <div class="w-content content-block-image">
        <video controls
            class="player"
            @if (isset($data['caption'])) title="{{ strip_tags($data['caption']) }}" @endif>
            <source src="{{ $data['file']->url }}">
        </video>
        @if (isset($data['caption']) && $data['caption'])
            <div class="content-block-image__caption">{!! $data['caption'] !!}</div>
        @endif
    </div>
@endif
