<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostReaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
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

        $posts = Post::query()
            ->published()
            ->with(['user', 'channel'])
            ->withCount('comments')
            ->withCount([
                'reactions as likes_count' => fn (Builder $query) => $query->where('type', PostReaction::LIKE),
                'reactions as dislikes_count' => fn (Builder $query) => $query->where('type', PostReaction::DISLIKE),
            ])
            ->when($viewer, function (Builder $query) use ($viewer): void {
                $query->with(['reactions' => fn (Relation $reactions) => $reactions->where('user_id', $viewer->id)]);
            })
            ->when($viewer, function (Builder $query) use ($viewer): void {
                $query->whereNotIn('user_id', $viewer->blockedUsers()->select('users.id'))
                    ->whereNotIn('user_id', $viewer->blockedByUsers()->select('users.id'));
            })
            ->when(! $viewer?->show_adult_content, fn (Builder $query) => $query->where('is_adult', false))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->when($period, function (Builder $query) use ($period): void {
                $query->where('published_at', '>=', match ($period) {
                    'day' => now()->subDay(),
                    'week' => now()->subWeek(),
                    'month' => now()->subMonth(),
                });
            })
            ->when($sort === 'popular', fn (Builder $query) => $query->orderByDesc('likes_count'))
            ->when($sort === 'views', fn (Builder $query) => $query->orderByDesc('views'))
            ->when($sort === 'latest', fn (Builder $query) => $query->orderByDesc('published_at')->orderByDesc('id'))
            ->paginate(10)
            ->withQueryString();

        $posts->getCollection()->each(function (Post $post): void {
            if ($post->relationLoaded('reactions')) {
                $post->setAttribute('viewer_reaction', $post->reactions->first()?->type);
            }
        });

        return view('posts.index', compact('posts', 'search', 'sort', 'period'));
    }

    public function create(Request $request): View
    {
        $channels = Channel::query()->active()->orderBy('name')->get();

        return view('posts.create', [
            'channels' => $channels,
            'user' => $request->user(),
        ]);
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
            'body' => ['required', 'string', 'max:100000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'tags' => ['nullable', 'string', 'max:500'],
            'is_adult' => ['sometimes', 'boolean'],
        ]);

        $slug = Str::slug($validated['title']);
        $slug = $slug !== '' ? $slug : 'post';
        $baseSlug = Str::limit($slug, 240, '');
        $slug = $baseSlug;
        $suffix = 1;

        while (Post::query()->where('slug', $slug)->exists()) {
            $slug = Str::limit($baseSlug, 240 - strlen((string) $suffix), '').'-'.$suffix++;
        }

        $coverImage = $request->file('cover_image')?->store('posts', 'public');

        if ($request->hasFile('cover_image') && ! $coverImage) {
            throw new \RuntimeException('The post cover image could not be stored.');
        }

        $mediaPaths = [];
        foreach ($request->file('media', []) as $mediaFile) {
            $mediaPath = $mediaFile->store('posts/gallery', 'public');
            if (! $mediaPath) {
                throw new \RuntimeException('A gallery image could not be stored.');
            }
            $mediaPaths[] = $mediaPath;
        }

        $tags = collect(explode(',', $validated['tags'] ?? ''))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique(fn (string $tag): string => mb_strtolower($tag))
            ->take(20)
            ->values()
            ->all();

        $post = Post::create([
            'user_id' => $request->user()->id,
            'channel_id' => $validated['channel_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'cover_image' => $coverImage,
            'media' => $mediaPaths,
            'tags' => $tags,
            'is_published' => true,
            'published_at' => now(),
            'is_adult' => $request->boolean('is_adult'),
        ]);

        return redirect()->route('posts.show', $post)->with('status', 'Yazınız yayınlandı.');
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->authorizeManage($request, $post);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'channel_id' => ['nullable', Rule::exists('channels', 'id')->where('is_active', true)],
            'excerpt' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:100000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'remove_media' => ['nullable', 'array', 'max:10'],
            'remove_media.*' => ['string', 'max:500'],
            'tags' => ['nullable', 'string', 'max:500'],
            'is_adult' => ['sometimes', 'boolean'],
            'remove_cover' => ['sometimes', 'boolean'],
        ]);

        $baseSlug = Str::limit(Str::slug($validated['title']) ?: 'post', 240, '');
        $slug = $baseSlug;
        $suffix = 1;
        while (Post::query()->where('slug', $slug)->where('id', '<>', $post->id)->exists()) {
            $slug = Str::limit($baseSlug, 240 - strlen((string) $suffix), '').'-'.$suffix++;
        }

        $newCover = $request->file('cover_image')?->store('posts', 'public');
        if ($request->hasFile('cover_image') && ! $newCover) {
            throw new \RuntimeException('The post cover image could not be stored.');
        }

        $tags = collect(explode(',', $validated['tags'] ?? ''))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique(fn (string $tag): string => mb_strtolower($tag))
            ->take(20)
            ->values()
            ->all();

        $oldCover = $post->cover_image;
        $existingMedia = collect($post->media ?? []);
        $removeMedia = collect($validated['remove_media'] ?? []);
        $removedMedia = $existingMedia->filter(fn (string $path): bool => $removeMedia->contains($path));
        foreach ($removedMedia as $mediaPath) {
            Storage::disk('public')->delete($mediaPath);
        }
        $mediaPaths = $existingMedia->reject(fn (string $path): bool => $removeMedia->contains($path))->values();

        foreach ($request->file('media', []) as $mediaFile) {
            $mediaPath = $mediaFile->store('posts/gallery', 'public');
            if (! $mediaPath) {
                throw new \RuntimeException('A gallery image could not be stored.');
            }
            $mediaPaths->push($mediaPath);
        }

        $post->fill([
            'media' => $mediaPaths->all(),
            'channel_id' => $validated['channel_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'tags' => $tags,
            'is_adult' => $request->boolean('is_adult'),
        ]);
        if ($newCover) {
            $post->cover_image = $newCover;
        } elseif ($request->boolean('remove_cover')) {
            $post->cover_image = null;
        }
        $post->save();

        if ($oldCover && $oldCover !== $post->cover_image && ! filter_var($oldCover, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($oldCover);
        }

        return redirect()->route('posts.show', $post)->with('status', 'Yazınız güncellendi.');
    }

    public function show(Request $request, Post $post): View
    {
        abort_unless(
            $post->is_published && (! $post->published_at || $post->published_at->isPast()),
            404,
        );

        if ($post->is_adult && ! $request->user()?->show_adult_content) {
            return view('posts.adult-warning');
        }

        $post->increment('views');
        $post->load(['user', 'channel']);
        $replyTo = null;

        if ($request->filled('reply_to')) {
            $replyTo = Comment::query()
                ->where('post_id', $post->id)
                ->whereNull('parent_id')
                ->find($request->query('reply_to'));
            abort_unless($replyTo, 404);
        }

        $commentsQuery = $post->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->withCount('reactions')
            ->withCount('replies')
            ->with([
                'replies' => fn ($query) => $query->withCount('reactions'),
            ]);

        if ($request->user()) {
            $commentsQuery
                ->withExists([
                    'reactions as viewer_reacted' => fn (Builder $query) => $query->where('user_id', $request->user()->id),
                ])
                ->with([
                    'replies' => fn ($query) => $query
                        ->withCount('reactions')
                        ->withExists([
                            'reactions as viewer_reacted' => fn (Builder $reactionQuery) => $reactionQuery->where('user_id', $request->user()->id),
                        ]),
                ]);
        }

        $post->setRelation('comments', $commentsQuery->get());
        $post->loadCount([
            'comments',
            'reactions as likes_count' => fn (Builder $query) => $query->where('type', PostReaction::LIKE),
            'reactions as dislikes_count' => fn (Builder $query) => $query->where('type', PostReaction::DISLIKE),
        ]);
        if ($request->user()) {
            $post->load(['reactions' => fn (Relation $query) => $query->where('user_id', $request->user()->id)]);
        }

        return view('posts.show', compact('post', 'replyTo'));
    }

    private function authorizeManage(Request $request, Post $post): void
    {
        abort_unless($request->user() && ($request->user()->is_admin || $request->user()->is($post->user)), 403);
    }
}
