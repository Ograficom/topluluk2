@php
    use Illuminate\Support\Str;

    $user             = $user ?? auth()->user();
    $userName         = optional($user)->name ?? 'Topluluk';
    $userUsername     = optional($user)->username ?? null;
    $avatar           = $user->profile_photo_url ?? $user->photo ?? $user->avatar ?? null;
    $initials         = $initials ?? Str::upper(Str::substr($userName, 0, 2));
    $postsCount       = $postsCount ?? ($user->posts_count ?? 0);
    $followersCount   = $followersCount ?? ($user->followers_count ?? 0);
    $followingsCount  = $followingsCount ?? ($user->followings_count ?? 0);
    $totalCategories  = $totalCategories ?? 0;
    $listedCategories = $listedCategories ?? 0;
    $listedPosts      = $listedPosts ?? 0;
    $perPage          = $perPage ?? 20;
    $topCategories    = collect($topCategories ?? [])->take(6);
@endphp

<section class="rounded-xl p-5" data-category-hero>
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Kategoriler</p>
            <h1 class="text-2xl font-semibold text-slate-900">Topluluk atlasi</h1>
            <p class="max-w-3xl text-sm text-slate-600">
                Yazilari konulara gore gez, arama ve filtrelerle ilgilendigin basliklari hizla bul.
            </p>
            <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-700">
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                    Toplam {{ $totalCategories }}
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                    Bu sayfada <span data-category-visible-count class="text-slate-900">{{ $listedCategories }}</span>
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                    Yazi {{ number_format($listedPosts) }}
                </span>
            </div>
        </div>

        <div class="w-full max-w-sm rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="relative h-12 w-12 overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200">
                    @if ($avatar)
                        <img src="{{ $avatar }}" alt="{{ $userName }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-sm font-semibold text-slate-700">
                            {{ $initials }}
                        </div>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate">{{ $userName }}</p>
                    @if ($userUsername)
                        <p class="text-xs text-slate-500 truncate">{{ '@' . $userUsername }}</p>
                    @endif
                </div>
            </div>
            <div class="mt-3 grid grid-cols-3 gap-2 text-center text-[11px] font-semibold text-slate-600">
                <div class="rounded-lg bg-slate-50 px-3 py-2">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Gonderi</p>
                    <p class="text-sm font-bold text-slate-900">{{ number_format($postsCount) }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 px-3 py-2">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Takipci</p>
                    <p class="text-sm font-bold text-slate-900">{{ number_format($followersCount) }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 px-3 py-2">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Takip</p>
                    <p class="text-sm font-bold text-slate-900">{{ number_format($followingsCount) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-700" data-category-filters>
            @foreach ([
                ['key' => 'all', 'label' => 'Hepsi'],
                ['key' => 'popular', 'label' => 'Populer'],
                ['key' => 'new', 'label' => 'Yeni'],
            ] as $filter)
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 focus:outline-none-0"
                    style="-webkit-tap-highlight-color: transparent;"
                    data-category-filter="{{ $filter['key'] }}"
                >
                    <span>{{ $filter['label'] }}</span>
                </button>
            @endforeach
    </div>

    @if($topCategories->count())
        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">One cikanlar</span>
            @foreach($topCategories as $item)
                <a
                    href="{{ route('blog.category', $item) }}"
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-slate-700"
                >
                    <span class="font-semibold">{{ $item->name }}</span>
                    <span class="text-[11px] text-slate-400">{{ $item->posts_count }} yazi</span>
                    <span class="text-slate-500">-></span>
                </a>
            @endforeach
        </div>
    @endif
</section>



