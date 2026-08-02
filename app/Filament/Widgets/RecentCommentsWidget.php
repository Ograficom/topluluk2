<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Support\Str;
use LaBoiteACode\FilamentDashboardWidgets\Data\RecentItem;
use LaBoiteACode\FilamentDashboardWidgets\Widgets\RecentItemsWidget;

class RecentCommentsWidget extends RecentItemsWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Son Yorumlar';

    protected function getLimit(): ?int
    {
        return 5;
    }

    protected function getViewAllUrl(): ?string
    {
        return CommentResource::getUrl('index');
    }

    protected function getItems(): array
    {
        return Comment::query()
            ->with(['user', 'post'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (Comment $comment) {
                $authorName = $comment->user?->name ?? $comment->author_name ?? 'Ziyaretçi';

                return RecentItem::make($authorName, Str::limit(strip_tags((string) $comment->content), 80))
                    ->meta($comment->created_at?->diffForHumans())
                    ->avatar($comment->user?->profile_photo_url)
                    ->badge($comment->is_approved ? 'Onaylı' : 'Beklemede')
                    ->badgeColor($comment->is_approved ? 'success' : 'warning')
                    ->url(CommentResource::getUrl('edit', ['record' => $comment]));
            })
            ->all();
    }
}
