<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class IndexNowService
{
    private static array $pendingUrls = [];
    private static bool $flushRegistered = false;

    public function queue(string $url): void
    {
        self::$pendingUrls[$url] = $url;

        if (self::$flushRegistered) {
            return;
        }

        self::$flushRegistered = true;
        app()->terminating(function () {
            $urls = array_values(self::$pendingUrls);
            self::$pendingUrls = [];
            self::$flushRegistered = false;

            try {
                app(self::class)->submit($urls);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }

    public function submit(array $urls): ?Response
    {
        if (! (bool) config('services.indexnow.enabled', true)) {
            return null;
        }

        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'ografi.com';
        $key = trim((string) config('services.indexnow.key'));
        $urls = collect($urls)
            ->map(fn ($url) => trim((string) $url))
            ->filter(fn ($url) => filter_var($url, FILTER_VALIDATE_URL)
                && strcasecmp((string) parse_url($url, PHP_URL_HOST), $host) === 0)
            ->unique()
            ->take(10000)
            ->values()
            ->all();

        if ($key === '' || $urls === []) {
            return null;
        }

        return Http::asJson()
            ->acceptJson()
            ->connectTimeout(3)
            ->timeout(8)
            ->retry(2, 250, throw: false)
            ->post((string) config('services.indexnow.endpoint', 'https://api.indexnow.org/indexnow'), [
                'host' => $host,
                'key' => $key,
                'keyLocation' => rtrim((string) config('app.url'), '/') . '/' . $key . '.txt',
                'urlList' => $urls,
            ]);
    }
}
