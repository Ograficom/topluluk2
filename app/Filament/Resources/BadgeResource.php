<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BadgeResource\Pages;
use App\Models\Badge;
use App\Models\Level;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class BadgeResource extends Resource
{
    protected static ?string $model = Badge::class;

    protected static ?int $navigationSort = 12;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    public static function getNavigationGroup(): ?string
    {
        return __('Main');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Badges');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Name'))
                                    ->maxValue(250)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('alias', str_replace('-', '_', Str::slug($state))) : null),

                                Forms\Components\TextInput::make('alias')
                                    ->label(__('Alias'))
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->unique(Badge::class, 'alias', ignoreRecord: true),

                                Forms\Components\Textarea::make('description')
                                    ->label(__('Description'))
                                    ->autosize()
                                    ->maxLength(1500),

                                Select::make('advanced_options')
                                    ->label(__('Type (Optional)'))
                                    ->options([
                                        'level' => __('Level'),
                                        'membership_years' => __('Membership Years'),
                                    ])
                                    ->disabled(fn (?Badge $record) => $record !== $record->is_default)
                                    ->native(false)
                                    ->disabledOn('edit')
                                    ->selectablePlaceholder(false)
                                    ->live()
                                    ->dehydrated(false)
                                    ->afterStateUpdated(fn (Select $component) => $component
                                        ->getContainer()
                                        ->getComponent('dynamicTypeFields')
                                        ->getChildComponentContainer()
                                        ->fill()),

                                Grid::make(1)
                                    ->schema(fn (Get $get): array => match ($get('advanced_options')) {
                                        'membership_years' => [
                                            Forms\Components\TextInput::make('membership_years')
                                                ->numeric()
                                                ->minValue(1)
                                                ->required(),
                                        ],
                                        'level' => [
                                            Select::make('level_id')
                                                ->label(__('Select level'))
                                                ->options(Level::query()->orderBy('id')->pluck('name', 'id'))
                                                ->native(false)
                                                ->required(),
                                        ],
                                        default => [],
                                    })
                                    ->key('dynamicTypeFields'),
                            ])
                            ->columns(2),
                    ])->columnSpan(['lg' => fn (?Badge $record) => $record === null ? 2 : 2]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label(__('Image'))
                            ->image()
                            ->disk(getCurrentDisk())
                            ->directory('badges')
                            ->visibility('public')
                            ->required(),
                        Forms\Components\Placeholder::make('membership_years')
                            ->label(__('Membership Years'))
                            ->content(fn (Badge $record): ?string => $record->membership_years)
                            ->hidden(fn (Badge $record = null): bool => $record === null || $record->membership_years === null),

                        Forms\Components\Placeholder::make('level.name')
                            ->label(__('Level'))
                            ->content(fn (Badge $record): ?string => $record->level->name)
                            ->hidden(fn (Badge $record = null): bool => $record === null || $record->level_id === null),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->label(__('Badge'))
                    ->circular()
                    ->defaultImageUrl(fn ($record): string => $record->getImageUrl()),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->formatStateUsing(function ($state) {
                        $truncatedValue = Str::limit($state, 40);

                        return new HtmlString("<span title='{$state}'>{$truncatedValue}</span>");
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('Description'))
                    ->formatStateUsing(function ($state) {
                        $truncatedValue = Str::limit($state, 40);

                        return new HtmlString("<span title='{$state}'>{$truncatedValue}</span>");
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('level.name')
                    ->label(__('Level'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('membership_years')
                    ->label(__('Membership Years'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->sortable()
                    ->searchable(),
            ])->defaultSort('id', 'asc')
            ->filters([
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(false)->size('md')->tooltip(__('Edit')),
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
                    ->hidden(fn ($record) => $record->is_default == true),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBadges::route('/'),
            'create' => Pages\CreateBadge::route('/create'),
            'edit' => Pages\EditBadge::route('/{record}/edit'),
        ];
    }
}
