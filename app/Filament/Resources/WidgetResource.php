<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WidgetResource\Pages;
use App\Models\Widget;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WidgetResource extends Resource
{
    protected static ?string $model = Widget::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?int $navigationSort = 9;

    public static function getNavigationGroup(): ?string
    {
        return __('Main');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Widgets');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Attention!'))
                            ->description('All widgets are cached for 30 minutes except for the HTML block and the latest comments. After making changes you need to clear the cache!')
                            ->icon('heroicon-o-rectangle-group')
                            ->schema([
                                Select::make('name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->options([
                                        'top-authors' => __('Top authors'),
                                        'popular-tags' => __('Popular tags'),
                                        'latest-comments' => __('Latest comments'),
                                        'block-html' => __('Block HTML'),
                                    ])
                                    ->disabledOn('edit')
                                    ->selectablePlaceholder(false)
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(fn (Select $component) => $component
                                        ->getContainer()
                                        ->getComponent('dynamicTypeFields')
                                        ->getChildComponentContainer()
                                        ->fill()),
                                Forms\Components\Group::make()->schema([
                                    TextInput::make('order')
                                        ->label(__('Order'))
                                        ->numeric()
                                        ->required()
                                        ->minValue(1)
                                        ->maxValue(5),
                                    Select::make('position')
                                        ->label(__('Position'))
                                        ->required()
                                        ->options([
                                            'static' => __('Static'),
                                            'sticky' => __('Sticky'),
                                        ])
                                        ->selectablePlaceholder(false)
                                        ->native(false),
                                ])->columns(2),
                                Forms\Components\Fieldset::make(__('Settings'))
                                    ->schema([
                                        Grid::make(1)
                                            ->schema(fn (Get $get): array => match ($get('name')) {
                                                'top-authors' => [
                                                    TextInput::make('days')
                                                        ->label(__('Popular in last days'))
                                                        ->required()
                                                        ->numeric()
                                                        ->minValue(7)
                                                        ->maxValue(365),
                                                    TextInput::make('quantity')
                                                        ->label(__('Quantity'))
                                                        ->required()
                                                        ->numeric()
                                                        ->minValue(5)
                                                        ->maxValue(10),
                                                ],
                                                'popular-tags' => [
                                                    TextInput::make('quantity')
                                                        ->label(__('Quantity'))
                                                        ->required()
                                                        ->numeric()
                                                        ->minValue(5)
                                                        ->maxValue(20),
                                                ],
                                                'latest-comments' => [
                                                    TextInput::make('quantity')
                                                        ->label(__('Quantity'))
                                                        ->required()
                                                        ->numeric()
                                                        ->minValue(5)
                                                        ->maxValue(20),
                                                ],
                                                'block-html' => [
                                                    Textarea::make('html')
                                                        ->label(__('HTML'))
                                                        ->required()
                                                        ->rows(5)
                                                        ->cols(20)
                                                        ->autosize(),
                                                ],
                                                default => [],
                                            })
                                            ->statePath('settings')
                                            ->key('dynamicTypeFields'),
                                    ])->hidden(fn (Get $get): bool => ! $get('name')),
                            ]),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('order')
                    ->label(__('Order'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('position')
                    ->label(__('Position'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->label(false)->size('md')->tooltip(__('Delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWidgets::route('/'),
            'create' => Pages\CreateWidget::route('/create'),
            'edit' => Pages\EditWidget::route('/{record}/edit'),
        ];
    }
}
