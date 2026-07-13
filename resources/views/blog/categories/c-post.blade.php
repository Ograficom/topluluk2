@php
    use Illuminate\Support\Str;

    $blocked        = $blocked ?? (($hasBlockedUser ?? false) || ($isBlockedByUser ?? false));
    $postCollection = $postCollection ?? ($posts ?? collect());
    $followers      = $followers ?? collect();
    $followings     = $followings ?? collect();
    $sort           = $sort ?? request('sort', 'new');
    $postsSource    = $postsSource
        ?? (is_object($postCollection) && method_exists($postCollection, 'links') ? $postCollection : null);
    $isPaginator    = $isPaginator
        ?? (is_object($postsSource) && method_exists($postsSource, 'hasPages'));
@endphp

<section class="bg-white rounded-[15px]" data-tab-root="profile-tabs" data-tab-initial="posts">
    <div class="flex flex-wrap items-center gap-3 px-4 sm:px-8 py-4">
        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-700" data-tab-group>
            @foreach(['posts' => 'Yayinlar', 'comments' => 'Yorumlar', 'followers' => 'Takipciler', 'following' => 'Takip Edilen'] as $value => $label)
                <button type="button"
                    data-tab-target="{{ $value }}"
                    data-base-class="rounded-xl px-3 py-1.5 ring-1 transition focus:outline-none text-xs"
                    class="rounded-xl px-3 py-1.5 ring-1 transition focus:outline-none text-xs"
                    data-active-class="bg-slate-900 text-white ring-slate-900"
                    data-inactive-class="bg-white text-slate-700 ring-slate-200 hover:bg-slate-50"
                    role="tab"
                    aria-controls="profile-tabs-{{ $value }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="relative ml-auto text-xs" data-sort-menu>
            <button type="button"
                class="inline-flex items-center gap-2 rounded-[6px] px-3 py-1.5 font-semibold text-slate-800 focus:outline-none"
                aria-expanded="false"
                data-sort-toggle>
                <span data-sort-label>{{ ($sort ?? 'new') === 'popular' ? 'Populer' : 'En Yeni' }}</span>
                <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
            <div class="absolute right-0 mt-1 hidden w-36 overflow-hidden rounded-[6px] text-left" data-sort-list>
                <button type="button" data-sort-value="new" class="flex w-full items-center px-3 py-2 text-left text-xs font-semibold text-slate-800 hover:bg-slate-50">
                    En Yeni
                </button>
                <button type="button" data-sort-value="popular" class="flex w-full items-center px-3 py-2 text-left text-xs font-semibold text-slate-800 hover:bg-slate-50">
                    Populer
                </button>
            </div>
        </div>
    </div>

    <div class="p-6 sm:p-8 space-y-6" data-tab-panel="posts" id="profile-tabs-posts" role="tabpanel" tabindex="0">
        <div class="grid grid-cols-1 gap-5">
            @forelse($postCollection as $post)
                @php
                    $excerpt = Str::limit(strip_tags($post->excerpt ?? $post->content ?? ''), 140);
                    $authorAvatar = optional($post->author)->profile_photo_url ?? 'https://placehold.co/80x80';
                    $categoryAvatar = optional($post->category)->profile_image_url
                        ?? optional($post->category)->profile_image
                        ?? 'https://placehold.co/80x80';
                    $featuredImage = $post->featured_image_url
                        ?? $post->featured_image
                        ?? $post->cover_image
                        ?? 'https://placehold.co/640x360/0ea5e9/ffffff?text=OGrafi';
                    $dateLabel = optional($post->published_at)->translatedFormat('d M') ?? 'Taslak';
                @endphp

                <article class="group flex h-full flex-col overflow-hidden rounded-[15px] transition hover:-translate-y-1">
                    <a href="{{ route('blog.post', $post) }}" class="relative block aspect-[4/3] overflow-hidden bg-slate-100" aria-label="{{ $post->title ?: 'Gonderi' }}">
                        <img src="{{ $featuredImage }}" alt="{{ $post->title ?: 'Gonderi' }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/30 via-transparent to-transparent"></div>

                        @if($post->category)
                            <span class="absolute left-3 top-3 inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-[11px] font-semibold text-slate-700">
                                <span class="h-5 w-5 overflow-hidden rounded-full bg-slate-200">
                                    <img src="{{ $categoryAvatar }}" alt="{{ $post->category->name }}" class="h-full w-full object-cover">
                                </span>
                                {{ $post->category->name }}
                            </span>
                        @endif

                        <span class="absolute right-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-semibold text-slate-700">
                            {{ $dateLabel }}
                        </span>
                    </a>

                    <div class="flex h-full flex-col p-4 space-y-3">
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <span>{{ number_format($post->views_count ?? 0) }} goruntulenme</span>
                            <span>·</span>
                            <span>{{ number_format($post->comments_count ?? 0) }} yorum</span>
                        </div>

                        <h2 class="text-lg font-semibold leading-snug text-slate-900 line-clamp-2 group-hover:text-gray-700 transition">
                            <a href="{{ route('blog.post', $post) }}" aria-label="{{ $post->title ?: 'Gonderi' }}">{{ $post->title }}</a>
                        </h2>

                        @if($excerpt)
                            <p class="text-sm text-slate-600 line-clamp-2">{{ $excerpt }}</p>
                        @endif

                        <div class="mt-auto flex items-center gap-2 pt-1">
                            <img src="{{ $authorAvatar }}" alt="{{ optional($post->author)->name }}" class="h-8 w-8 rounded-full object-cover">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900 truncate">{{ optional($post->author)->name ?? 'Topluluk' }}</p>
                                <p class="text-xs text-slate-500">{{ optional($post->published_at)->translatedFormat('d M Y') ?? 'Taslak' }}</p>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[15px] bg-slate-50 p-10 text-center text-sm text-slate-600">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-[15px] text-slate-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 7h16M4 12h10m-6 5h6"/></svg>
                    </div>
                    Henuz yayin yok. Takip ederek yeni paylasimlardan haberdar olun.
                </div>
            @endforelse
        </div>

        @if($isPaginator && $postsSource && method_exists($postsSource, 'hasPages') && $postsSource->hasPages())
            <div class="pt-2">
                {{ $postsSource->links() }}
            </div>
        @endif
    </div>

    <div class="p-6 sm:p-8" data-tab-panel="comments" id="profile-tabs-comments" role="tabpanel" tabindex="0" hidden>
        <div class="rounded-[15px] bg-slate-50 p-6 text-sm text-slate-600">
            Yorumlar yakinda burada gorunecek.
        </div>
    </div>

    <div class="p-6 sm:p-8 space-y-4" data-tab-panel="followers" id="profile-tabs-followers" role="tabpanel" tabindex="0" hidden>
        @if($followers->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($followers as $person)
                    @php
                        $avatar = $person->profile_photo_url ?? 'https://placehold.co/80x80';
                    @endphp
                    <a href="{{ route('users.show', $person) }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:-translate-y-[1px] hover:bg-gray-100">
                        <img src="{{ $avatar }}" alt="{{ $person->name }}" class="h-10 w-10 rounded-full object-cover">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-slate-900 truncate">{{ $person->name }}</div>
                            <div class="text-xs text-slate-500 truncate">{{ '@' . ($person->username ?? 'kullanici') }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-[15px] bg-slate-50 p-6 text-sm text-slate-600">
                Henuz takipci yok.
            </div>
        @endif
    </div>

    <div class="p-6 sm:p-8 space-y-4" data-tab-panel="following" id="profile-tabs-following" role="tabpanel" tabindex="0" hidden>
        @if($followings->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($followings as $person)
                    @php
                        $avatar = $person->profile_photo_url ?? 'https://placehold.co/80x80';
                    @endphp
                    <a href="{{ route('users.show', $person) }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:-translate-y-[1px] hover:bg-gray-100">
                        <img src="{{ $avatar }}" alt="{{ $person->name }}" class="h-10 w-10 rounded-full object-cover">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-slate-900 truncate">{{ $person->name }}</div>
                            <div class="text-xs text-slate-500 truncate">{{ '@' . ($person->username ?? 'kullanici') }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-[15px] bg-slate-50 p-6 text-sm text-slate-600">
                Henuz takip edilen yok.
            </div>
        @endif
    </div>
</section>

@once
    <script>
        (() => {
            const setupTabRoot = (root) => {
                if (!root || root.dataset.tabReady === 'true') return;
                root.dataset.tabReady = 'true';

                const buttons = Array.from(root.querySelectorAll('[data-tab-target]'));
                const panels = Array.from(root.querySelectorAll('[data-tab-panel]'));
                if (!buttons.length || !panels.length) return;

                const applyClasses = (button, isActive) => {
                    const base = button.dataset.baseClass || button.getAttribute('class') || '';
                    const active = button.dataset.activeClass || '';
                    const inactive = button.dataset.inactiveClass || '';
                    const classes = `${base} ${isActive ? active : inactive}`.trim().split(/\s+/).filter(Boolean);
                    button.className = classes.join(' ');
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    button.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    button.tabIndex = isActive ? 0 : -1;
                };

                const activate = (target) => {
                    panels.forEach((panel) => {
                        const match = panel.dataset.tabPanel === target;
                        panel.toggleAttribute('hidden', !match);
                        panel.setAttribute('aria-hidden', match ? 'false' : 'true');
                    });

                    buttons.forEach((button) => applyClasses(button, button.dataset.tabTarget === target));
                };

                root.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-tab-target]');
                    if (!button || !root.contains(button)) return;
                    event.preventDefault();
                    activate(button.dataset.tabTarget);
                });

                const initial = root.dataset.tabInitial
                    || panels.find((panel) => !panel.hasAttribute('hidden'))?.dataset.tabPanel
                    || buttons[0]?.dataset.tabTarget;

                if (initial) activate(initial);
            };

            const initTabGroups = () => {
                document.querySelectorAll('[data-tab-root]').forEach(setupTabRoot);
            };

            const initSortMenu = () => {
                const sortMenu = document.querySelector('[data-sort-menu]');
                if (!sortMenu) return;
                const toggle = sortMenu.querySelector('[data-sort-toggle]');
                const list = sortMenu.querySelector('[data-sort-list]');
                const label = sortMenu.querySelector('[data-sort-label]');

                const setOpen = (open) => {
                    if (!list) return;
                    list.classList.toggle('hidden', !open);
                    toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
                };

                sortMenu.querySelectorAll('[data-sort-value]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const value = btn.getAttribute('data-sort-value') || '';
                        const text = btn.textContent?.trim() || '';
                        if (label) label.textContent = text;
                        setOpen(false);
                        window.location = '?sort=' + encodeURIComponent(value);
                    });
                });

                toggle?.addEventListener('click', (e) => {
                    e.preventDefault();
                    const isOpen = !list?.classList.contains('hidden');
                    setOpen(!isOpen);
                });

                document.addEventListener('click', (e) => {
                    if (!sortMenu.contains(e.target)) setOpen(false);
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    initTabGroups();
                    initSortMenu();
                });
            } else {
                initTabGroups();
                initSortMenu();
            }
        })();
    </script>
@endonce


