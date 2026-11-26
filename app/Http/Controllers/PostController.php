<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Channel;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\RssItem;
use App\Models\Tag;
use App\Services\PostSpamFilter;
use App\Services\UserNotificationService;
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

        $posts = Post::query()->published()->whereNull('advertisement_id')
            ->when(! $viewer?->show_adult_content, fn (Builder $query) => $query->where('is_adult', false))
            ->with(['user', 'channel', 'tagRecords', 'comments' => fn ($query) => $query->with('user')->latest()->limit(1)])
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

    public function tagSuggestions(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $prefix = Str::slug(trim($validated['q'] ?? '', " \t\n\r#"));
        if ($prefix === '') return response()->json([])->header('Cache-Control', 'no-store');

        // Slugs normalize Turkish letters and case; the prefix contains no SQL wildcards.
        $tags = Tag::query()
            ->where('slug', 'like', $prefix.'%')
            ->withCount('posts')
            ->orderByRaw('CASE WHEN slug = ? THEN 0 ELSE 1 END', [$prefix])
            ->orderByDesc('posts_count')
            ->orderBy('name')
            ->limit(12)
            ->get(['id', 'name', 'slug']);

        return response()->json($tags->map(fn (Tag $tag) => [
            'name' => $tag->name,
            'slug' => $tag->slug,
        ])->values())->header('Cache-Control', 'no-store');
    }

    public function create(Request $request): View
    {
        // Share Target only prepares a draft; never publishes on GET.
        $readShared = static function (string $key, int $limit) use ($request): string {
            $value = $request->query($key);
            return is_string($value) ? mb_substr(trim($value), 0, $limit) : '';
        };
        $sharedTitle = $readShared('share_title', 255);
        $sharedText = $readShared('share_text', 10000);
        $sharedUrl = $readShared('share_url', 2048);
        if ($sharedUrl !== '' && ! in_array(parse_url($sharedUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
            $sharedUrl = '';
        }
        $sharedContent = trim(implode("\n\n", array_filter([$sharedText, $sharedUrl], static fn ($value) => $value !== '')));
        $sharedBody = $sharedContent === '' ? '' : json_encode([
            'blocks' => [['type' => 'paragraph', 'data' => ['text' => nl2br(e($sharedContent), false)]]],
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        return view('posts.create', [
            'channels' => Channel::query()->active()->orderBy('name')->get(),
            'user' => $request->user(),
            'sharedTitle' => $sharedTitle,
            'sharedBody' => $sharedBody,
            'sharedExcerpt' => mb_substr($sharedContent, 0, 500),
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
        return $this->publishPost($request);
    }

    public function publishPost(Request $request, ?Post $quoteSource = null): RedirectResponse
    {
        $validated = $request->validate([
            'title' => [$quoteSource ? 'nullable' : 'required', 'string', 'max:255'],
            'channel_id' => ['nullable', Rule::exists('channels', 'id')->where('is_active', true)],
            'excerpt' => [$quoteSource ? 'nullable' : 'required', 'string', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'content_source' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
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
            'show_toc' => ['sometimes', 'boolean'],
            'is_panorama' => ['sometimes', 'boolean'],
        ]);

        if ($request->boolean('is_panorama') && ! $request->hasFile('cover_image') && ! $request->hasFile('media')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['cover_image' => '280° panorama için bir fotoğraf yükleyin.']);
        }

        if ($quoteSource) {
            $validated['title'] = filled($validated['title'] ?? null)
                ? $validated['title']
                : Str::limit('Yeniden paylaşım: '.$quoteSource->title, 255, '');
            if (! filled($validated['excerpt'] ?? null)) {
                $validated['excerpt'] = Str::limit(trim(strip_tags((string) $validated['body'])), 500, '') ?: $validated['title'];
                $decoded = json_decode((string) $validated['body'], true);
                if (is_array($decoded) && is_array($decoded['blocks'] ?? null)) {
                    $text = collect($decoded['blocks'])->map(fn ($block) => is_array($block) && is_string(data_get($block, 'data.text')) ? strip_tags(data_get($block, 'data.text')) : '')->filter()->implode(' ');
                    $validated['excerpt'] = Str::limit(trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')), 500, '') ?: $validated['title'];
                }
            }
        }

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
        foreach (array_values($request->file('media', [])) as $mediaFile) {
            $path = $mediaFile->store('posts/gallery', 'public');
            if (! $path) throw new \RuntimeException('A gallery image could not be stored.');
            $mediaPaths[] = $path;
        }

        $validated['body'] = $this->resolvePostImageBlocks($validated['body'], $mediaPaths, $mediaPaths);
        $tags = $this->parseTags($validated['tags'] ?? '');
        $isSpam = app(PostSpamFilter::class)->shouldFlag($validated['title'], $validated['excerpt'], $validated['body']);
        $post = Post::create([
            'user_id' => $request->user()->id,
            'quoted_post_id' => $quoteSource?->id,
            'channel_id' => $validated['channel_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'meta_description' => $validated['meta_description'] ?? null,
            'content_source' => $validated['content_source'] ?? null,
            'location' => $validated['location'] ?? null,
            'body' => $validated['body'],
            'cover_image' => $coverImage,
            'media' => $mediaPaths,
            'video_url' => $validated['video_url'] ?? null,
            'video_path' => $videoPath,
            'tags' => $tags,
            'is_published' => ! $isSpam,
            'is_spam' => $isSpam,
            'published_at' => $isSpam ? null : now(),
            'is_adult' => $request->boolean('is_adult') || (bool) $quoteSource?->is_adult,
            'show_toc' => $request->boolean('show_toc'),
            'is_panorama' => $request->boolean('is_panorama'),
        ]);
        $this->syncTagRecords($post, $tags);

        if (! $isSpam) {
            if (! $quoteSource) {
                app(\App\Services\ReputationService::class)->award($request->user(), \App\Services\ReputationService::POST_PUBLISHED, 'post:'.$post->id.':published', 'Yazı yayınladı', Post::class, $post->id);
            }
            $recipients = $request->user()->followers()->get();

            if ($post->channel_id) {
                $channel = Channel::query()->find($post->channel_id);
                if ($channel) {
                    $recipients = $recipients->merge($channel->subscribers()->get());
                }
            }

            if ($quoteSource?->user && ! $quoteSource->user->is($request->user())) {
                UserNotificationService::send(
                    $quoteSource->user, 'posts', 'Yazın yorumla yeniden paylaşıldı',
                    $request->user()->displayName().' yazını yorumlayarak yeniden paylaştı.',
                    route('posts.show', $post),
                );
                $recipients = $recipients->reject(fn ($recipient) => $recipient->is($quoteSource->user));
            }
            $recipients->unique('id')->each(fn (\App\Models\User $recipient) => UserNotificationService::send(
                $recipient,
                'posts',
                'Yeni yazı yayınlandı',
                $request->user()->displayName().' yeni bir yazı yayınladı: '.$post->title,
                route('posts.show', $post),
            ));
        }

        return $isSpam
            ? redirect()->route('posts.index')->with('status', 'Postunuz spam filtresine takıldı ve yönetici incelemesine alındı.')
            : redirect()->route('posts.show', $post)->with('status', 'Yazınız yayınlandı.');
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->authorizeManage($request, $post);
        $wasSpam = (bool) $post->is_spam;
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'channel_id' => ['nullable', Rule::exists('channels', 'id')->where('is_active', true)],
            'excerpt' => ['required', 'string', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'content_source' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
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
            'show_toc' => ['sometimes', 'boolean'],
            'is_panorama' => ['sometimes', 'boolean'],
            'remove_cover' => ['sometimes', 'boolean'],
        ]);

        $slugBase = Str::limit(Str::slug($validated['title']) ?: 'post', 240, '');
        $slug = $slugBase;
        for ($suffix = 1; Post::query()->where('slug', $slug)->where('id', '<>', $post->id)->exists(); $suffix++) {
            $slug = Str::limit($slugBase, 240 - strlen((string) $suffix), '').'-'.$suffix;
        }

        $hasPanoramaCover = $request->hasFile('cover_image') || (! $request->boolean('remove_cover') && $post->coverImageUrl())
            || $request->hasFile('media')
            || collect($post->media ?? [])->diff($validated['remove_media'] ?? [])->isNotEmpty();
        $isPanorama = array_key_exists('is_panorama', $validated)
            ? $request->boolean('is_panorama') : (bool) $post->is_panorama;
        if ($isPanorama && ! $hasPanoramaCover && $request->boolean('is_panorama')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['cover_image' => '280° panorama için bir fotoğraf seçin veya panorama ayarını kapatın.']);
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
        $removedMediaPaths = $existingMedia->filter(fn (string $path) => $removeMedia->contains($path));
        $mediaPaths = $existingMedia->reject(fn (string $path) => $removeMedia->contains($path))->values();
        $newMediaPaths = [];
        foreach (array_values($request->file('media', [])) as $mediaFile) {
            $path = $mediaFile->store('posts/gallery', 'public');
            if (! $path) throw new \RuntimeException('A gallery image could not be stored.');
            $newMediaPaths[] = $path;
            $mediaPaths->push($path);
        }
        $validated['body'] = $this->resolvePostImageBlocks($validated['body'], $mediaPaths->all(), $newMediaPaths);

        $post->fill([
            'media' => $mediaPaths->all(),
            'video_url' => $validated['video_url'] ?? null,
            'channel_id' => $validated['channel_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'meta_description' => $validated['meta_description'] ?? null,
            'content_source' => array_key_exists('content_source', $validated) ? $validated['content_source'] : $post->content_source,
            'location' => $validated['location'] ?? null,
            'body' => $validated['body'],
            'tags' => $tags,
            'is_adult' => $request->boolean('is_adult'),
            'show_toc' => array_key_exists('show_toc', $validated) ? $request->boolean('show_toc') : (bool) $post->show_toc,
            'is_panorama' => $isPanorama && $hasPanoramaCover,
            'is_spam' => ($isSpam || $wasSpam),
        ]);
        if ($isSpam || $wasSpam) $post->is_published = false;
        if ($newVideo) $post->video_path = $newVideo;
        if ($newCover) $post->cover_image = $newCover;
        elseif ($request->boolean('remove_cover')) $post->cover_image = null;
        \Illuminate\Support\Facades\DB::transaction(fn () => $post->save());
        $this->syncTagRecords($post, $tags);

        // Delete only files no longer referenced by a saved post, never before saving.
        if ($oldCover && $oldCover !== $post->cover_image) $removedMediaPaths->push($oldCover);
        foreach ($removedMediaPaths->unique() as $path) $this->deleteUnusedPostImage($path);
        if ($oldVideo && $oldVideo !== $post->video_path) Storage::disk('public')->delete($oldVideo);

        if ($isSpam || $wasSpam) {
            return redirect()->route('posts.index')->with('status', 'Post spam incelemesinde. Yayına alınması için yönetici onayı bekliyor.');
        }

        return redirect()->route('posts.show', $post)->with('status', 'Yazınız güncellendi.');
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $this->authorizeManage($request, $post);
        \Illuminate\Support\Facades\DB::transaction(fn () => $post->delete());

        return redirect()->route('posts.index')->with('status', 'Yazı silindi.');
    }

    public function editHistory(Request $request, Post $post): \Illuminate\Http\JsonResponse
    {
        abort_unless(Post::query()->published()->whereKey($post->id)->exists(), 404);
        abort_if($post->is_adult && ! $request->user()?->show_adult_content, 404);
        if ($request->user()) {
            abort_if($request->user()->blockedUsers()->whereKey($post->user_id)->exists()
                || $request->user()->blockedByUsers()->whereKey($post->user_id)->exists(), 404);
        }

        $validated = $request->validate(['page' => ['nullable', 'integer', 'min:1', 'max:100000']]);
        $history = \Illuminate\Support\Facades\DB::table('post_edit_histories')
            ->where('post_id', $post->id)->orderByDesc('id')
            ->paginate(20, ['editor_name', 'edited_at'], 'page', $validated['page'] ?? 1);

        return response()->json([
            'count' => $history->total(),
            'entries' => collect($history->items())->map(fn ($entry) => [
                'editor' => $entry->editor_name,
                'date' => \Illuminate\Support\Carbon::parse($entry->edited_at)->toIso8601String(),
            ])->values(),
            'next_page' => $history->hasMorePages() ? $history->currentPage() + 1 : null,
        ])->header('Cache-Control', 'no-store');
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

        $similarPosts = Post::query()->published()->whereNull('advertisement_id')->where('posts.id', '<>', $post->id)
            ->when($post->channel_id, fn (Builder $query) => $query->where('channel_id', $post->channel_id))
            ->when(! $post->channel_id && $post->tagRecords->isNotEmpty(), fn (Builder $query) => $query->whereHas('tagRecords', fn (Builder $tags) => $tags->whereIn('tags.id', $post->tagRecords->pluck('id'))))
            ->when($request->user(), fn (Builder $query) => $query->whereNotIn('user_id', $request->user()->blockedUsers()->select('users.id'))->whereNotIn('user_id', $request->user()->blockedByUsers()->select('users.id')))
            ->when(! $request->user()?->show_adult_content, fn (Builder $query) => $query->where('is_adult', false))
            ->with(['user', 'channel', 'tagRecords'])
            ->withCount('comments')
            ->withCount([
                'reactions as likes_count' => fn (Builder $query) => $query->where('type', PostReaction::LIKE),
                'reactions as dislikes_count' => fn (Builder $query) => $query->where('type', PostReaction::DISLIKE),
            ])
            ->when($request->user(), fn (Builder $query) => $query->with(['reactions' => fn (Relation $reactions) => $reactions->where('user_id', $request->user()->id)]))
            ->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get();

        return view('posts.show', compact('post', 'replyTo', 'rssItem', 'similarPosts'));
    }

    private function resolvePostImageBlocks(string $body, array $allowedPaths, array $newPaths): string
    {
        $data = json_decode($body, true);
        if (! is_array($data) || ! is_array($data['blocks'] ?? null)) return $body;

        $seen = [];
        $blocks = [];
        foreach ($data['blocks'] as $block) {
            if (! is_array($block) || ($block['type'] ?? '') !== 'postImage') {
                $blocks[] = $block;
                continue;
            }
            $path = $block['data']['path'] ?? null;
            $index = $block['data']['uploadIndex'] ?? null;
            if (is_int($index)) $path = $newPaths[$index] ?? null;
            if (! is_string($path) || ! in_array($path, $allowedPaths, true) || isset($seen[$path])) continue;
            $seen[$path] = true;
            $block['data'] = ['path' => $path];
            $blocks[] = $block;
        }
        $data['blocks'] = $blocks;

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function deleteUnusedPostImage(string $value): void
    {
        // External RSS/remote images and unrelated storage paths are not owned uploads.
        if (filter_var($value, FILTER_VALIDATE_URL)) return;
        $path = ltrim($value, '/');
        if (str_starts_with($path, 'storage/')) $path = substr($path, 8);
        if (! preg_match('~\Aposts/(?:gallery/)?[A-Za-z0-9_-][A-Za-z0-9_.-]*\.(?:jpe?g|png|gif|webp)\z~i', $path) || str_contains($path, '..')) return;

        $aliases = [$path, '/'.$path, 'storage/'.$path, '/storage/'.$path, asset('storage/'.$path)];
        $referenced = Post::query()->where(function (Builder $query) use ($aliases): void {
            $query->whereIn('cover_image', $aliases);
            foreach ($aliases as $alias) $query->orWhereJsonContains('media', $alias);
        })->exists();

        if (! $referenced && ! Storage::disk('public')->delete($path)) {
            report(new \RuntimeException('Removed post image could not be deleted: '.$path));
        }
    }

    private function parseTags(string $input): array
    {
        // Accept both comma-separated tags and hashtag input such as
        // "#Atatürk #Tarih #Cumhuriyet" without saving the whole line as one tag.
        $parts = preg_split('/\s*,\s*|\s+(?=#)/u', trim($input), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return collect($parts)
            ->map(fn (string $tag) => trim($tag, " \t\n\r\0\x0B#"))
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
