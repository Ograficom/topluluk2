@php
    use Illuminate\Support\Str;

    $categoryList = $categories ?? collect();
    if (is_object($categoryList) && method_exists($categoryList, 'getCollection')) {
        $categoryList = $categoryList->getCollection();
    }

    $perPage = method_exists($categories, 'perPage') ? (int) $categories->perPage() : collect($categoryList)->count();
    $totalCategories = method_exists($categories, 'total') ? (int) $categories->total() : collect($categoryList)->count();
    $visibleCategories = collect($categoryList)->count();
    $currentPage = method_exists($categories, 'currentPage') ? (int) $categories->currentPage() : 1;
    $viewer = auth()->user();
    $canCreateCategory = $viewer && !$viewer->isBlockedFrom('categories');
@endphp

<section class="space-y-3">
    <div class="grid grid-cols-1 gap-3">
        @forelse ($categories as $category)
            @php
                $cover = $category->cover_image_url ?? $category->cover_image ?? null;
                $profile = $category->profile_image_url ?? $category->profile_image ?? null;
                $name = (string) ($category->name ?? '');
                $initials = Str::upper(Str::substr($name, 0, 2));
                $featured = $profile ?: $cover;
                $isJoined = (bool) ($category->is_joined ?? false);
            @endphp

            <div class="profile-post-card-wrapper">
                <div class="profile-public-card flex items-center justify-between gap-4 rounded-[16px] bg-white p-4">
                    <a
                        href="{{ route('blog.category', $category) }}"
                        class="flex min-w-0 items-center gap-3 focus:outline-none"
                        style="-webkit-tap-highlight-color: transparent;"
                    >
                        <div class="h-14 w-14 overflow-hidden rounded-full bg-slate-100">
                            @if ($featured)
                                <img src="{{ $featured }}" alt="{{ $name }}" class="h-full w-full object-cover" loading="lazy">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-xs font-medium text-slate-700">
                                    {{ $initials }}
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $name }}</p>
                            <p class="mt-1 text-xs text-slate-500">Kategori</p>
                        </div>
                    </a>

                    @if ($viewer)
                        <form method="POST" action="{{ route('blog.category.join', $category) }}" class="m-0 shrink-0">
                            @csrf
                            <button type="submit" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">
                                {{ $isJoined ? 'Takiptesin' : 'Takip et' }}
                            </button>
                        </form>
                    @elseif(\Illuminate\Support\Facades\Route::has('login'))
                        <a href="{{ route('login') }}" class="shrink-0 rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">
                            Takip et
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="profile-reference-empty">
                Henuz kategori bulunmuyor.
            </div>
        @endforelse
    </div>

    @if (method_exists($categories, 'links'))
        <div class="flex flex-wrap items-center justify-end gap-3 pt-2 text-xs text-slate-500">
            <div class="w-full">
                {{ $categories->links() }}
            </div>
        </div>
    @endif
</section>
