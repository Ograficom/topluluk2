@php
    $reactionTypesAll = collect($reactionTypes ?? []);

    if ($reactionTypesAll->isEmpty()) {
        $reactionTypesAll = \App\Models\ReactionType::query()
            ->where('is_active', true)
            ->get(['id', 'label', 'short_code', 'emoji', 'gif_url']);
    }

    $reactionSummaryAll = collect($reactionSummary ?? []);

    $typeIdByShort = $reactionTypesAll->mapWithKeys(function ($type) {
        $short = $type['short_code'] ?? ($type->short_code ?? null);
        $id = $type['id'] ?? ($type->id ?? null);

        return $short ? [$short => $id] : [];
    });

    $reactionPills = $reactionSummaryAll
        ->map(function ($row) use ($typeIdByShort) {
            $short = $row['short_code'] ?? null;
            $count = (int) ($row['count'] ?? 0);

            if (! $short || $count <= 0) {
                return null;
            }

            return [
                'type_id' => $typeIdByShort[$short] ?? null,
                'short_code' => $short,
                'label' => $row['label'] ?? null,
                'emoji' => $row['emoji'] ?? null,
                'gif_url' => $row['gif_url'] ?? null,
                'icon' => $row['emoji'] ?? ($row['gif_url'] ?? null),
                'count' => $count,
            ];
        })
        ->filter()
        ->values();

    $uid = 'rxbar_' . substr(md5(($post->id ?? 'post') . uniqid('', true)), 0, 8);
    $reactionTypesForPicker = $reactionTypesAll
        ->map(fn ($t) => [
            'id' => $t['id'] ?? ($t->id ?? null),
            'short_code' => $t['short_code'] ?? ($t->short_code ?? null),
            'emoji' => $t['emoji'] ?? ($t->emoji ?? null),
            'gif_url' => $t['gif_url'] ?? ($t->gif_url ?? null),
            'label' => $t['label'] ?? ($t->label ?? null),
        ])
        ->values();

    $commentsCount = (int) ($commentsCount ?? ($post->comments_count ?? (isset($post->comments) ? $post->comments->count() : 0)));
    $bookmarkUrl = $bookmarkUrl ?? null;
    $isBookmarked = (bool) ($isBookmarked ?? false);
    $bookmarkOutlineIcon = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m10.94 18.339l-3.43 2.548a1.71 1.71 0 0 1-2.76-1.23V6.35a3.735 3.735 0 0 1 3.87-3.597h6.76a3.742 3.742 0 0 1 3.87 3.597v13.309a1.708 1.708 0 0 1-2.76 1.229l-3.43-2.548a1.801 1.801 0 0 0-2.12 0"/>
</svg>
SVG;
    $bookmarkFilledIcon = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" fill-rule="evenodd" d="M7 2a3 3 0 0 0-3 3v15.138a1.5 1.5 0 0 0 2.244 1.303l5.26-3.006a1 1 0 0 1 .992 0l5.26 3.006A1.5 1.5 0 0 0 20 20.138V5a3 3 0 0 0-3-3H7z" clip-rule="evenodd"/>
</svg>
SVG;
    $showRepost = (bool) ($showRepost ?? true);
    $hideShare = (bool) ($hideShare ?? false);
    $hideOverflowToggle = (bool) ($hideOverflowToggle ?? false);
    $shareUrl = route('blog.post', $post);
    $shareTitle = (string) ($post->title ?? '');
    $shareUid = 'share_' . substr(md5(($post->id ?? 'post') . uniqid('', true)), 0, 8);
    $maxVisibleReactions = 7;
    $initialOverflowCount = max($reactionPills->count() - $maxVisibleReactions, 0);
@endphp

<div
    class="relative rounded-xl bg-card-light p-4 shadow-sm dark:bg-card-dark"
    data-reaction-root="{{ $uid }}"
    data-share-root="{{ $shareUid }}"
    data-reaction-max-visible="{{ $maxVisibleReactions }}"
    data-reaction-post-url="{{ route('blog.post.reaction', $post) }}"
    data-reaction-post-id="{{ $post->id }}"
>
    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center gap-2" data-reaction-pills>
            @foreach ($reactionPills as $index => $reaction)
                @php
                    $icon = $reaction['icon'] ?? ($reaction['emoji'] ?? null);

                    if (! $icon && ! empty($reaction['gif_url'])) {
                        $icon = $reaction['gif_url'];
                    }

                    if (
                        is_string($icon)
                        && ! \Illuminate\Support\Str::startsWith($icon, ['http://', 'https://', '//', '/'])
                        && preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $icon)
                    ) {
                        $icon = \Illuminate\Support\Str::startsWith($icon, 'storage/')
                            ? url('/' . ltrim($icon, '/'))
                            : asset('storage/' . ltrim($icon, '/'));
                    }

                    $isImage = is_string($icon) && preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $icon);

                    if (
                        $isImage
                        || (is_string($icon) && \Illuminate\Support\Str::startsWith($icon, ['http://', 'https://', '/storage', '/uploads']))
                    ) {
                        $icon = '<img src="' . e($icon) . '" alt="" class="h-5 w-5 rounded-full object-cover">';
                    }
                @endphp

                @continue(empty($icon))

                <div
                    class="rx-summary-pill"
                    data-reaction-pill
                    data-reaction-short="{{ $reaction['short_code'] ?? '' }}"
                    @if (! empty($reaction['type_id'])) data-reaction-id="{{ $reaction['type_id'] }}" @endif
                    @if ($index >= $maxVisibleReactions) hidden @endif
                >
                    <span class="rx-summary-pill__icon">{!! $icon !!}</span>
                    <span class="rx-summary-pill__count" data-reaction-count>{{ number_format((int) ($reaction['count'] ?? 0)) }}</span>
                </div>
            @endforeach

            @unless ($hideOverflowToggle)
                <button
                    type="button"
                    class="rx-summary-pill rx-summary-pill--overflow"
                    data-reaction-overflow-toggle
                    data-collapsed-label="Daha fazla"
                    data-expanded-label="Daha az"
                    aria-expanded="false"
                    @if ($initialOverflowCount === 0) hidden @endif
                >
                    <span data-reaction-overflow-label>Daha fazla</span>
                    <span class="rx-summary-pill__count" data-reaction-overflow-count>@if ($initialOverflowCount > 0){{ $initialOverflowCount }}@endif</span>
                </button>
            @endunless

            @include('blog.reaction', [
                'count' => '',
                'icon' => null,
                'label' => null,
                'isAdd' => true,
                'gifs' => $reactionTypesForPicker,
            ])
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="#comments"
                class="inline-flex h-9 items-center justify-center gap-2 rounded-full bg-gray-100 px-3 text-gray-700 transition-colors hover:bg-gray-200 focus:outline-none focus-visible:outline-none dark:bg-gray-700/50 dark:text-gray-200 dark:hover:bg-gray-600/60"
                style="-webkit-tap-highlight-color: transparent;"
                title="Yorum ({{ number_format($commentsCount) }})"
            >
                <iconify-icon icon="lucide:message-circle" class="h-5 w-5 text-[1.15rem]"></iconify-icon>
                <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ number_format($commentsCount) }}</span>
                <span class="sr-only">Yorum</span>
            </a>

            @if ($bookmarkUrl)
                <form action="{{ $bookmarkUrl }}" method="POST" class="m-0">
                    @csrf
                    <button
                        type="submit"
                        class="post-reaction-bookmark-btn inline-flex h-9 w-9 items-center justify-center rounded-full transition-colors focus:outline-none focus-visible:outline-none {{ $isBookmarked ? 'is-bookmarked' : '' }}"
                        style="-webkit-tap-highlight-color: transparent;"
                        title="{{ $isBookmarked ? 'Kaydedildi' : 'Kaydet' }}"
                    >
                        {!! $isBookmarked ? $bookmarkFilledIcon : $bookmarkOutlineIcon !!}
                        <span class="sr-only">{{ $isBookmarked ? 'Kaydedildi' : 'Kaydet' }}</span>
                    </button>
                </form>
            @endif

            @unless ($hideShare)
                <div class="relative" data-share-wrap>
                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700/50 dark:text-gray-200 dark:hover:bg-gray-600/60"
                        data-share-btn
                        data-share-url="{{ $shareUrl }}"
                        title="Paylas"
                    >
                        <iconify-icon icon="lucide:corner-up-right" class="h-5 w-5 text-[1.15rem]"></iconify-icon>
                        <span class="sr-only">Paylas</span>
                    </button>

                    <div
                        class="absolute right-0 top-full z-50 mt-2 hidden w-56 rounded-xl bg-white p-3 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800"
                        data-share-popup="{{ $shareUid }}"
                        style="max-width: calc(100vw - 24px);"
                    >
                        <div class="mb-3 flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-semibold text-gray-800 dark:text-gray-100">Paylas</span>
                            <button type="button" class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200" data-share-close="{{ $shareUid }}" aria-label="Kapat">
                                <span class="text-lg leading-none">&times;</span>
                            </button>
                        </div>

                        <div class="space-y-2">
                            <button type="button" data-repost-now="{{ $shareUid }}" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                <iconify-icon icon="lucide:repeat-2" class="h-4 w-4 text-[1rem]"></iconify-icon>
                                Yeniden paylas
                            </button>

                            <a href="{{ route('blog.repost.create', ['post' => $post->id]) }}?repost_url={{ urlencode($shareUrl) }}&repost_title={{ urlencode($shareTitle) }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                <iconify-icon icon="lucide:square-pen" class="h-5 w-5 text-[1.1rem]"></iconify-icon>
                                Alinti yap
                            </a>

                            <a href="https://wa.me/?text={{ urlencode($shareTitle . ' ' . $shareUrl) }}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-4.783 1.14L4.734 5.005 5.9 8.678a9.87 9.87 0 001.414 4.53h.005c2.498 2.25 6.165 2.759 9.195 1.265 3.03-1.495 5.058-4.808 4.63-8.136-1.048-8.047-10.392-11.127-16.941-5.402"/></svg>
                                WhatsApp
                            </a>

                            <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareTitle) }}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a14.028 14.028 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                Twitter
                            </a>

                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                Facebook
                            </a>

                            <button type="button" data-copy-link="{{ $shareUid }}" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                <iconify-icon icon="lucide:copy" class="h-5 w-5 text-[1.1rem]"></iconify-icon>
                                Linki Kopyala
                            </button>
                        </div>
                    </div>
                </div>
            @endunless
        </div>
    </div>

    <div class="pointer-events-none absolute bottom-4 left-4 hidden rounded-xl bg-gray-900 px-3 py-2 text-sm text-white shadow-lg" data-share-toast="{{ $shareUid }}">
        Link kopyalandi
    </div>
    <div class="pointer-events-none absolute bottom-4 left-4 hidden rounded-xl bg-gray-900 px-3 py-2 text-sm text-white shadow-lg" data-repost-toast="{{ $shareUid }}">
        Yeniden paylasma icin link kopyalandi
    </div>
</div>

@once
    <style>
        .rx-summary-pill {
            display: inline-flex;
            min-height: 36px;
            flex-shrink: 0;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            border-radius: 999px;
            background: #f3f4f6;
            padding: 6px 12px;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
            line-height: 1;
        }

        .rx-summary-pill__icon {
            display: inline-flex;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            line-height: 1;
        }

        .rx-summary-pill__count {
            display: inline-flex;
            flex-shrink: 0;
            align-items: center;
            font-size: 13px;
            font-weight: 600;
            line-height: 1;
        }

        .rx-summary-pill--overflow {
            cursor: pointer;
            transition: background-color .2s ease, color .2s ease;
        }

        .rx-summary-pill--overflow:hover {
            background: #e5e7eb;
        }

        html.dark .rx-summary-pill {
            background: rgba(51, 65, 85, 0.78);
            color: #e5e7eb;
        }

        html.dark .rx-summary-pill--overflow:hover {
            background: rgba(71, 85, 105, 0.86);
        }

        .post-reaction-bookmark-btn {
            background: #f3f4f6;
            color: #374151;
        }

        .post-reaction-bookmark-btn:hover,
        .post-reaction-bookmark-btn:focus,
        .post-reaction-bookmark-btn:focus-visible {
            background: #e5e7eb;
            color: #111827;
        }

        .post-reaction-bookmark-btn svg {
            width: 1.15rem;
            height: 1.15rem;
            flex: 0 0 1.15rem;
            display: block;
        }

        .post-reaction-bookmark-btn.is-bookmarked,
        .post-reaction-bookmark-btn.is-bookmarked:hover,
        .post-reaction-bookmark-btn.is-bookmarked:focus,
        .post-reaction-bookmark-btn.is-bookmarked:focus-visible {
            background: transparent;
            color: #16a34a;
        }

        html.dark .post-reaction-bookmark-btn {
            background: rgba(55, 65, 81, 0.72);
            color: #e5e7eb;
        }

        html.dark .post-reaction-bookmark-btn:hover,
        html.dark .post-reaction-bookmark-btn:focus,
        html.dark .post-reaction-bookmark-btn:focus-visible {
            background: rgba(75, 85, 99, 0.9);
            color: #f8fafc;
        }

        html.dark .post-reaction-bookmark-btn.is-bookmarked,
        html.dark .post-reaction-bookmark-btn.is-bookmarked:hover,
        html.dark .post-reaction-bookmark-btn.is-bookmarked:focus,
        html.dark .post-reaction-bookmark-btn.is-bookmarked:focus-visible {
            background: transparent;
            color: #22c55e;
        }
    </style>
@endonce

<script>
    (function () {
        const reactionRoot = document.querySelector('[data-reaction-root="{{ $uid }}"]');

        if (! reactionRoot) return;

        const postReactionUrl = reactionRoot.getAttribute('data-reaction-post-url') || '';
        const postId = reactionRoot.getAttribute('data-reaction-post-id') || '';
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name="_token"]')?.value
            || '';
        const reactionGuestRedirectUrl = @json(route('login'));
        const isGuestReactionUser = {{ auth()->check() ? 'false' : 'true' }};
        const pillsContainer = reactionRoot.querySelector('[data-reaction-pills]');
        const overflowToggle = reactionRoot.querySelector('[data-reaction-overflow-toggle]');
        const overflowLabel = overflowToggle?.querySelector('[data-reaction-overflow-label]');
        const overflowCountEl = overflowToggle?.querySelector('[data-reaction-overflow-count]');
        const maxVisible = parseInt(reactionRoot.getAttribute('data-reaction-max-visible') || '7', 10);
        let isExpanded = false;

        const syncOverflowToggle = (overflow) => {
            if (! overflowToggle) return;

            if (overflow <= 0) {
                overflowToggle.hidden = true;
                overflowToggle.setAttribute('aria-expanded', 'false');

                if (overflowCountEl) {
                    overflowCountEl.textContent = '';
                }

                return;
            }

            const collapsedLabel = overflowToggle.getAttribute('data-collapsed-label') || 'Daha fazla';
            const expandedLabel = overflowToggle.getAttribute('data-expanded-label') || 'Daha az';

            overflowToggle.hidden = false;
            overflowToggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');

            if (overflowLabel) {
                overflowLabel.textContent = isExpanded ? expandedLabel : collapsedLabel;
            }

            if (overflowCountEl) {
                overflowCountEl.textContent = isExpanded ? '' : `${overflow}`;
            }
        };

        const updateOverflow = () => {
            if (! pillsContainer) return;

            const pills = Array.from(pillsContainer.querySelectorAll('[data-reaction-pill]'));
            const overflow = Math.max(pills.length - maxVisible, 0);

            if (overflow === 0) {
                isExpanded = false;
            }

            syncOverflowToggle(overflow);

            pills.forEach((pill, index) => {
                pill.hidden = ! isExpanded && index >= maxVisible;
            });
        };

        const incrementReaction = (shortCode, iconHtml) => {
            if (! pillsContainer || ! shortCode) return;

            let pill = pillsContainer.querySelector(`[data-reaction-pill][data-reaction-short="${shortCode}"]`);

            if (! pill) {
                pill = document.createElement('div');
                pill.className = 'rx-summary-pill';
                pill.setAttribute('data-reaction-pill', '');
                pill.setAttribute('data-reaction-short', shortCode);

                const iconSpan = document.createElement('span');
                iconSpan.className = 'rx-summary-pill__icon';
                iconSpan.innerHTML = iconHtml || '?';

                const countSpan = document.createElement('span');
                countSpan.className = 'rx-summary-pill__count';
                countSpan.setAttribute('data-reaction-count', '');
                countSpan.textContent = '1';

                pill.appendChild(iconSpan);
                pill.appendChild(countSpan);

                const pickerWrapper = pillsContainer.querySelector('[data-rx-wrapper]');
                pillsContainer.insertBefore(pill, overflowToggle || pickerWrapper || null);
            } else {
                const countEl = pill.querySelector('[data-reaction-count]');
                const current = parseInt(countEl?.textContent?.replace(/,/g, '') || '0', 10);

                if (countEl) {
                    countEl.textContent = String(current + 1);
                }
            }

            updateOverflow();
        };

        const decrementReaction = (shortCode) => {
            if (! pillsContainer || ! shortCode) return;

            const pill = pillsContainer.querySelector(`[data-reaction-pill][data-reaction-short="${shortCode}"]`);

            if (! pill) return;

            const countEl = pill.querySelector('[data-reaction-count]');
            const current = parseInt(countEl?.textContent?.replace(/,/g, '') || '0', 10);
            const next = current - 1;

            if (next <= 0) {
                pill.remove();
            } else if (countEl) {
                countEl.textContent = String(next);
            }

            updateOverflow();
        };

        overflowToggle?.addEventListener('click', () => {
            isExpanded = ! isExpanded;
            updateOverflow();
        });

        const handleReactionSelected = (event) => {
            if (isGuestReactionUser) {
                window.location.href = reactionGuestRedirectUrl;
                return;
            }

            if (! postReactionUrl || ! csrf) return;

            const activeShort = reactionRoot.getAttribute('data-reaction-active') || '';
            const id = event.detail?.reaction_type_id;
            const uid = event.detail?.uid;
            const detailShort = event.detail?.short_code;

            if (uid && ! reactionRoot.querySelector(`[data-rx-wrapper="${uid}"]`)) {
                return;
            }

            const shortCode = detailShort || '';
            const iconHtml = event.detail?.icon_html || '?';

            if (! id && ! shortCode) return;

            const isToggleOff = activeShort && activeShort === shortCode;
            const nextShort = isToggleOff ? '' : shortCode;

            if (isToggleOff) {
                decrementReaction(shortCode);
            } else {
                if (activeShort && activeShort !== shortCode) {
                    decrementReaction(activeShort);
                }

                incrementReaction(shortCode, iconHtml);
            }

            reactionRoot.setAttribute('data-reaction-active', nextShort);

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = postReactionUrl;
            form.className = 'hidden';

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrf;
            form.appendChild(tokenInput);

            if (id) {
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'reaction_type_id';
                idInput.value = id;
                form.appendChild(idInput);
            }

            if (shortCode) {
                const codeInput = document.createElement('input');
                codeInput.type = 'hidden';
                codeInput.name = 'short_code';
                codeInput.value = shortCode;
                form.appendChild(codeInput);
            }

            if (postId) {
                const pidInput = document.createElement('input');
                pidInput.type = 'hidden';
                pidInput.name = 'post_id';
                pidInput.value = postId;
                form.appendChild(pidInput);
            }

            document.body.appendChild(form);
            form.submit();
        };

        reactionRoot.addEventListener('reaction:selected', handleReactionSelected);
        window.addEventListener('reaction:selected', handleReactionSelected);
        updateOverflow();
    })();
</script>

<script>
    (function () {
        const shareRoot = document.querySelector('[data-share-root="{{ $shareUid }}"]');

        if (! shareRoot) return;

        const shareWrap = shareRoot.querySelector('[data-share-wrap]');
        const shareBtn = shareWrap?.querySelector('[data-share-btn]');
        const sharePopup = shareWrap?.querySelector('[data-share-popup="{{ $shareUid }}"]');
        const shareCloseBtn = shareWrap?.querySelector('[data-share-close="{{ $shareUid }}"]');
        const shareToast = shareRoot.querySelector('[data-share-toast="{{ $shareUid }}"]');
        const copyLinkBtn = shareWrap?.querySelector('[data-copy-link="{{ $shareUid }}"]');
        const repostNowBtn = shareWrap?.querySelector('[data-repost-now="{{ $shareUid }}"]');
        const repostToast = shareRoot.querySelector('[data-repost-toast="{{ $shareUid }}"]');

        const closePopup = (popup) => {
            if (! popup) return;
            popup.classList.add('hidden');
        };

        const togglePopup = (popup) => {
            if (! popup) return;
            popup.classList.toggle('hidden');
        };

        shareBtn?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            togglePopup(sharePopup);
        });

        shareCloseBtn?.addEventListener('click', () => closePopup(sharePopup));

        copyLinkBtn?.addEventListener('click', async () => {
            const url = shareBtn?.getAttribute('data-share-url') || window.location.href;

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(url);
                } else {
                    const temp = document.createElement('input');
                    temp.value = url;
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    temp.remove();
                }
            } catch {
                // ignore
            }

            if (shareToast) {
                shareToast.classList.remove('hidden');
                setTimeout(() => shareToast.classList.add('hidden'), 1500);
            }

            closePopup(sharePopup);
        });

        repostNowBtn?.addEventListener('click', async () => {
            const url = shareBtn?.getAttribute('data-share-url') || window.location.href;

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(url);
                } else {
                    const temp = document.createElement('input');
                    temp.value = url;
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    temp.remove();
                }
            } catch {
                // ignore
            }

            if (repostToast) {
                repostToast.classList.remove('hidden');
                setTimeout(() => repostToast.classList.add('hidden'), 1500);
            }

            closePopup(sharePopup);
        });

        document.addEventListener('click', (event) => {
            if (sharePopup && shareWrap && ! shareWrap.contains(event.target)) {
                closePopup(sharePopup);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closePopup(sharePopup);
            }
        });

        window.addEventListener('scroll', () => {
            closePopup(sharePopup);
        }, true);

        window.addEventListener('resize', () => {
            closePopup(sharePopup);
        });
    })();
</script>
