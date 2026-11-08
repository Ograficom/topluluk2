<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostReaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
            ->with(['user', 'channel', 'advertisement'])
            ->with(['comments' => fn ($query) => $query->with('user')->limit(1)])
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
            ->whereHas('post', fn (Builder $postQuery) => $postQuery->published()
                ->when(! $viewer?->show_adult_content, fn (Builder $query) => $query->where('is_adult', false)))
            ->latest()
            ->paginate(10, ['*'], 'comments');
        $isBlocked = $viewer && $viewer->blockedUsers()->whereKey($user->id)->exists();
        $isFollowing = $viewer && $viewer->following()->whereKey($user->id)->exists();
        $followersCount = $user->followers()->count();
        $followingCount = $user->following()->count();
        $followerColumns = ['users.id', 'users.name', 'users.full_name', 'users.username', 'users.avatar_path', 'users.is_verified', 'users.verification_status'];

        if (Schema::hasColumn('users', 'last_seen_at')) {
            $followerColumns[] = 'users.last_seen_at';
        }

        $profileFollowersQuery = $user->followers()
            ->select($followerColumns)
            ->withCount(['posts' => fn (Builder $query) => $query->published()])
            ->latest('user_follows.created_at');

        $profileFollowers = (clone $profileFollowersQuery)->limit(12)->get();
        $allProfileFollowers = (clone $profileFollowersQuery)->limit(100)->get();

        $topPosition = User::query()
            ->withCount(['posts' => fn (Builder $query) => $query->published()])
            ->orderByDesc('posts_count')
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->search(fn (User $member): bool => $member->is($user));

        $topPosition = $topPosition === false ? null : $topPosition + 1;

        return view('profiles.show', compact(
            'user', 'posts', 'comments', 'isBlocked', 'isFollowing',
            'followersCount', 'followingCount', 'profileFollowers', 'allProfileFollowers', 'topPosition',
        ));
    }
}
