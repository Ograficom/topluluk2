<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Channel;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\RssItem;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:latest,popular,views'],
            'period' => ['nullable', 'in:day,week,month'],
        ]);
        $search = trim($validated['q'] ?? '');
        $sort = $validated['sort'] ?? 'latest';
        $period = $validated['period'] ?? null;
        $viewer = $request->user();

        $posts = Post::query()->published()->whereNull('advertisement_id')->with(['user', 'channel', 'tagRecords', 'comments' => fn ($query) => $query->with('user')->latest()->limit(1)])
            ->withCount('comments')
            ->withCount([
                'reactions as likes_count' => fn (Builder $query) => $query->where('type', PostReaction::LIKE),
                'reactions as dislikes_count' => fn (Builder $query) => $query->where('type', PostReaction::DISLIKE),
            ])
            ->when($viewer, fn (Builder $query) => $query->with(['reactions' => fn (Relation $r) => $r->where('user_id', $viewer->id)]))
            ->when($viewer, fn (Builder $query) => $query->whereNotIn('user_id', $viewer->blockedUsers()->select('users.id'))
                ->whereNotIn('user_id', $viewer->blockedByUsers()->select('users.id')))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $q) => $q
                ->where('title', 'like', "%{$search}%")->orWhere('excerpt', 'like', "%{$search}%")->orWhere('body', 'like', "%{$search}%")))
            ->when($period, fn (Builder $query) => $query->where('published_at', '>=', match ($period) {
                'day' => now()->subDay(), 'week' => now()->subWeek(), 'month' => now()->subMonth(),
            }))
            ->when($sort === 'popular', fn (Builder $query) => $query->orderByDesc('likes_count'))
            ->when($sort === 'views', fn (Builder $query) => $query->orderByDesc('views'))
            ->when($sort === 'latest', fn (Builder $query) => $query->orderByDesc('published_at')->orderByDesc('id'))
            ->paginate(10)->withQueryString();

        $ads = $search === '' ? Advertisement::query()->deliverable()->with(['user', 'post'])->inRandomOrder()->limit(2)->get() : collect();

        $this->setViewerReactions($posts);
        return view('posts.index', compact('posts', 'ads', 'search', 'sort', 'period'));
    }

    public function create(Request $request): View
    {
        return view('posts.create', ['channels' => Channel::query()->active()->orderBy('name')->get(), 'user' => $request->user()]);
    }

    public function edit(Request $request, Post $post): View
    {
        $this->authorizeManage($request, $post);
        return view('posts.create', [
            'channels' => Channel::query()->active()->orderBy('name')->get(),
            'user' => $request->user(),
            'post' => $post,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'channel_id' => ['nullable', Rule::exists('channels', 'id')->where('is_active', true)],
            'excerpt' => ['required', 'string', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:100000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'video_url' => ['nullable', 'url', 'max:2048', function (string $attribute, mixed $value, \Closure $fail): void {
                if (filled($value) && parse_url($value, PHP_URL_SCHEME) !== 'https') $fail('Video bağlantısı HTTPS kullanmalıdır.');
            }],
            'video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime,video/x-matroska', 'max:102400'],
            'tags' => ['nullable', 'string', 'max:500'],
            'is_adult' => ['sometimes', 'boolean'],
        ]);

        $slugBase = Str::limit(Str::slug($validated['title']) ?: 'post', 240, '');
        $slug = $slugBase;
        for ($suffix = 1; Post::query()->where('slug', $slug)->exists(); $suffix++) {
            $slug = Str::limit($slugBase, 240 - strlen((string) $suffix), '').'-'.$suffix;
        }

        $videoPath = $request->file('video_file')?->store('posts/videos', 'public');
        if ($request->hasFile('video_file') && ! $videoPath) throw new \RuntimeException('The post video could not be stored.');

        $coverImage = $request->file('cover_image')?->store('posts', 'public');
        if ($request->hasFile('cover_image') && ! $coverImage) throw new \RuntimeException('The post cover image could not be stored.');

        $mediaPaths = [];
        foreach ($request->file('media', []) as $mediaFile) {
            $path = $mediaFile->store('posts/gallery', 'public');
            if (! $path) throw new \RuntimeException('A gallery image could not be stored.');
            $mediaPaths[] = $path;
        }

        $tags = $this->parseTags($validated['tags'] ?? '');
        $isSpam = app(PostSpamFilter::class)->shouldFlag($validated['title'], $validated['excerpt'], $validated['body']);
        $post = Post::create([
            'user_id' => $request->user()->id,
            'channel_id' => $validated['channel_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'meta_description' => $validated['meta_description'] ?? null,
            'body' => $validated['body'],
            'cover_image' => $coverImage,
            'media' => $mediaPaths,
            'video_url' => $validated['video_url'] ?? null,
            'video_path' => $videoPath,
            'tags' => $tags,
            'is_published' => ! $isSpam,
            'is_spam' => $isSpam,
            'published_at' => $isSpam ? null : now(),
            'is_adult' => $request->boolean('is_adult'),
        ]);
        $this->syncTagRecords($post, $tags);

        return $isSpam
            ? redirect()->route('posts.index')->with('status', 'Postunuz spam filtresine takıldı ve yönetici incelemesine alındı.')
            : redirect()->route('posts.show', $post)->with('status', 'Yazınız yayınlandı.');
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->authorizeManage($request, $post);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'channel_id' => ['nullable', Rule::exists('channels', 'id')->where('is_active', true)],
            'excerpt' => ['required', 'string', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:100000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'video_url' => ['nullable', 'url', 'max:2048', function (string $attribute, mixed $value, \Closure $fail): void {
                if (filled($value) && parse_url($value, PHP_URL_SCHEME) !== 'https') $fail('Video bağlantısı HTTPS kullanmalıdır.');
            }],
            'remove_media' => ['nullable', 'array', 'max:10'],
            'remove_media.*' => ['string', 'max:500'],
            'video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime,video/x-matroska', 'max:102400'],
            'tags' => ['nullable', 'string', 'max:500'],
            'is_adult' => ['sometimes', 'boolean'],
            'remove_cover' => ['sometimes', 'boolean'],
        ]);

        $slugBase = Str::limit(Str::slug($validated['title']) ?: 'post', 240, '');
        $slug = $slugBase;
        for ($suffix = 1; Post::query()->where('slug', $slug)->where('id', '<>', $post->id)->exists(); $suffix++) {
            $slug = Str::limit($slugBase, 240 - strlen((string) $suffix), '').'-'.$suffix;
        }

        $newVideo = $request->file('video_file')?->store('posts/videos', 'public');
        if ($request->hasFile('video_file') && ! $newVideo) throw new \RuntimeException('The post video could not be stored.');

        $newCover = $request->file('cover_image')?->store('posts', 'public');
        if ($request->hasFile('cover_image') && ! $newCover) throw new \RuntimeException('The post cover image could not be stored.');

        $tags = $this->parseTags($validated['tags'] ?? '');
        $isSpam = app(PostSpamFilter::class)->shouldFlag($validated['title'], $validated['excerpt'], $validated['body']);
        $oldCover = $post->cover_image;
        $oldVideo = $post->video_path;
        $existingMedia = collect($post->media ?? []);
        $removeMedia = collect($validated['remove_media'] ?? []);
        foreach ($existingMedia->filter(fn (string $path) => $removeMedia->contains($path)) as $path) Storage::disk('public')->delete($path);
        $mediaPaths = $existingMedia->reject(fn (string $path) => $removeMedia->contains($path))->values();
        foreach ($request->file('media', []) as $mediaFile) {
            $path = $mediaFile->store('posts/gallery', 'public');
            if (! $path) throw new \RuntimeException('A gallery image could not be stored.');
            $mediaPaths->push($path);
        }

        $post->fill([
            'media' => $mediaPaths->all(),
            'video_url' => $validated['video_url'] ?? null,
            'channel_id' => $validated['channel_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'meta_description' => $validated['meta_description'] ?? null,
            'body' => $validated['body'],
            'tags' => $tags,
            'is_adult' => $request->boolean('is_adult'),
            'is_spam' => $isSpam,
        ]);
        if ($isSpam) $post->is_published = false;
        if ($newVideo) $post->video_path = $newVideo;
        if ($newCover) $post->cover_image = $newCover;
        elseif ($request->boolean('remove_cover')) $post->cover_image = null;
        $post->save();
        $this->syncTagRecords($post, $tags);

        if ($oldCover && $oldCover !== $post->cover_image && ! filter_var($oldCover, FILTER_VALIDATE_URL)) Storage::disk('public')->delete($oldCover);
        if ($oldVideo && $oldVideo !== $post->video_path) Storage::disk('public')->delete($oldVideo);

        return $isSpam
            ? redirect()->route('posts.index')->with('status', 'Postunuz spam incelemesine alındı; onaylanana kadar yayında görünmeyecek.')
            : redirect()->route('posts.show', $post)->with('status', 'Yazınız güncellendi.');
    }

    public function show(Request $request, Post $post): View
    {
        abort_unless(! $post->is_spam && $post->is_published && (! $post->published_at || $post->published_at->isPast()), 404);
        if ($post->advertisement_id) {
            $post->load('advertisement');
            $ad = $post->advertisement;
            $isDeliverable = $ad && $ad->status === 'approved' && $ad->starts_at?->isPast() && $ad->ends_at?->isFuture()
                && ($ad->billing_type === 'free' || ($ad->payment_status === 'paid' && $ad->spent_amount < $ad->total_budget));
            abort_unless($isDeliverable || $request->user()?->is($post->user), 404);
        }
        if ($post->is_adult && ! $request->user()?->show_adult_content) return view('posts.adult-warning');

        $post->increment('views');
        $post->load(['user', 'channel', 'tagRecords']);
        $rssItem = RssItem::query()
            ->with('source:id,name')
            ->where('published_post_id', $post->id)
            ->first();
        $replyTo = null;
        if ($request->filled('reply_to')) {
            $replyTo = Comment::query()->where('post_id', $post->id)->whereNull('parent_id')->find($request->query('reply_to'));
            abort_unless($replyTo, 404);
        }

        $commentsQuery = $post->comments()->whereNull('parent_id')->where('is_spam', false)->with(['user', 'replies' => fn ($query) => $query->where('is_spam', false)->with('user')])
            ->withCount('reactions')->withCount(['replies' => fn ($query) => $query->where('is_spam', false)])->with(['replies' => fn ($query) => $query->where('is_spam', false)->withCount('reactions')]);
        if ($request->user()) {
            $commentsQuery->withExists(['reactions as viewer_reacted' => fn (Builder $query) => $query->where('user_id', $request->user()->id)])
                ->with(['replies' => fn ($query) => $query->where('is_spam', false)->withCount('reactions')->withExists([
                    'reactions as viewer_reacted' => fn (Builder $r) => $r->where('user_id', $request->user()->id),
                ])]);
        }
        $post->setRelation('comments', $commentsQuery->get());
        $post->loadCount([
            'comments' => fn (Builder $query) => $query->where('is_spam', false),
            'reactions as likes_count' => fn (Builder $query) => $query->where('type', PostReaction::LIKE),
            'reactions as dislikes_count' => fn (Builder $query) => $query->where('type', PostReaction::DISLIKE),
        ]);
        if ($request->user()) $post->load(['reactions' => fn (Relation $query) => $query->where('user_id', $request->user()->id)]);

        return view('posts.show', compact('post', 'replyTo', 'rssItem'));
    }

    private function parseTags(string $input): array
    {
        // Accept both comma-separated tags and hashtag input such as
        // "#Atatürk #Tarih #Cumhuriyet" without saving the whole line as one tag.
        $parts = preg_split('/\\s*,\\s*|\\s+(?=#)/u', trim($input), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return collect($parts)
            ->map(fn (string $tag) => trim($tag, " \\t\\n\\r\\0\\x0B#"))
            ->filter()
            // Keep individual values within the tags.name column limit.
            ->map(fn (string $tag) => mb_substr($tag, 0, 100))
            ->unique(fn (string $tag) => mb_strtolower($tag))
            ->take(20)
            ->values()
            ->all();
    }

    private function syncTagRecords(Post $post, array $names): void
    {
        $ids = [];
        foreach ($names as $name) {
            $slug = Str::slug($name);
            if ($slug === '') continue;
            $tag = Tag::firstOrCreate(['slug' => $slug], ['name' => $name]);
            $ids[] = $tag->id;
        }
        $post->tagRecords()->sync($ids);
    }

    private function setViewerReactions($paginator): void
    {
        $paginator->getCollection()->each(function (Post $post): void {
            if ($post->relationLoaded('reactions')) $post->setAttribute('viewer_reaction', $post->reactions->first()?->type);
        });
    }

    private function authorizeManage(Request $request, Post $post): void
    {
        abort_unless($request->user() && ($request->user()->is_admin || $request->user()->is($post->user)), 403);
    }
}
