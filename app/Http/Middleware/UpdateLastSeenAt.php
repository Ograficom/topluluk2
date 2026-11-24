<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeenAt
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && Schema::hasColumn('users', 'last_seen_at')
            && (! $user->last_seen_at || $user->last_seen_at->lt(now()->subMinutes(2)))
        ) {
            $user->forceFill(['last_seen_at' => now()])->saveQuietly();
        }

        $previousAvatar = $user?->avatar_path;
        $response = $next($request);
        $currentUser = $request->user();
        if ($currentUser && $response->getStatusCode() < 400 && Schema::hasTable('task_activity_days')) {
            app(\App\Services\CommunityTaskService::class)->observeUser($currentUser->fresh(), $previousAvatar);
        }
        return $response;
    }
}
