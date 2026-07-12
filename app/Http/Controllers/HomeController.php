<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommunityCardResource;
use App\Http\Resources\StoryCardResource;
use App\Models\Community;
use App\Models\Story;
use App\Services\SeoGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    protected function shouldFilterNsfw()
    {
        $user = auth()->user();

        if ($user->preference_settings == null) {
            return false;
        }

        return $user->preference_settings['show_nsfw'] === false;
    }

    public function getSeoTitle(): string
    {
        $seoTitle = settings()->group('seo')->get('meta_title');

        return ! empty($seoTitle) ? $seoTitle : __('Home');
    }

    public function index(Request $request): mixed
    {
        $seo = new SeoGeneratorService();

        $stories = auth()->check()
            ? Story::whereNotIn('user_id', auth()->user()->getBlockingIds() ?? [0])->with([
                'user',
                'user.primaryBadge',
                'favorites',
                'community',
                'participants',
                'reposts',
            ])
                ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                ->approved()
                ->when($this->shouldFilterNsfw(), function ($query) {
                    $query->where('is_nsfw', false);
                })
                ->orderBy('approved_at', 'desc')
                ->simplePaginate(10)
            : Story::with([
                'user',
                'user.primaryBadge',
                'favorites',
                'community',
                'participants',
                'reposts',
            ])
                ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                ->approved()
                ->orderBy('approved_at', 'desc')
                ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('Home', [
            'stories' => StoryCardResource::collection($stories),
        ])->title($this->getSeoTitle())
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle($this->getSeoTitle())
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function active(Request $request): mixed
    {
        $seo = new SeoGeneratorService();

        $stories = auth()->check()
            ? Story::whereNotIn('user_id', auth()->user()->getBlockingIds() ?? [0])->with([
                'user',
                'user.primaryBadge',
                'favorites',
                'community',
                'participants',
                'reposts',
            ])
                ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                ->approved()
                ->when($this->shouldFilterNsfw(), function ($query) {
                    $query->where('is_nsfw', false);
                })
                ->orderByRaw('(2 * all_comments_count + likers_count) / POW((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(approved_at)), 1.5) DESC')
                ->simplePaginate(10)
            : Story::with([
                'user',
                'user.primaryBadge',
                'favorites',
                'community',
                'participants',
                'reposts',
            ])
                ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                ->approved()
                ->orderByRaw('(2 * all_comments_count + likers_count) / POW((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(approved_at)), 1.5) DESC')
                ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('Home', [
            'stories' => StoryCardResource::collection($stories),
        ])->title($this->getSeoTitle())
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle($this->getSeoTitle())
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function topToday(Request $request): mixed
    {
        $seo = new SeoGeneratorService();

        $stories = auth()->check()
            ? Story::whereNotIn('user_id', auth()->user()->getBlockingIds() ?? [0])->with([
                'user',
                'user.primaryBadge',
                'favorites',
                'community',
                'participants',
                'reposts',
            ])->whereHas('likers', function ($query) {
                $query->where('likes.likeable_type', 'App\Models\Story')->whereDate('likes.created_at', Carbon::today());
            })
                ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                ->approved()
                ->when($this->shouldFilterNsfw(), function ($query) {
                    $query->where('is_nsfw', false);
                })
                ->orderBy('likers_count', 'desc')
                ->simplePaginate(10)
            : Story::with([
                'user',
                'user.primaryBadge',
                'favorites',
                'community',
                'participants',
                'reposts',
            ])->whereHas('likers', function ($query) {
                $query->where('likes.likeable_type', 'App\Models\Story')->whereDate('likes.created_at', Carbon::today());
            })
                ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                ->approved()
                ->orderBy('likers_count', 'desc')
                ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('Home', [
            'stories' => StoryCardResource::collection($stories),
        ])->title($this->getSeoTitle())
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle($this->getSeoTitle())
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function topWeek(Request $request): mixed
    {
        $seo = new SeoGeneratorService();

        $stories = auth()->check()
            ? Story::whereNotIn('user_id', auth()->user()->getBlockingIds() ?? [0])->with([
                'user',
                'user.primaryBadge',
                'favorites',
                'community',
                'participants',
                'reposts',
            ])
                ->whereHas('likers', function ($query) {
                    $query->where('likes.likeable_type', 'App\Models\Story')
                        ->whereBetween('likes.created_at', [Carbon::now()->subDays(7), Carbon::now()]);
                })
                ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                ->approved()
                ->when($this->shouldFilterNsfw(), function ($query) {
                    $query->where('is_nsfw', false);
                })
                ->orderBy('likers_count', 'desc')
                ->simplePaginate(10)
            : Story::with([
                'user',
                'user.primaryBadge',
                'favorites',
                'community',
                'participants',
                'reposts',
            ])->whereHas('likers', function ($query) {
                $query->where('likes.likeable_type', 'App\Models\Story')
                    ->whereBetween('likes.created_at', [Carbon::now()->subDays(7), Carbon::now()]);
            })
                ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                ->approved()
                ->orderBy('likers_count', 'desc')
                ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('Home', [
            'stories' => StoryCardResource::collection($stories),
        ])->title($this->getSeoTitle())
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle($this->getSeoTitle())
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function topMonth(Request $request): mixed
    {
        $seo = new SeoGeneratorService();

        $stories = auth()->check()
            ? Story::whereNotIn('user_id', auth()->user()->getBlockingIds() ?? [0])->with([
                'user',
                'user.primaryBadge',
                'favorites',
                'community',
                'participants',
                'reposts',
            ])
                ->whereHas('likers', function ($query) {
                    $query->where('likes.likeable_type', 'App\Models\Story')
                        ->whereBetween('likes.created_at', [Carbon::now()->subDays(30), Carbon::now()]);
                })
                ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                ->approved()
                ->when($this->shouldFilterNsfw(), function ($query) {
                    $query->where('is_nsfw', false);
                })
                ->orderBy('likers_count', 'desc')
                ->simplePaginate(10)
            : Story::with([
                'user',
                'user.primaryBadge',
                'favorites',
                'community',
                'participants',
                'reposts',
            ])
                ->whereHas('likers', function ($query) {
                    $query->where('likes.likeable_type', 'App\Models\Story')
                        ->whereBetween('likes.created_at', [Carbon::now()->subDays(30), Carbon::now()]);
                })
                ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                ->approved()
                ->orderBy('likers_count', 'desc')
                ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('Home', [
            'stories' => StoryCardResource::collection($stories),
        ])->title($this->getSeoTitle())
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle($this->getSeoTitle())
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function topYear(Request $request): mixed
    {
        $seo = new SeoGeneratorService();

        $stories = auth()->check()
            ? Story::whereNotIn('user_id', auth()->user()->getBlockingIds() ?? [0])->with([
                'user',
                'user.primaryBadge',
                'favorites',
                'community',
                'participants',
                'reposts',
            ])
                ->whereHas('likers', function ($query) {
                    $query->where('likes.likeable_type', 'App\Models\Story')
                        ->whereBetween('likes.created_at', [Carbon::now()->subDays(365), Carbon::now()]);
                })
                ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                ->approved()
                ->when($this->shouldFilterNsfw(), function ($query) {
                    $query->where('is_nsfw', false);
                })
                ->orderBy('likers_count', 'desc')
                ->simplePaginate(10)
            : Story::with([
                'user',
                'user.primaryBadge',
                'favorites',
                'community',
                'participants',
                'reposts',
            ])
                ->whereHas('likers', function ($query) {
                    $query->where('likes.likeable_type', 'App\Models\Story')
                        ->whereBetween('likes.created_at', [Carbon::now()->subDays(365), Carbon::now()]);
                })
                ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                ->approved()
                ->orderBy('likers_count', 'desc')
                ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('Home', [
            'stories' => StoryCardResource::collection($stories),
        ])->title($this->getSeoTitle())
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle($this->getSeoTitle())
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function topAllTime(Request $request): mixed
    {
        $seo = new SeoGeneratorService();

        $stories = auth()->check()
            ? Story::whereNotIn('user_id', auth()->user()->getBlockingIds() ?? [0])->with([
                'user',
                'user.primaryBadge',
                'favorites',
                'community',
                'participants',
                'reposts',
            ])
                ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                ->approved()
                ->when($this->shouldFilterNsfw(), function ($query) {
                    $query->where('is_nsfw', false);
                })
                ->having('likers_count', '>', 0)
                ->orderBy('likers_count', 'desc')
                ->simplePaginate(10)
            : Story::with([
                'user',
                'user.primaryBadge',
                'favorites',
                'community',
                'participants',
                'reposts',
            ])
                ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                ->approved()
                ->having('likers_count', '>', 0)
                ->orderBy('likers_count', 'desc')
                ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('Home', [
            'stories' => StoryCardResource::collection($stories),
        ])->title($this->getSeoTitle())
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle($this->getSeoTitle())
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function featured(Request $request)
    {
        $seo = new SeoGeneratorService();

        $stories = Story::with([
            'user',
            'user.primaryBadge',
            'favorites',
            'community',
            'participants',
            'reposts',
        ])
            ->approved()
            ->featured()
            ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
            ->latest()
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('Featured', [
            'stories' => StoryCardResource::collection($stories),
        ])
            ->title(__('Featured stories'))
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(__('Featured stories'))
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function myFeed(Request $request)
    {
        $seo = new SeoGeneratorService();

        $followingCommunityIds = auth()->user()->followings()->where('followable_type', 'App\Models\Community')->pluck('followable_id')->toArray();
        $followingUserIds = auth()->user()->followings()->where('followable_type', 'App\Models\User')->pluck('followable_id')->toArray();
        $totalRecords = Story::whereIn('user_id', $followingUserIds)->orWhereIn('community_id', $followingCommunityIds)->published()->count();

        $stories = Story::with([
            'user',
            'user.primaryBadge',
            'favorites',
            'community',
            'participants',
            'reposts',
        ])
            ->whereIn('user_id', $followingUserIds)
            ->orWhereIn('community_id', $followingCommunityIds)
            ->whereNotIn('user_id', auth()->user()->getBlockingIds() ?? [0])
            ->published()
            ->when($this->shouldFilterNsfw(), function ($query) {
                $query->where('is_nsfw', false);
            })
            ->when($totalRecords, function ($query) {
                return $query->latest('published_at');
            })
            ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('MyFeed', [
            'stories' => StoryCardResource::collection($stories),
        ])
            ->title(__('My Feed'))
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(__('My Feed'))
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function communities(Request $request)
    {
        $seo = new SeoGeneratorService();

        $communities = Community::active()
            ->with(['user', 'stories', 'followers'])
            ->withCount('stories', 'followers')
            ->orderByFollowersCountDesc()
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return CommunityCardResource::collection($communities);
        }

        return Inertia::render('Feeds/Communities/Index', [
            'communities' => CommunityCardResource::collection($communities),
            'filter' => $request->input('filter'),
        ])
            ->title(__('Сommunities'))
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(__('Сommunities'))
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function myCommunities(Request $request)
    {
        $seo = new SeoGeneratorService();

        $communities = Community::active()
            ->with(['user', 'stories', 'followers'])
            ->where('user_id', auth()->user()->id)
            ->withCount('stories', 'followers')
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return CommunityCardResource::collection($communities);
        }

        return Inertia::render('Feeds/Communities/MyCommunities', [
            'communities' => CommunityCardResource::collection($communities),
        ])
            ->title(__('My Сommunities'))
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(__('My Сommunities'))
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }
}
