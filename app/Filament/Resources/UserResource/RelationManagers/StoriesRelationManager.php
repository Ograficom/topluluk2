<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class StoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'stories';

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('Stories');
    }

    public static function getPluralRecordLabel(): string
    {
        return __('stories');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->formatStateUsing(function ($state) {
                        $truncatedValue = Str::limit($state, 100);

                        return new HtmlString("<span title='{$state}'>{$truncatedValue}</span>");
                    }),
                Tables\Columns\TextColumn::make('published_at')->label(__('Published')),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([
            ])
            ->groupedBulkActions([
            ]);
    }
}
