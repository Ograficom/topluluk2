<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Rules\UsernameFormat;
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
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->label('Kullanıcı adı')
                    ->required()
                    ->maxLength(30)
                    ->rules([new UsernameFormat])
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label('E-posta')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('Parola')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->afterStateHydrated(fn (TextInput $component) => $component->state(null))
                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Parolayı değiştirmeyecekseniz boş bırakın.' : null),
                Toggle::make('is_admin')
                    ->label('Yönetici')
                    ->required()
                    ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false),
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
