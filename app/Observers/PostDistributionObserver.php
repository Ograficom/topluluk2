<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostDistributionObserver
{
    private const FLAGS = [
        'followers_only',
        'noindex',
        'is_ai_product',
        'hide_from_feeds',
        'suppress_follower_notifications',
    ];

    public function saving(Post $post): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        /** @var Request $request */
        $request = request();
        $routeName = (string) optional($request->route())->getName();

        if (! str_starts_with($routeName, 'blog.')) {
            return;
        }

        if (! in_array(strtolower($request->method()), ['post', 'put', 'patch'], true)) {
            return;
        }

        foreach (self::FLAGS as $flag) {
            if ($request->has($flag)) {
                $post->setAttribute($flag, $request->boolean($flag));
            }
        }
    }
}
