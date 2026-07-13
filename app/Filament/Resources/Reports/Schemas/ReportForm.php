<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(2)->schema([
                    Select::make('reporter_id')
                        ->label('Sikayet Eden')
                        ->relationship('reporter', 'name')
                        ->searchable()
                        ->required(),
                    Select::make('reported_user_id')
                        ->label('Sikayet Edilen')
                        ->relationship('reportedUser', 'name')
                        ->searchable()
                        ->required(),
                ]),
                Grid::make(2)->schema([
                    Select::make('topic')
                        ->label('Konu')
                        ->options([
                            'Taciz' => 'Taciz',
                            'Zorbalik' => 'Zorbalik',
                            'Dolandirici' => 'Dolandirici',
                            'Kimlik taklidi' => 'Kimlik taklidi',
                            'Casus veya supheli' => 'Casus veya supheli',
                            'Satici' => 'Satici',
                            'Istenmeyen' => 'Istenmeyen',
                            'Olasi aktivite' => 'Olasi aktivite',
                        ])
                        ->required(),
                    Select::make('status')
                        ->label('Durum')
                        ->options([
                            'pending' => 'Beklemede',
                            'in_review' => 'Incelemede',
                            'resolved' => 'Cozuldu',
                            'dismissed' => 'Kapandi',
                        ])
                        ->default('pending'),
                ]),
                Toggle::make('show_username')
                    ->label('Kullanici adi paylasilsin')
                    ->helperText('Sikayet edilen kiside kullanici adin gorunur.')
                    ->inline(false),
                Textarea::make('description')
                    ->label('Aciklama')
                    ->columnSpanFull()
                    ->rows(4),
            ]);
    }
}









