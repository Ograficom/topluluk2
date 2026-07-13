<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Models\Comment;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Blog';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static bool $isGloballySearchable = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('post_id')
                ->label('Gonderi')
                ->relationship('post', 'title')
                ->searchable()
                ->required()
                ->preload(),
            Select::make('user_id')
                ->label('Kullanici')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->nullable(),
            TextInput::make('author_name')
                ->label('Ad')
                ->maxLength(255)
                ->requiredWithout('user_id'),
            TextInput::make('author_email')
                ->label('E-posta')
                ->email()
                ->maxLength(255)
                ->requiredWithout('user_id'),
            Textarea::make('content')
                ->label('Yorum')
                ->required()
                ->columnSpanFull(),
            Toggle::make('is_approved')
                ->label('Onayli')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post.title')
                    ->label('Gonderi')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('author_name')
                    ->label('Ad')
                    ->searchable(),
                TextColumn::make('author_email')
                    ->label('E-posta')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('content')
                    ->label('Yorum')
                    ->limit(50),
                IconColumn::make('is_approved')
                    ->label('Onayli')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Olusturulma')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('post')->relationship('post', 'title'),
                TernaryFilter::make('is_approved')
                    ->label('Onay'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['author_name', 'author_email', 'content'];
    }
}







