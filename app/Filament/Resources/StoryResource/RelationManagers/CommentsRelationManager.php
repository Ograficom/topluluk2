<?php

namespace App\Filament\Resources\StoryResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $recordTitleAttribute = 'title';

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('Comments');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
            ])
            ->columns(1);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->schema([
                TextEntry::make('user.username')->label(__('User')),
                TextEntry::make('comment')->html()->label(__('Comment')),
                TextEntry::make('created_at')
                    ->label(__('Created'))->badge(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('comment')
                    ->label(__('Comments'))
                    ->formatStateUsing(function ($state) {
                        $truncatedValue = Str::limit($state, 100);

                        return new HtmlString("<span title='{$state}'>{$truncatedValue}</span>");
                    }),

                Tables\Columns\TextColumn::make('user.username')
                    ->label(__('User')),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([
            ])
            ->groupedBulkActions([
            ])->deferLoading(false);
    }
}
