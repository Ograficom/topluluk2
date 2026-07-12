<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdsResource\Pages;
use App\Models\Ad;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdsResource extends Resource
{
    protected static ?string $model = Ad::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): ?string
    {
        return __('Main');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Advertisements');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make($form->model->position)
                    ->label($form->model->position)
                    ->schema([
                        Toggle::make('status')
                            ->label(__('Status'))
                            ->onIcon('heroicon-m-bolt')
                            ->offIcon('heroicon-m-bolt-slash')
                            ->live(onBlur: true),
                        Forms\Components\Textarea::make('code')
                            ->label(__('Code'))
                            ->autosize()
                            ->visible(fn (Get $get) => $get('status') === true),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('position')
                    ->label(__('Position'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('alias')
                    ->label(__('Alias'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->getStateUsing(fn (Ad $record): string => $record->status ? __('Active') : __('Disabled'))
                    ->colors([
                        'success' => __('Active'),
                        'danger' => __('Disabled'),
                    ])
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->beforeFormFilled(
                    function ($record, Tables\Actions\EditAction $action) {
                        if (env('DEMO_MODE') == true) {
                            Notification::make()
                                ->title('Warning!')
                                ->body("You can't edit this because DEMO_MODE is enabled.")
                                ->status('warning')
                                ->send();
                            $action->cancel();
                        }
                    }
                ),
            ])
            ->bulkActions([
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
            'index' => Pages\ListAds::route('/'),
            // 'edit' => Pages\EditAds::route('/{record}/edit')
        ];
    }
}
