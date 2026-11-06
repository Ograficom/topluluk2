<?php

namespace App\Http\Controllers;

use App\Models\SitemapSetting;
use App\Services\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(SitemapService $sitemap): Response
    {
        abort_unless(SitemapSetting::current()->enabled, 404);

        return response($sitemap->xml(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
