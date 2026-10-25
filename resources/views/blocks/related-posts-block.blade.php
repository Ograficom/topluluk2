@php
    $ids = collect(data_get($block->data, 'post_ids', []))
        ->filter(fn ($id) => is_numeric($id))
        ->map(fn ($id) => (int) $id)
        ->unique()->take(6)->values();
    $relatedPosts = $ids->isEmpty() ? collect() : \App\Models\Post::query()
        ->whereIn('id', $ids)
        ->where('is_published', true)
        ->where(function ($query): void {
            $query->whereNull('published_at')->orWhere('published_at', '<=', now());
        })
        ->get(['id', 'title', 'slug', 'excerpt', 'featured_image']);
@endphp
@if ($relatedPosts->isNotEmpty())
    <section class="my-7" aria-label="İlgili gönderiler">
        <h3 class="mb-3 text-base font-semibold text-slate-900">İlgili gönderiler</h3>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach ($relatedPosts as $relatedPost)
                <a href="{{ route('blog.post', ['post' => $relatedPost]) }}" class="block rounded-lg border border-slate-200 p-3 no-underline hover:bg-slate-50">
                    <span class="font-medium text-slate-900">{{ $relatedPost->title }}</span>
                    @if (filled($relatedPost->excerpt))
                        <span class="mt-1 block text-sm text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags($relatedPost->excerpt), 140) }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
