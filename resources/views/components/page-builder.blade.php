@if($page && is_array($page->sections))
    @foreach($page->sections as $section)
        @php
            $sectionType = $section['type'] ?? 'content';
            $sectionContent = $section['content'] ?? $section['html'] ?? '';
        @endphp
        @if($sectionType === 'heading')
            <section class="card card-body"><h2>{{ $section['title'] ?? '' }}</h2>@if($sectionContent)<p>{{ $sectionContent }}</p>@endif</section>
        @elseif($sectionType === 'html')
            <section class="page-builder-section">{!! $sectionContent !!}</section>
        @elseif($sectionType === 'image' && !empty($section['url']))
            <figure class="page-builder-section"><img src="{{ $section['url'] }}" alt="{{ $section['alt'] ?? '' }}" loading="lazy"></figure>
        @elseif($sectionContent !== '')
            <section class="card card-body page-builder-section"><div>{!! nl2br(e($sectionContent)) !!}</div></section>
        @endif
    @endforeach
@endif
