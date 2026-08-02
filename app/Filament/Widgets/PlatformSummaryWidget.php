<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CommentResource;
use App\Filament\Resources\PostReportResource;
use App\Filament\Resources\RssFeedResource;
use App\Filament\Resources\TagResource;
use App\Models\Category;
use App\Models\Comment;
use App\Models\PostReport;
use App\Models\RssFeed;
use App\Models\Tag;
use LaBoiteACode\FilamentDashboardWidgets\Data\Detail;
use LaBoiteACode\FilamentDashboardWidgets\Widgets\DetailListWidget;

class PlatformSummaryWidget extends DetailListWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Platform Özeti';

    protected function getDetails(): array
    {
        $pendingReports = PostReport::query()->where('status', 'pending')->count();
        $pendingComments = Comment::query()->where('is_approved', false)->count();
        $activeFeeds = RssFeed::query()->where('is_enabled', true)->count();

        return [
            Detail::make('Kategoriler', Category::query()->count())
                ->icon('heroicon-o-folder')
                ->url(CategoryResource::getUrl('index')),

            Detail::make('Etiketler', Tag::query()->count())
                ->icon('heroicon-o-tag')
                ->url(TagResource::getUrl('index')),

            Detail::make('Onay Bekleyen Yorumlar', $pendingComments)
                ->icon('heroicon-o-chat-bubble-left-right')
                ->badge($pendingComments > 0 ? (string) $pendingComments : null)
                ->badgeColor('warning')
                ->url(CommentResource::getUrl('index')),

            Detail::make('Bekleyen Şikayetler', $pendingReports)
                ->icon('heroicon-o-flag')
                ->badge($pendingReports > 0 ? (string) $pendingReports : null)
                ->badgeColor('danger')
                ->url(PostReportResource::getUrl('index')),

            Detail::make('Aktif RSS Kaynakları', $activeFeeds)
                ->icon('heroicon-o-rss')
                ->url(RssFeedResource::getUrl('index')),
        ];
    }
}
