<?php
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages\CreateFaq;
use App\Filament\Resources\FaqResource\Pages\EditFaq;
use App\Filament\Resources\FaqResource\Pages\ListFaqs;
use App\Models\Faq;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Icerik';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static bool $isGloballySearchable = true;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('question')
                ->label('Soru')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Textarea::make('answer')
                ->label('Cevap')
                ->required()
                ->rows(4)
                ->columnSpanFull(),
            Grid::make(2)->schema([
                TextInput::make('sort_order')
                    ->label('Siralama')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')
                    ->label('Soru')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('answer')
                    ->label('Cevap')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label('Sira')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Guncellendi')
                    ->dateTime()
                    ->since(),
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
            'index' => ListFaqs::route('/'),
            'create' => CreateFaq::route('/create'),
            'edit' => EditFaq::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['question', 'answer'];
    }
}
