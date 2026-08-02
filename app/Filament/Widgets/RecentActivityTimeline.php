<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PostResource;
use App\Filament\Resources\UserResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;
use LaBoiteACode\FilamentDashboardWidgets\Data\TimelineEvent;
use LaBoiteACode\FilamentDashboardWidgets\Widgets\TimelineWidget;

class RecentActivityTimeline extends TimelineWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Son Etkinlik';

    protected function getLimit(): ?int
    {
        return 8;
    }

    protected function getEvents(): array
    {
        $posts = Post::query()
            ->published()
            ->with('author')
            ->latest('published_at')
            ->limit(8)
            ->get()
            ->map(fn (Post $post) => TimelineEvent::make(Str::limit($post->title, 60))
                ->timestamp($post->published_at)
                ->actor($post->author?->name ?? 'Ografi')
                ->badge('Yeni Gönderi')
                ->badgeColor('primary')
                ->url(PostResource::getUrl('edit', ['record' => $post])));

        $users = User::query()
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (User $user) => TimelineEvent::make($user->name)
                ->timestamp($user->created_at)
                ->badge('Yeni Üye')
                ->badgeColor('success')
                ->url(UserResource::getUrl('edit', ['record' => $user])));

        $comments = Comment::query()
            ->with('user')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Comment $comment) => TimelineEvent::make(Str::limit(strip_tags((string) $comment->content), 60))
                ->timestamp($comment->created_at)
                ->actor($comment->user?->name ?? $comment->author_name ?? 'Ziyaretçi')
                ->badge('Yeni Yorum')
                ->badgeColor('warning'));

        return $posts->concat($users)->concat($comments)
            ->sortByDesc(fn (TimelineEvent $event) => $event->getTimestamp())
            ->take(8)
            ->values()
            ->all();
    }
}
