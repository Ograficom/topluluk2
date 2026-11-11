<?php

namespace App\Filament\Resources\Channels\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ChannelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Temel bilgiler')
                    ->description('Kanalın adını, bağlantı adresini ve ziyaretçilere gösterilecek açıklamayı düzenleyin.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Kanal adı')
                            ->placeholder('Örn. Teknoloji')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug($state ?? ''))),

                        TextInput::make('slug')
                            ->label('URL adresi')
                            ->placeholder('kanal-adresi')
                            ->helperText('Kanalın web adresinde kullanılacak benzersiz kısa ad.')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Textarea::make('description')
                            ->label('Kanal açıklaması')
                            ->placeholder('Bu kanalda hangi konuların paylaşılacağını açıklayın.')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Kanal görseli')
                    ->description('Bu görsel kanal sayfasında ve sosyal medya bağlantı önizlemelerinde otomatik kullanılır.')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Profil görseli')
                            ->image()
                            ->disk('public')
                            ->directory('channels')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(5120)
                            ->helperText('JPG, PNG veya WebP. En fazla 5 MB. Yeni görsel yüklediğinizde SEO ve sosyal paylaşım görseli otomatik güncellenir.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('SEO ve arama görünümü')
                    ->description('Google sonuçlarında görünecek başlık ve açıklamayı belirleyin. Boş bıraktığınız alanlarda kanal adı ve açıklaması kullanılır.')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('SEO başlığı')
                            ->placeholder('Boş bırakılırsa kanal adı kullanılır')
                            ->maxLength(255)
                            ->helperText('Önerilen uzunluk: yaklaşık 50–60 karakter.'),

                        TextInput::make('canonical_url')
                            ->label('Canonical URL')
                            ->placeholder('https://ografi.com/kanallar/...')
                            ->url()
                            ->maxLength(2048)
                            ->helperText('Özel bir adres kullanmıyorsanız boş bırakın.'),

                        Textarea::make('meta_description')
                            ->label('Meta açıklaması')
                            ->placeholder('Kanalı arama sonuçlarında tanıtacak kısa açıklama.')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Önerilen uzunluk: yaklaşık 140–160 karakter.')
                            ->columnSpanFull(),

                        TextInput::make('meta_keywords')
                            ->label('Anahtar kelimeler')
                            ->placeholder('teknoloji, yazılım, donanım')
                            ->maxLength(2000)
                            ->columnSpanFull(),

                        Toggle::make('robots_index')
                            ->label('Arama motorlarında indekslensin')
                            ->default(true)
                            ->helperText('Kapalı olduğunda arama motorlarına noindex talimatı gönderilir.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Yayın durumu')
                    ->description('Kanalın ziyaretçilere açık olup olmayacağını belirleyin.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Kanal yayında')
                            ->default(true)
                            ->helperText('Kapalı kanallar ziyaretçilere gösterilmez.'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
