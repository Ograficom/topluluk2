<?php

namespace App\Filament\Resources\PostResource\RelationManagers;

use Filament\Actions as FilamentActions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Yorumlar';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('author_name')
                    ->label('Ad')
                    ->maxLength(255),
                Forms\Components\TextInput::make('author_email')
                    ->label('E-posta')
                    ->email()
                    ->maxLength(255),
                Forms\Components\Textarea::make('content')
                    ->label('Yorum')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_approved')
                    ->label('Onayli')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('author_name')
            ->columns([
                Tables\Columns\TextColumn::make('author_name')
                    ->label('Ad')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('author_email')
                    ->label('E-posta')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->label('Yorum')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Onayli')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Olusturulma')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Onayli mi?'),
            ])
            ->headerActions([
                FilamentActions\CreateAction::make(),
            ])
            ->actions([
                FilamentActions\EditAction::make(),
                FilamentActions\DeleteAction::make(),
            ])
            ->bulkActions([
                FilamentActions\DeleteBulkAction::make(),
            ]);
    }
}





