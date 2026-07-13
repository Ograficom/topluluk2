<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;

class RolePolicies extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Kullanicilar';

    protected static ?string $navigationLabel = 'Roller ve Kurallar';

    protected string $view = 'filament.pages.role-policies';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getTitle(): string
    {
        return 'Roller ve Kurallar';
    }

    public function getRoleCards(): array
    {
        return collect(User::roleOptions())
            ->map(function (string $label, string $role) {
                return [
                    'label' => $label,
                    'description' => User::roleDescriptions()[$role] ?? '',
                    'rules' => User::roleBaseRestrictionLabels($role),
                ];
            })
            ->values()
            ->all();
    }

    public function getExtraRestrictions(): array
    {
        return [
            'Mesaj engeli',
            'Gonderi engeli',
            'Kategori engeli',
            'Etiket engeli',
            'Yorum engeli',
            'Reaksiyon engeli',
        ];
    }
}
