<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LevelResource\Pages;
use App\Models\Level;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LevelResource extends Resource
{
    protected static ?string $model = Level::class;

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public static function getNavigationGroup(): ?string
    {
        return __('Main');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Levels');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Name'))
                    ->maxValue(100)
                    ->required(),

                Forms\Components\TextInput::make('points')
                    ->label(__('Points'))
                    ->required()
                    ->numeric()
                    ->minValue(100)
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('points')
                    ->label(__('Points'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->date(),
            ])->defaultSort('id', 'asc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modalWidth('md')->label(false)->size('md')->tooltip(__('Edit')),
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
                )->hidden(fn ($record) => $record->is_default == true),
            ])->bulkActions([
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
                    ]),
                ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLevels::route('/'),
        ];
    }
}
