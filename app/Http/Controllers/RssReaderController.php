<?php

namespace App\\Http\\Controllers;

use App\\Services\\RssFeedService;
use Illuminate\\Http\\Response;

class RssReaderController extends Controller
{
    public function index(RssFeedService $feeds): Response
    {
        return response($feeds->xml('all'), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
