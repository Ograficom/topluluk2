<?php

namespace App\Http\Controllers;

use App\Services\RssFeedService;
use Illuminate\Http\Response;

class RssController extends Controller
{
    public function all(RssFeedService $feeds): Response
    {
        return $this->response($feeds->xml('all'));
    }

    public function latest(RssFeedService $feeds): Response
    {
        return $this->response($feeds->xml('latest'));
    }

    public function author(string $username, RssFeedService $feeds): Response
    {
        return $this->response($feeds->xml('author', $username));
    }

    public function channel(string $slug, RssFeedService $feeds): Response
    {
        return $this->response($feeds->xml('channel', $slug));
    }

    public function tag(string $slug, RssFeedService $feeds): Response
    {
        return $this->response($feeds->xml('tag', $slug));
    }

    private function response(string $xml): Response
    {
        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
