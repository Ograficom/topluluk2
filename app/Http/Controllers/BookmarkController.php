<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentCardResource;
use App\Http\Resources\StoryCardResource;
use App\Models\Comment;
use App\Models\Story;
use App\Services\SeoGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;

class BookmarkController extends Controller
{
    public function stories(Request $request): Response|AnonymousResourceCollection
    {
        $seo = new SeoGeneratorService();

        $stories = auth()->user()->getFavoriteItems(Story::class)
            ->with(['user', 'user.primaryBadge', 'favorites', 'community', 'participants', 'reposts'])
            ->published()
            ->withCount(['allComments', 'favoriters', 'likers', 'reposts'])
            ->latest()
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('Bookmark/Stories', [
            'stories' => StoryCardResource::collection($stories),
        ])->title(__('Bookmarks'))
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(__('Bookmarks'))
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function comments(Request $request): Response|AnonymousResourceCollection
    {
        $seo = new SeoGeneratorService();

        $comments = auth()->user()->getFavoriteItems(Comment::class)
            ->whereNull('deleted_at')
            ->with(['user', 'favorites', 'story'])
            ->withCount(['favoriters', 'likers'])
            ->latest('id')
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return CommentCardResource::collection($comments);
        }

        return Inertia::render('Bookmark/Comments', [
            'comments' => CommentCardResource::collection($comments),
        ])
            ->title(__('Bookmarks'))
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(__('Bookmarks'))
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }
}
