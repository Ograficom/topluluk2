<?php

namespace App\Http\Controllers;

use App\Http\Resources\StoryCardResource;
use App\Http\Resources\TagResource;
use App\Models\Story;
use App\Services\SeoGeneratorService;
use Cviebrock\EloquentTaggable\Models\Tag;
use Inertia\Inertia;

class TagsController extends Controller
{
    public function show(Tag $tag)
    {
        $seo = new SeoGeneratorService();

        $stories = Story::with(['user', 'favorites', 'community', 'participants', 'originalPost'])
            ->withAllTags($tag)
            ->withCount(['allComments', 'favoriters', 'reposts', 'likers'])
            ->approved()
            ->latest()
            ->simplePaginate(10);

        return Inertia::render('Tag/Show', [
            'stories' => StoryCardResource::collection($stories),
            'tag' => TagResource::make($tag),
        ])
            ->title($tag->name)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle($tag->name)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }
}
