<?php

namespace App\Http\Controllers;

use App\Enums\PostType;
use App\Events\Story\CreatedPublishedStory;
use App\Events\Story\DeletedPublishedStory;
use App\Http\Resources\CommunityStoryCardResource;
use App\Http\Resources\StoryCardResource;
use App\Http\Resources\StoryEditorResource;
use App\Http\Resources\StoryOriginalCardResource;
use App\Http\Resources\StoryResource;
use App\Models\Community;
use App\Models\Story;
use App\Services\SeoGeneratorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class StoryController extends Controller
{
    use AuthorizesRequests;

    public function create(Request $request)
    {
        $this->authorize('add_stories');

        $draftLimits = auth()->user()->stories()->notPublished()->count() > 10;

        if ($draftLimits) {
            toast_warning(__('You have reached your draft limit, please delete old drafts first'));

            return redirect()->route('feed.home');
        }

        $story = Story::create([
            'user_id' => auth()->id(),
            'title' => '',
            'slug' => Str::uuid(),
            'body_rendered' => '',
            'meta' => [
                'meta_title' => '',
                'meta_description' => '',
                'meta_canonical_url' => '',
            ],
        ]);

        if (! $story) {
            toast_warning(__('Something went wrong, please try again'));

            return back();
        }

        return to_route('story.edit', ['story' => $story]);
    }

    public function repost(Request $request, Story $story)
    {
        $this->authorize('add_stories');

        $draftLimits = auth()->user()->stories()->notPublished()->count() > 10;

        if ($draftLimits) {
            toast_warning(__('You have reached your draft limit, please delete old drafts first'));

            return redirect()->route('feed.home');
        }

        $repost = Story::create([
            'user_id' => auth()->id(),
            'original_story_id' => $story->id,
            'type' => PostType::REPOST->value,
            'title' => '',
            'slug' => Str::uuid(),
            'body_rendered' => '',
            'meta' => [
                'meta_title' => '',
                'meta_description' => '',
                'meta_canonical_url' => '',
            ],
        ]);

        if (! $repost) {
            toast_warning(__('Something went wrong, please try again'));

            return back();
        }

        return to_route('story.edit', ['story' => $repost]);
    }

    public function save(Story $story, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'min:5', 'max:160'],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:250'],
            'community.id' => ['nullable', 'integer', 'exists:communities,id'],
            'meta.title' => ['nullable', 'sometimes', 'max:60'],
            'meta.description' => ['nullable', 'sometimes', 'max:156'],
            'meta.canonical_url' => ['nullable', 'sometimes', 'url:http,https'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (isset($request->title) && isValidUuid($request->slug)) {
            $story->slug = null;
        }

        $story->forceFill([
            'community_id' => $request->community['id'] ?? null,
            'title' => strip_tags($request->title),
            'subtitle' => strip_tags($request->subtitle),
            'body' => $request->body,
            'meta' => [
                'meta_title' => $request->meta['title'] ?? '',
                'meta_description' => $request->meta['description'] ?? '',
                'meta_canonical_url' => $request->meta['canonical_url'] ?? '',
            ],
            'content_visibility' => 'All',
            'is_comments_disabled' => $request->is_comments_disabled ?? false,
            'is_pinned' => $request->is_pinned ?? false,
            'is_nsfw' => $request->is_nsfw ?? false,
        ])->save();

        $tags = collect($request->tags);
        $story->retag($tags);
        $story->load('community');

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => StoryEditorResource::make($story),
            'tags' => $story->tagArray,
            'message' => $story->isPublished() ? __('Story updated') : __('Story saved'),
        ]);
    }

    public function publish(Story $story, Request $request)
    {
        $rules = [
            'title' => ['required', 'string', 'min:5', 'max:160'],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:250'],
            'community.id' => ['nullable', 'integer', 'exists:communities,id'],
            'meta.title' => ['nullable', 'sometimes', 'max:60'],
            'meta.description' => ['nullable', 'sometimes', 'max:156'],
            'meta.canonical_url' => ['nullable', 'sometimes', 'url:http,https'],
        ];

        try {
            Validator::make($request->all(), $rules)->validate();
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $key => $error) {
                toast_error($error[0]);
            }

            return back();
        }

        if (isValidUuid($story->slug)) {
            $story->slug = null;
        }

        $story->forceFill([
            'community_id' => $request->community['id'] ?? null,
            'title' => strip_tags($request->title),
            'subtitle' => strip_tags($request->subtitle),
            'body' => $request->body,
            'meta' => [
                'meta_title' => $request->meta['title'] ?? '',
                'meta_description' => $request->meta['description'] ?? '',
                'meta_canonical_url' => $request->meta['canonical_url'] ?? '',
            ],
            'content_visibility' => 'All',
            'is_comments_disabled' => $request->is_comments_disabled ?? false,
            'is_nsfw' => $request->is_nsfw ?? false,
            'published_at' => now(),
        ])->save();

        $tags = collect($request->tags);
        $story->retag($tags);

        if ($story->isPublished()) {
            $isPostsAutoApprovalEnabled = config('alma.posts_auto_approval');

            if ($isPostsAutoApprovalEnabled) {
                $story->update(['approved_at' => now()]);
            }

            event(new CreatedPublishedStory($story));
        }

        toast_success(__('Story published'));

        return redirect()->route('story.show', ['story' => $story]);
    }

    public function show(Story $story, Request $request)
    {
        $seo = new SeoGeneratorService();

        if ($story->isPublished()) {
            $cacheKey = "watched-story-{$story->slug}-ip-{$request->ip()}";

            if (! Cache::has($cacheKey)) {
                Cache::put($cacheKey, $story->views_count, now()->addHours(24));

                $story->increment('views_count');
            }
        }

        $story
            ->load(['user', 'user.primaryBadge', 'favorites', 'community', 'tags', 'originalPost'])
            ->loadCount(['likers', 'favoriters', 'allComments' => function (Builder $query) {
                $query->whereNull('deleted_at');
            }]);
        // dd($story->meta['meta_canonical_url']);
        $recomendations = Story::with(['user', 'favorites', 'community'])
            ->where('id', '!=', $story->id)
            ->where(function (Builder $query) use ($story) {
                $query->whereFullText('title', $story->title);

                if ($story->community_id !== null) {
                    $query->orWhere('community_id', $story->community->id);
                }
            })
            ->published()
            ->withCount(['comments', 'favoriters', 'likers'])
            ->inRandomOrder()
            ->simplePaginate(10);

        if ($request->wantsJson()) {
            return StoryCardResource::collection($recomendations);
        }

        return Inertia::render('Story/Show', [
            'story' => StoryResource::make($story),
            'recomendations' => StoryCardResource::collection($recomendations),
        ])
            ->title($story->meta['meta_title'] ?? $story->title)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($story->isNotPublished() ? $seo->getMetaRobotsNoIndexble() : $seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl($story->meta['meta_canonical_url'] ?? url()->current()))
            ->ogTitle($story->meta['meta_title'] ?? $story->title)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function edit(Story $story)
    {
        $this->authorize('edit', $story);

        $story->load(['community', 'originalPost']);
        $tags = $story->tagArray;

        $seo = new SeoGeneratorService();

        return Inertia::render('Story/Edit', [
            'story' => StoryEditorResource::make($story),
            'originalStory' => $story->isRepost() ? StoryOriginalCardResource::make($story->originalPost) : false,
            'tags' => $tags,
        ])
            ->title(__('Edit Story'))
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsNoIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle(__('Edit Story'))
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }

    public function getUserJoinedCommunities(Request $request)
    {
        $followingCommunityIds = auth()->user()->followings()->where('followable_type', 'App\Models\Community')->pluck('followable_id')->toArray();

        $communities = ! empty($followingCommunityIds)
            ? Community::whereIn('id', $followingCommunityIds)->active()->get()
            : Community::active()->limit(20)->get();

        return CommunityStoryCardResource::collection($communities);
    }

    public function destroy(Story $story)
    {
        $this->authorize('delete', $story);

        $story->isNotPublished() ? $story->forceDelete() : $story->delete();

        if ($story->isPublished()) {
            event(new DeletedPublishedStory($story));
        }

        return redirect()->route('feed.home');
    }
}
