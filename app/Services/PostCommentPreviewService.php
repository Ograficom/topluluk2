<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Comment;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class PostCommentPreviewService
{
    public function attachToPosts(mixed $posts, int $limit = 3): void
    {
        $collection = $this->extractCollection($posts);
        if ($collection->isEmpty()) {
            return;
        }

        $postIds = $collection->pluck('id')
            ->filter()
            ->unique()
            ->values();

        if ($postIds->isEmpty()) {
            return;
        }

        $comments = Comment::query()
            ->approved()
            ->whereNull('parent_id')
            ->whereIn('post_id', $postIds)
            ->with('user:id,name,username,profile_photo_path')
            ->withCount([
                'reactions as likes_count' => fn ($query) => $query->where('is_like', true),
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(max(50, $postIds->count() * 12))
            ->get(['id', 'post_id', 'user_id', 'author_name', 'author_email', 'content', 'created_at']);

        $byPost = [];
        $commenterKeysByPost = [];
        $latestPreviewByPost = [];
        foreach ($comments as $comment) {
            $postId = (int) $comment->post_id;
            if (!isset($byPost[$postId])) {
                $byPost[$postId] = [];
            }

            $email = mb_strtolower(trim((string) ($comment->author_email ?? '')));
            $user = $comment->user;
            $previewKey = $user
                ? 'user:' . $user->id
                : ($email !== '' ? 'guest:' . $email : 'comment:' . $comment->id);

            $commenterKeysByPost[$postId][$previewKey] = true;

            if (!isset($latestPreviewByPost[$postId])) {
                $previewText = trim((string) preg_replace('/\[(gif|img):([^\]\s]+|data:image\/[^\]\s]+)\]/i', '', (string) ($comment->content ?? '')));
                $previewText = trim((string) preg_replace('/\s+/', ' ', strip_tags($previewText)));
                $latestAvatar = $comment->user?->profile_photo_url;
                if (!$latestAvatar && $email !== '') {
                    $latestAvatar = 'https://www.gravatar.com/avatar/' . md5($email) . '?s=96&d=identicon';
                }
                if (!$latestAvatar) {
                    $latestAvatar = 'https://placehold.co/96x96';
                }
                $latestPreviewByPost[$postId] = [
                    'id' => $comment->id,
                    'name' => trim((string) ($comment->user?->name ?? $comment->author_name ?? 'Topluluk uyesi')),
                    'avatar' => $latestAvatar,
                    'content' => $previewText,
                    'created_at' => optional($comment->created_at)->toIso8601String(),
                    'likes_count' => (int) ($comment->likes_count ?? 0),
                ];
            }

            if (count($byPost[$postId]) >= $limit) {
                continue;
            }

            $name = trim((string) ($user?->name ?? $comment->author_name ?? 'Topluluk uyesi'));
            if ($name === '') {
                $name = 'Topluluk uyesi';
            }

            $avatar = $user?->profile_photo_url;
            if (!$avatar && $email !== '') {
                $avatar = 'https://www.gravatar.com/avatar/' . md5($email) . '?s=96&d=identicon';
            }
            if (!$avatar) {
                $avatar = 'https://placehold.co/96x96';
            }

            $exists = false;
            foreach ($byPost[$postId] as $entry) {
                if (($entry['key'] ?? null) === $previewKey) {
                    $exists = true;
                    break;
                }
            }
            if ($exists) {
                continue;
            }

            $byPost[$postId][] = [
                'key' => $previewKey,
                'id' => $user?->id,
                'name' => $name,
                'avatar' => $avatar,
                'likes_count' => (int) ($comment->likes_count ?? 0),
            ];
        }

        $collection->each(function ($post) use ($byPost, $commenterKeysByPost, $latestPreviewByPost) {
            $postId = $post->id ?? null;
            $post->commenter_previews = $postId ? ($byPost[(int) $postId] ?? []) : [];
            $commenterCount = $postId ? count($commenterKeysByPost[(int) $postId] ?? []) : 0;
            $post->commenter_preview_extra_count = max(0, $commenterCount - count($post->commenter_previews));
            $post->latest_comment_preview = $postId ? ($latestPreviewByPost[(int) $postId] ?? null) : null;
        });
    }

    private function extractCollection(mixed $posts): Collection
    {
        if ($posts instanceof LengthAwarePaginator || $posts instanceof Paginator || $posts instanceof PaginatorContract) {
            return $posts->getCollection();
        }

        if ($posts instanceof Collection) {
            return $posts;
        }

        return collect($posts);
    }
}
