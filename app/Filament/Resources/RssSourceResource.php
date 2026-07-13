<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RssSourceResource\Pages;
use App\Models\RssSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RssSourceResource extends Resource
{
    protected static ?string $model = RssSource::class;
    protected static ?string $navigationIcon = 'heroicon-o-rss';
    protected static ?int $navigationSort = 18;

    public static function getNavigationGroup(): ?string
    {
        return __('Main');
    }

    public static function getPluralModelLabel(): string
    {
        return 'RSS Kaynakları';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('RSS Kaynağı')->schema([
                Forms\Components\TextInput::make('name')->label('Ad')->required()->maxLength(255),
                Forms\Components\TextInput::make('url')->label('RSS / Atom URL')->url()->required()->maxLength(2048)->columnSpanFull(),
                Forms\Components\Select::make('user_id')->label('Gönderi sahibi')->relationship('user', 'email')->searchable()->preload()->required(),
                Forms\Components\Select::make('community_id')->label('Topluluk')->relationship('community', 'name')->searchable()->preload(),
                Forms\Components\TextInput::make('item_limit')->label('Çalışma başına içerik')->numeric()->minValue(1)->maxValue(20)->default(5)->required(),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
                Forms\Components\Toggle::make('auto_publish')->label('Gönderi akışında otomatik yayınla')->helperText('Kapalıysa içerikler taslak oluşturulur.')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Kaynak')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('user.email')->label('Gönderi sahibi'),
            Tables\Columns\TextColumn::make('community.name')->label('Topluluk')->placeholder('Genel'),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            Tables\Columns\IconColumn::make('auto_publish')->label('Otomatik yayın')->boolean(),
            Tables\Columns\TextColumn::make('last_run_at')->label('Son çalışma')->dateTime('d.m.Y H:i')->placeholder('Henüz çalışmadı'),
            Tables\Columns\TextColumn::make('last_error')->label('Son hata')->limit(45)->tooltip(fn (RssSource $record) => $record->last_error),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRssSources::route('/'),
            'create' => Pages\CreateRssSource::route('/create'),
            'edit' => Pages\EditRssSource::route('/{record}/edit'),
        ];
    }
}
