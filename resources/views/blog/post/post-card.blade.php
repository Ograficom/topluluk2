@php
    use Illuminate\Support\Collection;
    use Illuminate\Support\Str;

    $user = auth()->user();
    $author = $post->author ?? null;

    $bookmarkUrl = $user ? route('blog.post.bookmark', $post) : null;

    $authorAvatar = $author?->profile_photo_url ?? 'https://placehold.co/80x80';
    $categoryAvatar = $post->category?->profile_image_url
        ?? $post->category?->profile_image
        ?? null;
    $hasCategory = (bool) $post->category;

    $featuredImage = $post->featured_image_url
        ?? $post->featured_image
        ?? null;
    $featuredRenderWidth = 3840;
    $featuredRenderHeight = 2160;
    $featuredFrameStyle = 'display:block;width:100%;aspect-ratio:3840 / 2160;overflow:hidden;';
    $featuredImageStyle = 'display:block;width:100%;height:100%;max-width:none;object-fit:cover;';
    $linkPreview = $post->link_preview ?? null;
    if (is_object($linkPreview)) {
        $linkPreview = (array) $linkPreview;
    }
    $linkPreview = is_array($linkPreview) ? $linkPreview : null;

    $publishedAt = $post->published_at ?? $post->created_at;

    $videoFallbacks = collect($post->content_json['blocks'] ?? [])
        ->filter(fn ($block) => ($block['type'] ?? null) === 'video')
        ->map(fn ($block) => [
            'url' => $block['data']['url'] ?? null,
            'caption' => $block['data']['caption'] ?? null,
            'subtitles' => $block['data']['subtitles'] ?? [],
        ])
        ->filter(fn ($entry) => filled($entry['url']))
        ->values();

    $hasVideoBlocks = $videoFallbacks->isNotEmpty();
    $contentWithoutVideo = $hasVideoBlocks
        ? preg_replace('/<video\b[^>]*>[\s\S]*?<\/video>/i', '', $post->content ?? '')
        : ($post->content ?? '');

    $hasPoll = collect($post->content_json['blocks'] ?? [])
        ->contains(fn ($block) => ($block['type'] ?? null) === 'poll');
@endphp

<article class="space-y-6">
    <div class="bg-card-light dark:bg-card-dark rounded-xl p-5 shadow-sm sm:p-6">
        <div class="flex items-center gap-4">
            <div class="relative h-12 w-12 shrink-0">
                <div class="absolute inset-0 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700 ring-1 ring-gray-200 dark:ring-gray-700">
                    <img src="{{ $authorAvatar }}" alt="{{ $author?->name ?? 'Yazar' }}" class="h-full w-full object-cover" loading="lazy" />
                </div>
                @if($hasCategory)
                    <div class="absolute bottom-0 right-0 h-7 w-7 translate-x-1 translate-y-1 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700 ring-1 ring-gray-200 dark:ring-gray-700">
                        @if($categoryAvatar)
                            <img src="{{ $categoryAvatar }}" alt="{{ $post->category?->name ?? 'Kategori' }}" class="h-full w-full object-cover" loading="lazy" />
                        @else
                            <span class="flex h-full w-full items-center justify-center bg-white text-[8px] font-bold uppercase tracking-[0.04em] text-gray-500">AI</span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="min-w-0">
                <div class="flex items-center gap-1 text-sm font-semibold text-gray-900 dark:text-white">
                    <span class="truncate">{{ $author?->name ?? 'Topluluk' }}</span>
                    @if((bool) ($author?->is_verified ?? false) || filled($author?->verification_badge ?? null) || filled($author?->verification_badge_svg ?? null))
                        <span class="inline-flex items-center" title="Doğrulandı">
                            <x-verification-badge :user="$author" size="md" />
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                    @if($hasCategory)
                        <a href="{{ route('blog.category', $post->category) }}" class="max-w-[220px] truncate font-medium text-gray-800 dark:text-gray-200 hover:text-gray-500">
                            {{ $post->category->name }}
                        </a>
                        @if($publishedAt)
                            <span class="text-gray-400">&bull;</span>
                            <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">{{ $publishedAt->format('d.m.Y H:i') }}</span>
                        @endif
                        @if($hasPoll)
                            <span class="text-gray-400">&bull;</span>
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Anket</span>
                        @endif
                    @else
                        <span class="text-gray-500 dark:text-gray-400">Kategori yok</span>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <div class="bg-card-light dark:bg-card-dark rounded-xl p-5 shadow-sm space-y-5 sm:p-6">
        <h1 class="text-2xl font-bold leading-tight text-gray-900 dark:text-white sm:text-3xl">{{ $post->title }}</h1>

        @if(!empty($post->excerpt))
            <div class="text-sm text-gray-700 dark:text-gray-300 sm:text-base">
                {{ $post->excerpt }}
            </div>
        @endif

        @if($linkPreview)
            @include('blog.partials.link-preview', ['preview' => $linkPreview])
        @endif

        @if(!empty($featuredImage))
            <div class="overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800" style="{{ $featuredFrameStyle }}">
                <img
                    src="{{ $featuredImage }}"
                    alt="{{ $post->title }}"
                    class="block h-auto w-full"
                    width="{{ $featuredRenderWidth }}"
                    height="{{ $featuredRenderHeight }}"
                    style="{{ $featuredImageStyle }}"
                    loading="lazy"
                    decoding="async"
                />
            </div>
        @endif

        <div class="post-content text-[14px] leading-7 text-gray-800 dark:text-gray-200 sm:text-[15px]">
            {!! $contentWithoutVideo !!}
        </div>

        @if(!empty($pollBlocks))
            <div class="space-y-4">
                @foreach($pollBlocks as $poll)
                    @php
                        $durationLabel = ($poll['duration_minutes'] ?? 0) > 0
                            ? ($poll['duration_minutes'] . ' dk')
                            : 'Suresiz';
                        $userVote = $poll['user_vote'];
                    @endphp
                    <div class="rounded-xl p-4 shadow-sm bg-card-light dark:bg-card-dark" data-poll data-block-id="{{ $poll['id'] }}">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-xs font-semibold uppercase tracking-[0.4em] text-gray-400">Anket</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $durationLabel }}
                                @if($poll['expired'])
                                    <span class="ml-2 rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700">Bitti</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $poll['question'] }}</div>
                        <div class="mt-3 space-y-2">
                            @foreach($poll['options'] as $idx => $option)
                                @php
                                    $percentage = $poll['percentages'][$idx] ?? 0;
                                    $isSelected = $userVote !== null && (int) $userVote === (int) $idx;
                                @endphp
                                <button
                                    type="button"
                                    data-poll-option
                                    data-option-index="{{ $idx }}"
                                    class="w-full rounded-xl px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-200 transition {{ $isSelected ? 'bg-emerald-50' : 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                                    @if($poll['expired']) disabled @endif
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium">{{ $option }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400" data-poll-percent>{{ $percentage }}%</span>
                                    </div>
                                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700/50">
                                        <div class="h-full rounded-full bg-emerald-500" data-poll-bar style="width: {{ $percentage }}%"></div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400" data-poll-total>
                            {{ $poll['total'] }} oy
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($videoFallbacks->isNotEmpty())
            @foreach($videoFallbacks as $video)
                <figure class="my-4">
                    @include('components.custom-video-player', ['video' => $video])
                </figure>
            @endforeach
        @endif
    </div>

    @if($post->tags?->isNotEmpty())
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-5 shadow-sm sm:p-6">
            <div class="flex flex-wrap gap-2 text-xs">
                @foreach ($post->tags as $tag)
                    <a href="{{ route('blog.posts', ['tag' => $tag->slug]) }}"
                       class="rounded-full bg-gray-100 dark:bg-gray-700/50 px-2 py-1 font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600/60">
                        #{{ $tag->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Reaction system (ana sayfa ile ayni davranis) --}}
    @include('blog.post.reactions', [
        'post' => $post,
        'reactionSummary' => $reactionSummary ?? [],
        'reactionTypes' => $reactionTypes ?? [],
        'commentsCount' => isset($post->comments) ? $post->comments->count() : 0,
        'bookmarkUrl' => $bookmarkUrl,
        'isBookmarked' => (bool) ($isBookmarked ?? false),
    ])
</article>



