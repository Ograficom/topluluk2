<?php

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ReactionDetailsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isDetailsRequest($request)) {
            return $this->details($request);
        }

        return $next($request);
    }

    private function isDetailsRequest(Request $request): bool
    {
        if (! $request->isMethod('GET') || ! $request->boolean('details')) {
            return false;
        }

        return preg_match('#(?:^|/)posts/[^/]+/reactions$#u', trim($request->path(), '/')) === 1;
    }

    private function details(Request $request): JsonResponse
    {
        $path = trim($request->path(), '/');
        preg_match('#(?:^|/)posts/([^/]+)/reactions$#u', $path, $matches);
        $slug = rawurldecode((string) ($matches[1] ?? ''));

        abort_if($slug === '', 404);

        $post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $allReactions = $post->reactions()
            ->with([
                'user:id,name,username,profile_photo_path',
                'type:id,label,short_code,emoji,gif_url',
            ])
            ->latest('id')
            ->get();

        $registered = $allReactions
            ->whereNotNull('user_id')
            ->unique('user_id')
            ->values();

        $items = $registered
            ->map(function ($reaction): ?array {
                $user = $reaction->user;
                if (! $user) {
                    return null;
                }

                $type = $reaction->type;
                $name = trim((string) $user->name) ?: 'Ografi kullanıcısı';
                $username = trim((string) $user->username);
                $avatar = trim((string) $user->profile_photo_url);
                $initials = collect(preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY))
                    ->take(2)
                    ->map(fn ($part) => Str::upper(Str::substr((string) $part, 0, 1)))
                    ->implode('');

                $gif = trim((string) ($type?->gif_url ?? ''));
                $reactionImage = null;
                if ($gif !== '') {
                    if (Str::startsWith($gif, ['http://', 'https://', '//', 'data:', '/'])) {
                        $reactionImage = Str::startsWith($gif, '//') ? 'https:' . $gif : $gif;
                    } elseif (Str::startsWith($gif, 'storage/')) {
                        $reactionImage = asset($gif);
                    } else {
                        $reactionImage = asset('storage/' . ltrim($gif, '/'));
                    }
                }

                return [
                    'id' => (int) $user->id,
                    'name' => $name,
                    'username' => $username,
                    'profile_url' => $username !== '' ? route('users.show', ['user' => $username]) : null,
                    'avatar' => $avatar !== '' ? $avatar : null,
                    'initials' => $initials !== '' ? $initials : Str::upper(Str::substr($name, 0, 1)),
                    'reaction' => [
                        'label' => trim((string) ($type?->label ?? 'Tepki')) ?: 'Tepki',
                        'emoji' => trim((string) ($type?->emoji ?? '')) ?: null,
                        'image_url' => $reactionImage,
                    ],
                ];
            })
            ->filter()
            ->values();

        $total = (int) $allReactions->count();
        $anonymousCount = max($total - $items->count(), 0);

        return response()->json([
            'total' => $total,
            'reactors_count' => (int) $items->count(),
            'anonymous_count' => $anonymousCount,
            'overflow' => min(max($total - 5, 0), 99),
            'preview' => $items->take(5)->values()->all(),
            'items' => $items->all(),
        ]);
    }
}
