<?php

namespace App\Filament\Resources\SitePages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SitePageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Sayfa başlığı')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug($state ?? ''))),
            TextInput::make('slug')
                ->label('URL / Slug')
                ->required()
                ->maxLength(180)
                ->unique(ignoreRecord: true)
                ->helperText('Örnek: about → /pages/about'),
            RichEditor::make('content')
                ->label('Sayfa içeriği')
                ->columnSpanFull()
                ->fileAttachmentsDisk('public')
                ->fileAttachmentsDirectory('site-pages')
                ->toolbarButtons([
                    'bold', 'italic', 'underline', 'strike',
                    'h2', 'h3', 'bulletList', 'orderedList',
                    'blockquote', 'link', 'attachFiles',
                    'undo', 'redo',
                ]),
            TextInput::make('meta_title')
                ->label('SEO başlığı')
                ->maxLength(255),
            Textarea::make('meta_description')
                ->label('SEO açıklaması')
                ->rows(3)
                ->maxLength(500)
                ->columnSpanFull(),
            Toggle::make('is_published')
                ->label('Yayında')
                ->default(true),
            DateTimePicker::make('published_at')
                ->label('Yayın tarihi')
                ->timezone('Europe/Istanbul')
                ->default(now())
                ->seconds(false)
                ->helperText('Boş bırakılırsa sayfa hemen yayınlanır. İleri bir tarih seçerseniz o tarihte yayınlanır.'),
        ]);
    }
}
