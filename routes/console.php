<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\RssFeed;
use App\Models\Post;
use App\Support\PostSeoText;
use App\Services\Rss\RssSyncService;
use App\Console\Commands\GenerateVideoSubtitles;
use Symfony\Component\Console\Input\ArrayInput;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('rss:sync {--feed_id=} {--force}', function (RssSyncService $service) {
    $feedId = $this->option('feed_id');
    $force = (bool) $this->option('force');

    if ($feedId) {
        $feed = RssFeed::find($feedId);
        if (!$feed) {
            $this->error("Feed not found: {$feedId}");
            return 1;
        }

        $result = $service->syncFeed($feed, $force);
        if ($result['error']) {
            $this->error("RSS sync error: {$result['error']}");
            return 1;
        }

        $this->info("OK. items_new={$result['items_new']} items_updated={$result['items_updated']} posts_created={$result['posts_created']} posts_updated={$result['posts_updated']}");
        return 0;
    }

    $summary = $service->syncAllEnabled();
    $this->info("OK. feeds={$summary['feeds']} items_new={$summary['items_new']} items_updated={$summary['items_updated']} posts_created={$summary['posts_created']} posts_updated={$summary['posts_updated']} errors={$summary['errors']}");
    return $summary['errors'] ? 1 : 0;
})->purpose('Sync RSS feeds and import as posts');

Schedule::command('rss:sync')
    ->everyFiveMinutes()
    ->withoutOverlapping();

Artisan::command('posts:seo-backfill {--force : Replace existing generated metadata too}', function () {
    $force = (bool) $this->option('force');
    $updated = 0;

    Post::query()->orderBy('id')->chunkById(200, function ($posts) use ($force, &$updated) {
        foreach ($posts as $post) {
            $title = PostSeoText::title($post->title);
            $description = PostSeoText::description($post->excerpt, $post->content, $post->title);
            $changes = [];
            if ($force || blank($post->meta_title)) {
                $changes['meta_title'] = $title;
            }
            if ($force || blank($post->meta_description)) {
                $changes['meta_description'] = $description;
            }
            if ($changes !== [] && collect($changes)->contains(fn ($value, $key) => $post->{$key} !== $value)) {
                $post->forceFill($changes)->saveQuietly();
                $updated++;
            }
        }
    });

    $this->info("SEO metadata updated for {$updated} posts.");
})->purpose('Backfill SEO titles and descriptions for all posts');

Artisan::command('subtitles:generate {postId}', function ($postId) {
    $command = new GenerateVideoSubtitles();
    $command->setLaravel(app());
    return $command->run(new ArrayInput(['postId' => $postId]), $this->output);
})->purpose('Videolar için otomatik altyazı üret.');
