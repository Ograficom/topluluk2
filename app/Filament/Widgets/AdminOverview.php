<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Channels\ChannelResource;
use App\Filament\Resources\Comments\CommentResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Channel;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverview extends StatsOverviewWidget
{
    protected static ?bool $isLazyLoaded = false;

    protected ?string $heading = 'OgraFi genel durumu';

    protected ?string $description = 'Topluluk içeriklerini ve hesapları yönetin.';

    protected function getStats(): array
    {
        return [
            Stat::make('Üyeler', number_format(User::query()->count()))
                ->description('Kayıtlı hesaplar')
                ->icon(Heroicon::OutlinedUsers)
                ->color('info')
                ->url(UserResource::getUrl()),
            Stat::make('Yazılar', number_format(Post::query()->count()))
                ->description('Tüm içerikler')
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('primary')
                ->url(PostResource::getUrl()),
            Stat::make('Taslaklar', number_format(Post::query()->where('is_published', false)->count()))
                ->description('Henüz yayımlanmamış')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->color('warning')
                ->url(PostResource::getUrl()),
            Stat::make('Yorumlar', number_format(Comment::query()->count()))
                ->description('Topluluk yorumları')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->color('success')
                ->url(CommentResource::getUrl()),
            Stat::make('Kanallar', number_format(Channel::query()->count()))
                ->description('İçerik kategorileri')
                ->icon(Heroicon::OutlinedRectangleStack)
                ->color('gray')
                ->url(ChannelResource::getUrl()),
            Stat::make('Etiketler', number_format(Tag::query()->count()))
                ->description('İçerik etiketleri')
                ->icon(Heroicon::OutlinedHashtag)
                ->color('gray')
                ->url(TagResource::getUrl()),
            Stat::make('İletişim formları', number_format(SupportTicket::query()->count()))
                ->description(number_format(SupportTicket::query()->where('status', SupportTicket::STATUS_NEW)->count()).' yeni talep')
                ->icon(Heroicon::OutlinedLifebuoy)
                ->color('warning')
                ->url(SupportTicketResource::getUrl()),
        ];
    }
}
