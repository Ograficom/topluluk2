<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\NewYearMode;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class NewYearManagement extends Page
{
    protected static ?string $slug = 'new-year-management';
    protected static string|\UnitEnum|null $navigationGroup = 'Ayarlar';
    protected static ?string $navigationLabel = 'Yeni yıl modu';
    protected static ?string $title = 'Yeni yıl modu';
    protected static ?int $navigationSort = 20;
    protected string $view = 'filament.pages.new-year-management';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(NewYearMode::settings(SiteSetting::current()));
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Yeni yıl modu')
                ->description('Etkinleştirdiğinizde seçilen tarihlerde web ve mobil görünümde çalışır. Saat dilimi: Türkiye.')
                ->schema([
                    Toggle::make('enabled')->label('Yeni yıl modu aktif'),
                    TextInput::make('target_year')->label('Kutlanacak yıl')->required()->integer()->minValue(2027)->maxValue(2100),
                    DatePicker::make('start_date')->label('Başlangıç tarihi')->required(),
                    DatePicker::make('end_date')->label('Bitiş tarihi (dahil)')->required()->afterOrEqual('start_date'),
                ])->columns(2),
            Section::make('Görsel efektler')
                ->schema([
                    Toggle::make('snow')->label('Kar yağışı'),
                    Toggle::make('lights')->label('Header üzerinde yılbaşı ışıkları'),
                    Toggle::make('logo')->label('Logo üzerinde yılbaşı şapkası'),
                    Toggle::make('countdown')->label('Ana sayfada geri sayım ve kutlama kartı'),
                    Toggle::make('confetti')->label('1 Ocak kutlama konfetisi'),
                ])->columns(2),
        ])->statePath('data');
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->is_admin, 403);
        $data = $this->form->getState();

        if (! DatabaseSchema::hasColumn('site_settings', 'new_year_settings')) {
            Notification::make()->title('Önce yeni yıl modu veritabanı migrasyonu çalıştırılmalıdır.')->danger()->send();
            return;
        }

        $site = SiteSetting::current() ?? new SiteSetting();
        $site->id = 1;
        $site->setAttribute('new_year_settings', json_encode(array_intersect_key($data, NewYearMode::defaults()), JSON_THROW_ON_ERROR));
        $site->save();

        Notification::make()->title('Yeni yıl modu ayarları kaydedildi.')->success()->send();
    }
}
