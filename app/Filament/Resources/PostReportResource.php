<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostReportResource\Pages;
use App\Models\PostReport;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostReportResource extends Resource
{
    protected static ?string $model = PostReport::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-flag';
    protected static string | \UnitEnum | null $navigationGroup = 'Moderasyon';
    protected static ?string $navigationLabel = 'Gonderi Sikayetleri';
    protected static ?string $pluralLabel = 'Gonderi Sikayetleri';
    protected static ?string $modelLabel = 'Gonderi Sikayeti';
    protected static ?int $navigationSort = 91;

    protected static ?string $recordTitleAttribute = 'topic';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                Select::make('reporter_id')
                    ->label('Sikayet eden')
                    ->relationship('reporter', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('post_id')
                    ->label('Gonderi')
                    ->relationship('post', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]),
            Grid::make(2)->schema([
                Select::make('topic')
                    ->label('Sikayet turu')
                    ->options(PostReport::TOPICS)
                    ->required(),
                Select::make('status')
                    ->label('Durum')
                    ->options(PostReport::STATUSES)
                    ->default('pending')
                    ->required(),
            ]),
            Textarea::make('description')
                ->label('Mesaj')
                ->rows(5)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('reporter.name')
                    ->label('Sikayet eden')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('post.title')
                    ->label('Gonderi')
                    ->searchable()
                    ->limit(50)
                    ->sortable(),
                TextColumn::make('topic')
                    ->label('Tur')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Durum')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'in_review',
                        'success' => 'resolved',
                        'danger' => 'dismissed',
                    ])
                    ->formatStateUsing(fn (string $state): string => PostReport::STATUSES[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(PostReport::STATUSES),
                SelectFilter::make('topic')
                    ->label('Sikayet turu')
                    ->options(PostReport::TOPICS),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPostReports::route('/'),
            'edit' => Pages\EditPostReport::route('/{record}/edit'),
        ];
    }
}
