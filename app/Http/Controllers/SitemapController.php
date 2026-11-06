<?php

namespace App\Http\Controllers;

use App\Models\SitemapSetting;
use App\Services\SitemapService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function __invoke(Request $request, SitemapService $sitemap): Response
    {
        abort_unless(SitemapSetting::current()->enabled, 404);

        $raw = $request->boolean('raw');

        return response($sitemap->xml(), 200, [
            // Normal endpoint remains machine-readable XML for Google and Yandex.
            // ?raw=1 asks the browser to display the literal XML markup as text.
            'Content-Type' => $raw ? 'text/plain; charset=UTF-8' : 'application/xml; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
