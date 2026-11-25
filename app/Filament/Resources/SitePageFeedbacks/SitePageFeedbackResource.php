<?php

namespace App\Filament\Resources\SitePageFeedbacks;

use App\Filament\Resources\SitePageFeedbacks\Pages\ListSitePageFeedbacks;
use App\Models\SitePageFeedback;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SitePageFeedbackResource extends Resource
{
    protected static ?string $model = SitePageFeedback::class;

    protected static ?string $slug = 'site-page-feedbacks';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'Ayarlar';

    protected static ?string $navigationLabel = 'Sayfa geri bildirimleri';

    protected static ?string $modelLabel = 'sayfa geri bildirimi';

    protected static ?string $pluralModelLabel = 'Sayfa geri bildirimleri';

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_admin;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sitePage.title')->label('Sayfa')->searchable()->sortable(),
                TextColumn::make('vote')->label('Değerlendirme')->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'like' ? 'Olumlu' : 'Olumsuz')
                    ->color(fn (string $state): string => $state === 'like' ? 'success' : 'danger'),
                TextColumn::make('email')->label('E-posta')->placeholder('—')->searchable()->copyable(),
                TextColumn::make('message')->label('Geri bildirim')->placeholder('Sadece oy')->wrap()->searchable(),
                TextColumn::make('submitted_at')->label('Mesaj tarihi')->dateTime('d.m.Y H:i')->placeholder('—')->sortable(),
                TextColumn::make('updated_at')->label('Son değerlendirme')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('site_page_id')->label('Sayfa')->relationship('sitePage', 'title')->searchable()->preload(),
                SelectFilter::make('vote')->label('Değerlendirme')->options(['like' => 'Olumlu', 'dislike' => 'Olumsuz']),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListSitePageFeedbacks::route('/')];
    }
}
