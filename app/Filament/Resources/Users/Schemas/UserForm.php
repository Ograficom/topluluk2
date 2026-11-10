<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
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
                Toggle::make('is_company')
                    ->label('Şirket hesabı')
                    ->helperText('Bu hesabı bir şirket veya kurum temsil ediyorsa etkinleştirin.'),
                Toggle::make('is_company_verified')
                    ->label('Altın şirket doğrulama rozeti')
                    ->helperText('Yalnızca şirket hesabı etkin olan profillerde gösterilir.'),
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
                \\Filament\\Schemas\\Components\\Section::make('Herkese açık profil SEO ayarları')
                    ->description('Bu kullanıcının herkese açık profil sayfasına özel SEO bilgileri. Hesap ayarları ve özel alanlar indekslenmez.')
                    ->schema([
                        TextInput::make('profile_seo_title')->label('Google sonuç başlığı')->maxLength(255)->helperText('Boşsa kullanıcının görünen adı kullanılır.'),
                        \\Filament\\Forms\\Components\\Textarea::make('profile_seo_description')->label('Meta açıklaması')->rows(3)->maxLength(500)->columnSpanFull(),
                        \\Filament\\Forms\\Components\\TextInput::make('profile_seo_keywords')->label('Anahtar kelimeler')->maxLength(2000)->columnSpanFull(),
                        TextInput::make('profile_canonical_url')->label('Canonical URL')->url()->maxLength(2048)->helperText('Özel bir canonical adres gerekmiyorsa boş bırakın.')->columnSpanFull(),
                        TextInput::make('profile_og_image')->label('Sosyal paylaşım görseli URL')->url()->maxLength(2048)->helperText('Paylaşım kartı içindir. Boşsa profil avatarı kullanılır.')->columnSpanFull(),
                        Toggle::make('profile_robots_index')->label('Profili indekslet')->default(true),
                    ])->columns(2),
            ]);
    }
}
