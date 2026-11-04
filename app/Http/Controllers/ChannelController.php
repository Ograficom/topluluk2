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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChannelController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();

        $subscriptionsTableExists = Schema::hasTable('channel_subscriptions');

        $channelsQuery = Channel::query()
            ->active()
            ->withCount([
                'posts' => fn (Builder $query) => $query->published(),
            ]);

        if ($subscriptionsTableExists) {
            $channelsQuery
                ->withCount('subscribers')
                ->when($viewer, fn (Builder $query) => $query->withExists([
                    'subscribers as is_subscribed' => fn (Builder $subscribers) => $subscribers->where('users.id', $viewer->id),
                ]));
        }

        $channels = $channelsQuery
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('channels.index', compact('channels'));
    }

    public function create(Request $request): View
    {
        $this->ensureAdmin($request);

        return view('channels.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('channels', 'slug')],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $slug = filled($validated['slug'] ?? null)
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);
        $slug = $slug !== '' ? $slug : 'channel';
        $baseSlug = Str::limit($slug, 240, '');
        $slug = $baseSlug;
        $suffix = 1;

        while (Channel::query()->where('slug', $slug)->exists()) {
            $slug = Str::limit($baseSlug, 240 - strlen((string) $suffix), '').'-'.$suffix++;
        }

        $channel = Channel::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('channels.show', $channel)->with('status', 'Kategori oluşturuldu.');
    }

    public function show(Request $request, Channel $channel): View
    {
        abort_unless($channel->is_active, 404);
        $viewer = $request->user();
        $subscriptionsTableExists = Schema::hasTable('channel_subscriptions');
        $channel->setAttribute('subscribers_count', $subscriptionsTableExists ? $channel->subscribers()->count() : 0);
        $isSubscribed = $viewer && $subscriptionsTableExists
            ? $channel->subscribers()->whereKey($viewer->id)->exists()
            : false;

        $posts = $channel->posts()
            ->published()
            ->with('user')
            ->withCount('comments')
            ->withCount([
                'reactions as likes_count' => fn (Builder $query) => $query->where('type', PostReaction::LIKE),
                'reactions as dislikes_count' => fn (Builder $query) => $query->where('type', PostReaction::DISLIKE),
            ])
            ->when(! $viewer?->show_adult_content, fn (Builder $query) => $query->where('is_adult', false))
            ->when($viewer, function (Builder $query) use ($viewer): void {
                $query->with(['reactions' => fn (Relation $reactions) => $reactions->where('user_id', $viewer->id)]);
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(10);
        $posts->getCollection()->each(function (Post $post): void {
            if ($post->relationLoaded('reactions')) {
                $post->setAttribute('viewer_reaction', $post->reactions->first()?->type);
            }
        });

        $comments = Comment::query()
            ->whereNull('parent_id')
            ->whereHas('post', fn (Builder $query) => $query->where('channel_id', $channel->id)->published())
            ->with(['user', 'post', 'replies.user'])
            ->withCount(['reactions', 'replies'])
            ->when($viewer, function (Builder $query) use ($viewer): void {
                $query->withExists([
                    'reactions as viewer_reacted' => fn (Builder $reactions) => $reactions->where('user_id', $viewer->id),
                ])->with([
                    'replies' => fn ($replies) => $replies
                        ->withCount('reactions')
                        ->withExists([
                            'reactions as viewer_reacted' => fn (Builder $reactions) => $reactions->where('user_id', $viewer->id),
                        ]),
                ]);
            })
            ->latest()
            ->paginate(15, ['*'], 'comments')
            ->withQueryString();

        return view('channels.show', compact('channel', 'posts', 'comments', 'isSubscribed'));
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->is_admin, 403);
    }
}
