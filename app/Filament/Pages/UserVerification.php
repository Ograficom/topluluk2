<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class UserVerification extends Page
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Kullanici Onaylari';

    protected static string | \UnitEnum | null $navigationGroup = 'Kullanicilar';

    protected string $view = 'filament.pages.user-verification';

    public array $data = [
        'userId' => null,
        'is_verified' => false,
        'verification_badge' => null,
        'badge_file' => null,
    ];

    public function mount(): void
    {
        $first = User::query()->orderBy('created_at')->value('id');
        $this->data['userId'] = $first;
        $this->syncUserState($first);
        $this->form->fill($this->data);
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(2)->schema([
                Forms\Components\Select::make('userId')
                    ->label('Kullanici')
                    ->options(User::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $this->syncUserState($state);
                        $set('is_verified', $this->data['is_verified']);
                        $set('verification_badge', $this->data['verification_badge']);
                        $set('badge_file', null);
                    }),
                Forms\Components\Toggle::make('is_verified')
                    ->label('Onayli hesap'),
            ]),
            Forms\Components\Select::make('verification_badge')
                ->label('Rozet')
                ->options([
                    'none' => 'Yok',
                    'blue-check' => 'Mavi tik',
                    'gold-check' => 'Altin tik',
                    'gray-check' => 'Gri tik',
                    'custom' => 'Ozel SVG',
                ]),
            Forms\Components\FileUpload::make('badge_file')
                ->label('Ozel rozet SVG (yukleme)')
                ->acceptedFileTypes(['image/svg+xml'])
                ->directory('badges')
                ->visibility('public')
                ->helperText('Sadece rozet ayari esnasinda gosterilir.')
                ->hidden(fn (callable $get) => $get('verification_badge') !== 'custom'),
        ];
    }

    public function submit(): void
    {
        $this->validate([
            'data.userId' => ['required', 'exists:users,id'],
            'data.is_verified' => ['boolean'],
            'data.verification_badge' => ['nullable', 'in:none,blue-check,gold-check,gray-check,custom'],
            'data.badge_file' => ['nullable'],
        ]);

        $user = User::findOrFail($this->data['userId']);

        $user->update([
            'is_verified' => (bool) $this->data['is_verified'],
            'verification_badge' => $this->data['verification_badge'],
            'verification_badge_svg' => $this->data['badge_file'],
        ]);

        Notification::make()
            ->title('Onay bilgisi guncellendi')
            ->success()
            ->send();
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    protected function syncUserState(?int $userId): void
    {
        $user = $userId ? User::find($userId) : null;

        $this->data['is_verified'] = $user?->is_verified ?? false;
        $this->data['verification_badge'] = $user?->verification_badge;
        $this->data['badge_file'] = null;
    }
}













