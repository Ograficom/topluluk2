<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\SeoGeneratorService;
use Inertia\Inertia;

class PageController extends Controller
{
    public function show(Page $page)
    {
        $seo = new SeoGeneratorService();

        return Inertia::render('Page', compact('page'))
            ->title($page->title)
            ->description($seo->getMetaDescription())
            ->tag($seo->getMetaCsrfToken())
            ->tag($seo->getMetaRobotsIndexble())
            ->tag($seo->getCurrentCanonicalUrl(url()->current()))
            ->ogTitle($page->title)
            ->ogDescription($seo->getOGDescription())
            ->ogImage($seo->getMetaImage())
            ->tag($seo->getOGSiteName())
            ->tag($seo->getOGType())
            ->tag($seo->getOGLocale())
            ->tag($seo->getOGUrl())
            ->twitterLargeCard();
    }
}
