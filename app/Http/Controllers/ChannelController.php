<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Post;
use App\Models\PostReaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChannelController extends Controller
{
    public function index(): View
    {
        $channels = Channel::query()
            ->active()
            ->withCount(['posts' => fn (Builder $query) => $query->published()])
            ->orderBy('name')
            ->paginate(12);

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

        return view('channels.show', compact('channel', 'posts'));
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->is_admin, 403);
    }
}
