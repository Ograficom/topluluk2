<?php

namespace App\Filament\Resources\PostReports\Schemas;

use App\Models\PostReport;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Şikayet bilgileri')
                ->schema([
                    Placeholder::make('reported_post')
                        ->label('Bildirilen yazı')
                        ->content(fn (?PostReport $record): string => $record?->post?->title ?? 'Silinmiş yazı'),
                    Placeholder::make('reporting_user')
                        ->label('Bildiren kullanıcı')
                        ->content(fn (?PostReport $record): string => $record?->user?->name ?? 'Silinmiş kullanıcı'),
                    Select::make('reason')
                        ->label('Bildirim nedeni')
                        ->options([
                            'spam' => 'Spam veya reklam',
                            'misinformation' => 'Yanlış veya yanıltıcı bilgi',
                            'harassment' => 'Hakaret veya taciz',
                            'copyright' => 'Telif hakkı ihlali',
                            'inappropriate' => 'Uygunsuz içerik',
                            'other' => 'Diğer',
                        ])
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('details')
                        ->label('Kullanıcı açıklaması')
                        ->rows(6)
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ])->columns(2),
            Section::make('İnceleme')
                ->schema([
                    Select::make('status')
                        ->label('Şikayet durumu')
                        ->options([
                            'pending' => 'Bekliyor',
                            'reviewing' => 'İnceleniyor',
                            'resolved' => 'İşlem yapıldı',
                            'rejected' => 'Uygun bulunmadı',
                        ])
                        ->required(),
                ]),
        ]);
    }
}
