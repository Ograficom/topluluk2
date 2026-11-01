<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostReaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request, string $identifier): View
    {
        $user = User::query()->where('username', $identifier)->first();

        if (! $user && is_numeric($identifier)) {
            $user = User::query()->find($identifier);
        }

        abort_if(! $user, 404);
        $viewer = $request->user();

        if ($viewer && $viewer->isNot($user)) {
            abort_if(
                $viewer->blockedUsers()->whereKey($user->id)->exists()
                    || $viewer->blockedByUsers()->whereKey($user->id)->exists(),
                404,
            );
        }

        $posts = Post::query()
            ->published()
            ->where('user_id', $user->id)
            ->with(['user', 'channel'])
            ->withCount('comments')
            ->withCount([
                'reactions as likes_count' => fn (Builder $query) => $query->where('type', PostReaction::LIKE),
                'reactions as dislikes_count' => fn (Builder $query) => $query->where('type', PostReaction::DISLIKE),
            ])
            ->when(! $viewer?->show_adult_content, fn (Builder $query) => $query->where('is_adult', false))
            ->when($viewer, function (Builder $query) use ($viewer): void {
                $query->with(['reactions' => fn (Relation $reactions) => $reactions->where('user_id', $viewer->id)]);
            })
            ->latest('published_at')
            ->paginate(10, ['*'], 'posts');
        $posts->getCollection()->each(function (Post $post): void {
            if ($post->relationLoaded('reactions')) {
                $post->setAttribute('viewer_reaction', $post->reactions->first()?->type);
            }
        });

        $comments = $user->comments()
            ->with(['post', 'post.channel'])
            ->when(! $viewer?->show_adult_content, fn (Builder $query) => $query->whereHas('post', fn (Builder $postQuery) => $postQuery->where('is_adult', false)))
            ->latest()
            ->paginate(10, ['*'], 'comments');
        $isBlocked = $viewer && $viewer->blockedUsers()->whereKey($user->id)->exists();

        return view('profiles.show', compact('user', 'posts', 'comments', 'isBlocked'));
    }
}
