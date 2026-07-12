<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentCardResource;
use App\Http\Resources\StoryCardResource;
use App\Http\Resources\UserFollowingsResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserSimpleResource;
use App\Models\Comment;
use App\Models\Story;
use App\Models\User;
use App\Services\SeoGeneratorService;
use App\Services\UploadUserAvatarService;
use App\Services\UploadUserCoverImageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function show(User $user, Request $request)
    {
        $seo = new SeoGeneratorService();

        // Retrieve the user with trashed records
        $user = User::withTrashed()->find($user->id);

        // Check if the user exists
        if (! $user) {
            abort(404);
        }

        if ($user->isSoftDeleted()) {
            return Inertia::render('User/Deleted', [
                'user' => UserProfileResource::make($user),
            ])->title(! empty($user->name) ? $user->name : $user->username)
                ->description($seo->getMetaDescription())
                ->tag($seo->getMetaCsrfToken())
                ->tag($seo->getMetaRobotsIndexble())
                ->tag($seo->getCurrentCanonicalUrl(url()->current()))
                ->ogTitle(! empty($user->name) ? $user->name : $user->username)
                ->ogDescription($seo->getOGDescription())
                ->ogImage($seo->getMetaImage())
                ->tag($seo->getOGSiteName())
                ->tag($seo->getOGType())
                ->tag($seo->getOGLocale())
                ->tag($seo->getOGUrl())
                ->twitterLargeCard();
        }

        if (auth()->check() ? auth()->user()->isBlockedBy($user) : null) {
            return Inertia::render('User/Blocked', [
                'user' => UserProfileResource::make($user),
            ])->title(! empty($user->name) ? $user->name : $user->username)
                ->description($seo->getMetaDescription())
                ->tag($seo->getMetaCsrfToken())
                ->tag($seo->getMetaRobotsIndexble())
                ->tag($seo->getCurrentCanonicalUrl(url()->current()))
                ->ogTitle(! empty($user->name) ? $user->name : $user->username)
                ->ogDescription($seo->getOGDescription())
                ->ogImage($seo->getMetaImage())
                ->tag($seo->getOGSiteName())
                ->tag($seo->getOGType())
                ->tag($seo->getOGLocale())
                ->tag($seo->getOGUrl())
                ->twitterLargeCard();
        }

        if (auth()->check() ? auth()->user()->isBlocking($user) : null) {
            return Inertia::render('User/Blocked', [
                'user' => UserProfileResource::make($user),
            ])->title(! empty($user->name) ? $user->name : $user->username)
                ->description($seo->getMetaDescription())
                ->tag($seo->getMetaCsrfToken())
                ->tag($seo->getMetaRobotsIndexble())
                ->tag($seo->getCurrentCanonicalUrl(url()->current()))
                ->ogTitle(! empty($user->name) ? $user->name : $user->username)
                ->ogDescription($seo->getOGDescription())
                ->ogImage($seo->getMetaImage())
                ->tag($seo->getOGSiteName())
                ->tag($seo->getOGType())
                ->tag($seo->getOGLocale())
                ->tag($seo->getOGUrl())
                ->twitterLargeCard();
        }

        $user->load(['profile', 'primaryBadge'])->loadCount(['followables',
            'followings' => function (Builder $query) {
                $query->where('followable_type', User::class);
            },
            'stories' => function (Builder $query) {
                $query->whereNull('deleted_at');
            },
        ]);

        $stories = Story::where('user_id', $user->id)->with(['user', 'favorites', 'community', 'participants'])
            ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
            ->published()
            ->latest('published_at')
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('User/Show', [
            'user' => UserProfileResource::make($user),
            'stories' => StoryCardResource::collection($stories),
        ])
            ->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function drafts(Request $request)
    {
        $seo = new SeoGeneratorService();

        $stories = Story::where('user_id', auth()->id())->with(['user', 'favorites', 'community'])
            ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
            ->notPublished()
            ->latest()
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('User/Draft', [
            'stories' => StoryCardResource::collection($stories),
        ])
            ->title(__('Drafts'))
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(__('Drafts'))
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function uploadAvatar(Request $request): void
    {
        $request->validate(['image' => 'required|mimes:jpg,jpeg,png|max:2000']);
        $user = (new UploadUserAvatarService())->update(auth()->user(), $request);
        $user->save();
    }

    public function uploadCoverImage(Request $request): void
    {
        $request->validate(['image' => 'required|mimes:jpg,jpeg,png|max:2000']);
        $user = (new UploadUserCoverImageService())->update(auth()->user(), $request);
        $user->save();
    }

    public function pinnedStories(User $user, Request $request)
    {
        $seo = new SeoGeneratorService();

        $user->load(['profile', 'primaryBadge'])->loadCount(['followables',
            'followings' => function (Builder $query) {
                $query->where('followable_type', User::class);
            },
            'stories' => function (Builder $query) {
                $query->whereNull('deleted_at');
            },
        ]);

        $stories = Story::where('user_id', $user->id)->with(['user', 'favorites', 'community', 'participants'])
            ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
            ->pinned()
            ->published()
            ->latest('pinned_at')
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('User/Pinned', [
            'user' => UserProfileResource::make($user),
            'stories' => StoryCardResource::collection($stories),
        ])
            ->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function comments(User $user, Request $request)
    {
        $seo = new SeoGeneratorService();

        $user->load(['profile', 'primaryBadge'])->loadCount(['followables',
            'followings' => function (Builder $query) {
                $query->where('followable_type', User::class);
            },
            'stories' => function (Builder $query) {
                $query->whereNull('deleted_at');
            },
        ]);

        $comments = Comment::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->with(['user', 'story'])
            ->withCount(['favoriters', 'likers'])
            ->latest('id')
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return CommentCardResource::collection($comments);
        }

        return Inertia::render('User/Comments', [
            'user' => UserProfileResource::make($user),
            'comments' => CommentCardResource::collection($comments),
        ])
            ->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function followings(Request $request, User $user)
    {
        $seo = new SeoGeneratorService();

        $user->load(['profile', 'primaryBadge'])->loadCount(['followables',
            'followings' => function (Builder $query) {
                $query->where('followable_type', User::class);
            },
            'stories' => function (Builder $query) {
                $query->whereNull('deleted_at');
            },
        ]);

        $followings = $user->followings()->where('followable_type', User::class)->with('followable')->simplePaginate(10);

        if ($request->wantsJson()) {
            return UserFollowingsResource::collection($followings);
        }

        return Inertia::render('User/Followings', [
            'user' => UserProfileResource::make($user),
            'followings' => UserFollowingsResource::collection($followings),
        ])
            ->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function followers(Request $request, User $user)
    {
        $seo = new SeoGeneratorService();

        $user->load(['profile', 'primaryBadge'])->loadCount(['followables',
            'followings' => function (Builder $query) {
                $query->where('followable_type', User::class);
            },
            'stories' => function (Builder $query) {
                $query->whereNull('deleted_at');
            },
        ]);

        $followers = $user->followers()->simplePaginate(10);

        if ($request->wantsJson()) {
            return UserSimpleResource::collection($followers);
        }

        return Inertia::render('User/Followers', [
            'user' => UserProfileResource::make($user),
            'followers' => UserSimpleResource::collection($followers),
        ])
            ->title(! empty($user->name) ? $user->name : $user->username)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(! empty($user->name) ? $user->name : $user->username)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }
}
