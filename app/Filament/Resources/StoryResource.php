<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoryResource\Pages;
use App\Filament\Resources\StoryResource\RelationManagers;
use App\Models\Story;
use Archilex\ToggleIconColumn\Columns\ToggleIconColumn;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class StoryResource extends Resource
{
    protected static ?string $model = Story::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('Main');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Stories');
    }

    public static function getNavigationBadge(): ?string
    {
        return (static::getModel()::whereNotNull('published_at')->whereNull('approved_at')->count() > 0) ? static::getModel()::whereNotNull('published_at')->whereNull('approved_at')->count() : false;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::whereNotNull('published_at')->whereNull('approved_at')->count() > 10 ? 'danger' : 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('media')
                    ->label(__('Media'))
                    ->width(50)
                    ->height(50)
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->defaultImageUrl(fn ($record): string => $record->getFilamentMediaUrl()),
                Tables\Columns\TextColumn::make('author.username')
                    ->label(__('Author'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->formatStateUsing(function ($state) {
                        $truncatedValue = Str::limit($state, 50);

                        return new HtmlString("<span title='{$state}'>{$truncatedValue}</span>");
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('Status'))
                    ->badge()
                    ->getStateUsing(fn (Story $record): string => $record->published_at?->isPast() ? __('Published') : __('Draft'))
                    ->colors([
                        'success' => __('Published'),
                    ])
                    ->sortable(),
                ViewColumn::make('link')
                    ->label(__('URL'))
                    ->view('filament.tables.columns.story-link'),
                ToggleIconColumn::make('featured')
                    ->label(__('Featured'))
                    ->translateLabel()
                    ->alignCenter()
                    ->onIcon('heroicon-s-star')
                    ->offIcon('heroicon-o-star')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('approved_at')
                    ->label(__('Approved'))
                    ->updateStateUsing(function ($record, $state) {
                        $record->update(['approved_at' => $state ? now() : null]);
                    })->alignCenter(),
            ])->defaultSort('id', 'desc')->deferLoading(false)
            ->filters([
                TernaryFilter::make('status')
                    ->label(__('Status'))
                    ->placeholder(__('All'))
                    ->trueLabel(__('Published'))
                    ->falseLabel(__('Draft'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('published_at'),
                        false: fn (Builder $query) => $query->whereNull('published_at'),
                        blank: fn (Builder $query) => $query,
                    ),
                Filter::make('published_from')
                    ->form([
                        DatePicker::make('published_from')
                            ->label(__('Published from'))
                            ->placeholder(fn ($state): string => 'Dec 18, '.now()->subYear()->format('Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['published_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('published_at', '>=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['published_from'] ?? null) {
                            $indicators['published_from'] = 'Published from '.Carbon::parse($data['published_from'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
                Filter::make('published_until')
                    ->form([
                        DatePicker::make('published_until')
                            ->label(__('Published until'))
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['published_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('published_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['published_until'] ?? null) {
                            $indicators['published_until'] = 'Published until '.Carbon::parse($data['published_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
                Tables\Filters\TrashedFilter::make(),
            ], layout: FiltersLayout::AboveContentCollapsible)->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()->before(
                    function ($record, Tables\Actions\DeleteAction $action) {
                        if (env('DEMO_MODE') == true) {
                            Notification::make()
                                ->title('Warning!')
                                ->body("You can't delete because DEMO_MODE is enabled.")
                                ->status('warning')
                                ->send();
                            $action->cancel();
                        }
                    }
                )
                    ->label(false)
                    ->size('md')
                    ->tooltip(__('Delete'))
                    ->action(function (Story $record): void {
                        $record->comments()->each(function ($comment) {
                            $comment->favorites()->where('favoriteable_id', $comment->id)->each(function ($favorite) {
                                $favorite->delete();
                            });

                            $comment->likes()->where('likeable_type', 'App\Models\Comment')->where('likeable_id', $comment->id)->each(function ($like) {
                                $like->delete();
                            });

                            $comment->delete();
                        });
                        $record->favorites()->where('favoriteable_id', $record->id)->each(function ($favorite) {
                            $favorite->delete();
                        });

                        $record->likes()->where('likeable_type', 'App\Models\Story')->where('likeable_id', $record->id)->each(function ($like) {
                            $like->delete();
                        });

                        if ($record->poll()->exists()) {
                            $record->poll()->delete();
                        }

                        $record->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->before(
                        function ($records, Tables\Actions\DeleteBulkAction $action) {
                            if (env('DEMO_MODE') == true) {
                                Notification::make()
                                    ->title('Warning!')
                                    ->body("You can't delete because DEMO_MODE is enabled.")
                                    ->status('warning')
                                    ->send();
                                $action->cancel();
                            }
                        }
                    ),
                    Tables\Actions\ForceDeleteBulkAction::make()->before(
                        function ($records, Tables\Actions\ForceDeleteBulkAction $action) {
                            if (env('DEMO_MODE') == true) {
                                Notification::make()
                                    ->title('Warning!')
                                    ->body("You can't delete because DEMO_MODE is enabled.")
                                    ->status('warning')
                                    ->send();
                                $action->cancel();
                            }
                        }
                    ),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\Split::make([
                            Components\Grid::make(2)
                                ->schema([
                                    Components\Group::make([
                                        Components\TextEntry::make('title')->label(__('Title')),
                                        Components\TextEntry::make('slug')->label(__('Slug')),
                                        Components\TextEntry::make('published_at')
                                            ->label(__('Published date'))
                                            ->hidden(fn (?Story $record): string => $record->published_at === null)
                                            ->badge()
                                            ->date()
                                            ->color('success'),
                                        Components\TextEntry::make('status')
                                            ->label(__('Status'))
                                            ->badge()
                                            ->getStateUsing(fn (Story $record): string => $record->published_at?->isPast() ? __('Published') : __('Draft'))
                                            ->colors([
                                                'success' => __('Published'),
                                            ]),
                                    ]),
                                    Components\Group::make([
                                        Components\TextEntry::make('author.name')->label(__('Author')),
                                        Components\TextEntry::make('community.name')->label(__('Community')),
                                        Components\TextEntry::make('tags.name')
                                            ->label(__('Tags'))
                                            ->badge(),
                                    ]),
                                ]),
                            ImageEntry::make('media')
                                ->label(__('Media'))
                                ->width(300)
                                ->height(200)
                                ->extraImgAttributes(['loading' => 'lazy'])
                                ->defaultImageUrl(fn ($record): string => $record->getFilamentMediaUrl())
                                ->grow(false),
                        ])->from('lg'),
                    ]),
                Components\Section::make(__('Story'))
                    ->schema([
                        Components\TextEntry::make('body_rendered')
                            ->prose()
                            ->html()
                            ->hiddenLabel(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStories::route('/'),
            'view' => Pages\ViewStory::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user', 'community']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'user.username', 'community.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Story $record */
        $details = [];

        if ($record->user) {
            $details[__('Author')] = $record->user->name ? $record->user->name : $record->user->username;
        }

        if ($record->community) {
            $details[__('Community')] = $record->community->name;
        }

        return $details;
    }
}
