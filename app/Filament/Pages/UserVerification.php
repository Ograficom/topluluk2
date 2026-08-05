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
                // A valid .svg file's detected content MIME type is inconsistent across
                // environments (image/svg+xml, image/svg, text/xml, application/xml all
                // occur for genuinely valid files - filamentphp/filament#14219), so every
                // realistic variant is accepted here instead of only the "correct" one -
                // otherwise a real SVG can silently fail this check and never upload.
                ->acceptedFileTypes(['image/svg+xml', 'image/svg', 'text/xml', 'application/xml'])
                ->directory('badges')
                ->visibility('public')
                ->helperText('Sadece rozet ayari esnasinda gosterilir. .svg dosyasi yukleyin.')
                ->hidden(fn (callable $get) => $get('verification_badge') !== 'custom'),
        ];
    }

    public function submit(): void
    {
        $this->validate([
            'data.userId' => ['required', 'exists:users,id'],
            'data.is_verified' => ['boolean'],
            'data.verification_badge' => ['nullable', 'in:none,blue-check,gold-check,gray-check,custom'],
        ]);

        // Reading $this->data['badge_file'] directly (as this used to) skips Filament's
        // upload pipeline: FileUpload only converts a freshly-picked file from a raw
        // Livewire temporary-upload object into a real "badges/xxx.svg" disk path when
        // the schema's state is dehydrated via getState(). Without that step, the raw
        // upload object gets json-encoded straight into the DB (e.g. `[{}]` or
        // `{"<uuid>":{}}`) - a broken path that can never resolve to a real image, which
        // is why a previously-selected blue/gold/custom badge could silently end up
        // rendering nothing (or, before that "gray" override existed, always gray).
        $state = $this->form->getState();

        $user = User::findOrFail($state['userId']);
        $badgeType = $state['verification_badge'] ?? null;
        $uploadedSvg = $state['badge_file'] ?? null;

        $user->update([
            'is_verified' => (bool) $state['is_verified'],
            'verification_badge' => $badgeType,
            'verification_badge_svg' => $badgeType === 'custom'
                // Keep the existing custom SVG if the admin re-saves without picking a
                // new file (e.g. just flipping "Onayli hesap"), instead of wiping it.
                ? ($uploadedSvg ?: $user->verification_badge_svg)
                : null,
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













