<?php

namespace App\Http\Controllers;

use App\Http\Resources\Explore\CommunityExploreResource;
use App\Http\Resources\Explore\UserExploreResource;
use App\Http\Resources\StoryCardResource;
use App\Models\Community;
use App\Models\Story;
use App\Models\User;
use App\Services\SeoGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExploreController extends Controller
{
    protected function shouldFilterNsfw()
    {
        $user = auth()->user();

        if ($user->preference_settings == null) {
            return false;
        }

        return $user->preference_settings['show_nsfw'] === false;
    }

    public function explore(Request $request)
    {
        $seo = new SeoGeneratorService();

        $users = User::with(['followers', 'primaryBadge'])
            ->withoutTrashed()
            ->withCount('followers')
            ->having('followers_count', '>', 0)
            ->orderByFollowersCountDesc()
            ->take(3)
            ->get();

        $communities = Community::active()
            ->with(['followers'])
            ->withCount('followers')
            ->having('followers_count', '>', 0)
            ->orderByFollowersCountDesc()
            ->take(3)
            ->get();

        $recomendations = Story::with(['user',
            'user.primaryBadge',
            'favorites',
            'community',
            'participants',
            'reposts',])
            ->withoutTrashed()
            ->published()
            ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
            ->inRandomOrder()
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($recomendations);
        }

        return Inertia::render('Explore/Index', [
            'users' => UserExploreResource::collection($users),
            'communities' => CommunityExploreResource::collection($communities),
            'recomendations' => StoryCardResource::collection($recomendations),
        ])->title(__('Explore'))
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(__('Explore'))
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function exploreTopUsers(Request $request)
    {
        $seo = new SeoGeneratorService();

        $users = User::with(['followers', 'primaryBadge'])
            ->withoutTrashed()
            ->withCount('followers')
            ->having('followers_count', '>=', 1)
            ->orderByFollowersCountDesc()
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return UserExploreResource::collection($users);
        }

        return Inertia::render('Explore/TopUsers', [
            'users' => UserExploreResource::collection($users),
        ])->title(__('Top users'))
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(__('Top users'))
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function exploreTopCommunities(Request $request)
    {
        $seo = new SeoGeneratorService();

        $communities = Community::active()
            ->with(['followers'])
            ->withCount('followers')
            ->having('followers_count', '>=', 1)
            ->orderByFollowersCountDesc()
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return CommunityExploreResource::collection($communities);
        }

        return Inertia::render('Explore/TopCommunities', [
            'communities' => CommunityExploreResource::collection($communities),
        ])->title(__('Top communities'))
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(__('Top communities'))
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function search(Request $request)
    {
        $seo = new SeoGeneratorService();

        if ($request->has('q')) {
            $query = $request->q;

            if (mb_strlen($query, 'utf8') > 2) {
                $users = User::where('name', 'like', '%' . $query . '%')->orWhere('username', 'like', '%' . $query . '%')
                    ->withoutTrashed()
                    ->with(['followers'])
                    ->withCount('followers')
                    ->orderByFollowersCountDesc()
                    ->take(3)
                    ->get();

                $communities = Community::active()
                    ->where('name', 'like', '%' . $query . '%')
                    ->with(['followers'])
                    ->withCount('followers')
                    ->orderByFollowersCountDesc()
                    ->take(3)
                    ->get();

                $stories = auth()->check()
                    ? Story::with(['user',
                        'user.primaryBadge',
                        'favorites',
                        'community',
                        'participants',
                        'reposts',])
                        ->where('title', 'like', '%' . $query . '%')
                        ->withoutTrashed()
                        ->published()
                        ->when($this->shouldFilterNsfw(), function ($query) {
                            $query->where('is_nsfw', false);
                        })
                        ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                        ->latest()
                        ->simplePaginate(10)
                        ->appends($query)
                    : Story::with(['user',
                        'user.primaryBadge',
                        'favorites',
                        'community',
                        'participants',
                        'reposts',])
                        ->where('title', 'like', '%' . $query . '%')
                        ->withoutTrashed()
                        ->published()
                        ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
                        ->latest()
                        ->simplePaginate(10)
                        ->appends($query);
            }

            if ($request->wantsJson()) {
                return StoryCardResource::collection($stories);
            }

            return Inertia::render('Explore/Search', [
                'query' => (string) $query,
                'users' => UserExploreResource::collection($users),
                'communities' => CommunityExploreResource::collection($communities),
                'stories' => StoryCardResource::collection($stories),
            ])->title(__('Search') . ' / ' . $query)
                ->description($seo->getMetaDescription())
                ->tag($seo->getMetaCsrfToken())
                ->tag($seo->getMetaRobotsNoIndexble())
                ->tag($seo->getCurrentCanonicalUrl(url()->current()))
                ->ogTitle(__('Search') . ' / ' . $query)
                ->ogDescription($seo->getOGDescription())
                ->ogImage($seo->getMetaImage())
                ->tag($seo->getOGSiteName())
                ->tag($seo->getOGType())
                ->tag($seo->getOGLocale())
                ->tag($seo->getOGUrl())
                ->twitterLargeCard();
        }
    }

    public function units(Request $request)
    {
        if ($request->has('q')) {
            $query = $request->q;

            if (mb_strlen($query, 'utf8') > 2) {
                $users = User::where('name', 'like', '%' . $request->q . '%')->orWhere('username', 'like', '%' . $request->q . '%')
                    ->withoutTrashed()
                    ->with(['followers'])
                    ->withCount('followers')
                    ->orderByFollowersCountDesc()
                    ->take(3)
                    ->get();

                $communities = Community::active()
                    ->where('name', 'like', '%' . $request->q . '%')
                    ->with(['followers'])
                    ->withCount('followers')
                    ->orderByFollowersCountDesc()
                    ->take(3)
                    ->get();
            }
        }

        return [
            'users' => UserExploreResource::collection($users),
            'communities' => CommunityExploreResource::collection($communities),
        ];
    }

    public function users(Request $request)
    {
        $seo = new SeoGeneratorService();

        if ($request->has('q')) {
            $query = $request->q;

            if (mb_strlen($query, 'utf8') > 2) {
                $users = User::with(['followers', 'primaryBadge'])
                    ->where('name', 'like', '%' . $query . '%')->orWhere('username', 'like', '%' . $query . '%')
                    ->withoutTrashed()
                    ->withCount('followers')
                    ->orderByFollowersCountDesc()
                    ->simplePaginate(10);
            }

            if ($request->wantsJson()) {
                return UserExploreResource::collection($users);
            }

            return Inertia::render('Explore/Users', [
                'query' => (string) $query,
                'users' => UserExploreResource::collection($users),
            ])->title(__('Users') . ' / ' . $query)
                ->description($seo->getMetaDescription())
                ->tag($seo->getMetaCsrfToken())
                ->tag($seo->getMetaRobotsNoIndexble())
                ->tag($seo->getCurrentCanonicalUrl(url()->current()))
                ->ogTitle(__('Users') . ' / ' . $query)
                ->ogDescription($seo->getOGDescription())
                ->ogImage($seo->getMetaImage())
                ->tag($seo->getOGSiteName())
                ->tag($seo->getOGType())
                ->tag($seo->getOGLocale())
                ->tag($seo->getOGUrl())
                ->twitterLargeCard();
        }
    }

    public function communities(Request $request)
    {
        $seo = new SeoGeneratorService();

        if ($request->has('q')) {
            $query = $request->q;

            if (mb_strlen($query, 'utf8') > 2) {
                $communities = Community::active()
                    ->where('name', 'like', '%' . $query . '%')
                    ->with(['followers'])
                    ->withCount('followers')
                    ->orderByFollowersCountDesc()
                    ->simplePaginate(10);
            }

            if ($request->wantsJson()) {
                return CommunityExploreResource::collection($communities);
            }

            return Inertia::render('Explore/Communities', [
                'query' => (string) $request->q,
                'communities' => CommunityExploreResource::collection($communities),
            ])->title(__('Communities') . ' / ' . $query)
                ->description($seo->getMetaDescription())
                ->tag($seo->getMetaCsrfToken())
                ->tag($seo->getMetaRobotsNoIndexble())
                ->tag($seo->getCurrentCanonicalUrl(url()->current()))
                ->ogTitle(__('Communities') . ' / ' . $query)
                ->ogDescription($seo->getOGDescription())
                ->ogImage($seo->getMetaImage())
                ->tag($seo->getOGSiteName())
                ->tag($seo->getOGType())
                ->tag($seo->getOGLocale())
                ->tag($seo->getOGUrl())
                ->twitterLargeCard();
        }
    }
}
