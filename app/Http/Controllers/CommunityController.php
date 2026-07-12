<?php

namespace App\Http\Controllers;

use App\Http\Resources\Community\CommunitySettingsResource;
use App\Http\Resources\CommunityResource;
use App\Http\Resources\StoryCardResource;
use App\Models\Community;
use App\Models\Story;
use App\Services\SeoGeneratorService;
use App\Services\UploadCommunityAvatarService;
use App\Services\UploadCommunityCoverImageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CommunityController extends Controller
{
    use AuthorizesRequests;

    public function create(Request $request)
    {
        $this->authorize('add_communities');

        $seo = new SeoGeneratorService();

        return Inertia::render('Community/Create')
            ->title(__('Create a community'))
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(__('Create a community'))
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function store(Request $request)
    {
        $this->authorize('add_communities');

        $creator = auth()->user();

        $request->validate([
            'name' => ['required', 'min:3', 'max:60', 'regex:/^[\pL0-9\s]+$/u', 'unique:communities,name'],
            'description' => ['required', 'min:10', 'max:200'],
            'rules' => ['sometimes', 'nullable', 'max:1000'],
        ]);

        $community = Community::create([
            'user_id' => $creator->id,
            'name' => $request->name,
            'description' => $request->description,
            'rules' => nl2p(strip_tags($request->rules)),
        ]);

        $creator->follow($community);

        return redirect()->route('community.show', $community);
    }

    public function show(Community $community, Request $request)
    {
        $seo = new SeoGeneratorService();

        $community->loadCount(['followables', 'stories' => function (Builder $query) {
            $query->published()->whereNull('deleted_at');
        }, 'stories as total_views_count' => function (Builder $query) {
            $query->select(DB::raw('sum(views_count)'));
        }]);

        $stories = Story::where('community_id', $community->id)
            ->with(['user', 'user.primaryBadge', 'favorites', 'community', 'participants'])
            ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
            ->approved()
            ->latest()
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('Community/Show', [
            'community' => CommunityResource::make($community),
            'stories' => StoryCardResource::collection($stories),
        ])
            ->title($community->name)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle($community->name)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function top(Community $community, Request $request)
    {
        $seo = new SeoGeneratorService();

        $community->load('user')->loadCount(['followables', 'stories' => function (Builder $query) {
            $query->published()->whereNull('deleted_at');
        }, 'stories as total_views_count' => function (Builder $query) {
            $query->select(DB::raw('sum(views_count)'));
        }]);

        $stories = Story::where('community_id', $community->id)
            ->with(['user', 'user.primaryBadge', 'favorites', 'community', 'participants', 'reposts'])
            ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
            ->approved()
            ->orderByRaw('(2 * all_comments_count + likers_count) / POW((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(approved_at)), 1.5) DESC')
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($stories);
        }

        return Inertia::render('Community/Top', [
            'community' => CommunityResource::make($community),
            'stories' => StoryCardResource::collection($stories),
        ])->title($community->name)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle($community->name)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function about(Community $community)
    {
        $seo = new SeoGeneratorService();

        $community->load('user')->loadCount(['followables', 'stories' => function (Builder $query) {
            $query->published()->whereNull('deleted_at');
        }, 'stories as total_views_count' => function (Builder $query) {
            $query->select(DB::raw('sum(views_count)'));
        }]);

        return Inertia::render('Community/About', [
            'community' => CommunityResource::make($community),
        ])->title($community->name)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle($community->name)
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

        $community = Community::find($request->community_id);

        if ($community) {
            $community = (new UploadCommunityAvatarService())->update($community, $request);
            $community->save();
        }
    }

    public function uploadCoverImage(Request $request): void
    {
        $request->validate(['image' => 'required|mimes:jpg,jpeg,png|max:2000']);

        $community = Community::find($request->community_id);

        if ($community) {
            $community = (new UploadCommunityCoverImageService())->update($community, $request);
            $community->save();
        }
    }

    public function settings(Community $community)
    {
        $seo = new SeoGeneratorService();

        return Inertia::render('Community/Settings', [
            'community' => CommunitySettingsResource::make($community),
        ])->title($community->name)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle($community->name)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function updateSettings(Community $community, Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'min:3', 'max:60', 'regex:/^[\pL0-9\s]+$/u'],
            'description' => ['required', 'min:10', 'max:200'],
            'rules' => ['sometimes', 'nullable', 'min:15', 'max:1000'],
        ]);

        $community->update([
            'name' => $request->name,
            'description' => strip_tags($request->description),
            'rules' => nl2p(strip_tags($request->rules)),
        ]);

        toast_success(__('Updated successfully'));

        return back();
    }
}
