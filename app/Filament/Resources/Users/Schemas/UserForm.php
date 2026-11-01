<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Görünen ad')
                    ->required(),
                TextInput::make('username')
                    ->label('Kullanıcı adı')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label('E-posta')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->visible(fn (string $operation): bool => $operation === 'create'),
                Toggle::make('is_admin')
                    ->label('Yönetici')
                    ->required(),
                Select::make('verification_status')
                    ->label('Mavi tik durumu')
                    ->options([
                        'unverified' => 'Doğrulanmadı',
                        'pending' => 'İnceleme bekliyor',
                        'approved' => 'Doğrulandı',
                        'rejected' => 'Reddedildi',
                    ])
                    ->required(),
                TextInput::make('verification_reason')
                    ->label('Doğrulama başvurusu')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }
}
