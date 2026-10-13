<?php

namespace App\Filament\Pages;

use App\Models\ThemeDesignRevision;
use App\Models\ThemeSetting;
use App\Services\ThemeComponentDesign;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ComponentDesignEditor extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.component-design-editor';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected static ?string $navigationLabel = 'Bilesen Tasarimi';

    protected static ?string $title = 'Gorsel Bilesen Tasarimi';

    public array $state = [];

    public string $previewDevice = 'desktop';

    public string $previewComponent = 'post_card';

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function getFormStatePath(): string
    {
        return 'state';
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Header ve ust menu')
                ->description('Mevcut header ve navigasyon korunur; burada yalnizca gorunumu ayarlanir.')
                ->schema([
                    Toggle::make('component_styles.header.visible')
                        ->label('Header gorunsun')
                        ->default(true)
                        ->live(),
                    ColorPicker::make('component_styles.header.background')
                        ->label('Arka plan')
                        ->live(),
                    ColorPicker::make('component_styles.header.text_color')
                        ->label('Yazi rengi')
                        ->live(),
                    ColorPicker::make('component_styles.header.border_color')
                        ->label('Alt kenarlik rengi')
                        ->live(),
                    TextInput::make('component_styles.header.height')
                        ->label('Yukseklik (px)')
                        ->numeric()
                        ->minValue(48)
                        ->maxValue(112)
                        ->live(),
                    TextInput::make('component_styles.header.padding_x')
                        ->label('Yatay ic bosluk (px)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(48)
                        ->live(),
                ])
                ->columns(2),

            Section::make('Ana sayfa yerlesimi')
                ->description('Ana akis genisligini ve kenar sutunlari kontrol eder.')
                ->collapsible()
                ->schema([
                    TextInput::make('component_styles.home.max_width')
                        ->label('Maksimum genislik (px)')
                        ->numeric()
                        ->minValue(960)
                        ->maxValue(1920)
                        ->live(),
                    TextInput::make('component_styles.home.gap')
                        ->label('Sutunlar arasi bosluk (px)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(48)
                        ->live(),
                    Toggle::make('component_styles.home.show_sidebars')
                        ->label('Sol ve sag sutunlari goster')
                        ->default(true)
                        ->live(),
                ])
                ->columns(2),

            Section::make('Post-card tasarimi')
                ->description('Akis ve liste kartlarinin gorunumunu degistirir; kartin veri ve aksiyonlarini korur.')
                ->collapsible()
                ->schema([
                    ColorPicker::make('component_styles.post_card.background')->label('Kart arka plani')->live(),
                    ColorPicker::make('component_styles.post_card.text_color')->label('Yazi rengi')->live(),
                    ColorPicker::make('component_styles.post_card.border_color')->label('Kenarlik rengi')->live(),
                    TextInput::make('component_styles.post_card.radius')->label('Kose yuvarlakligi (px)')->numeric()->minValue(0)->maxValue(32)->live(),
                    TextInput::make('component_styles.post_card.padding')->label('Kart ic boslugu (px)')->numeric()->minValue(0)->maxValue(40)->live(),
                    TextInput::make('component_styles.post_card.title_size')->label('Baslik boyutu (px)')->numeric()->minValue(14)->maxValue(32)->live(),
                    TextInput::make('component_styles.post_card.body_size')->label('Metin boyutu (px)')->numeric()->minValue(12)->maxValue(20)->live(),
                ])
                ->columns(2),

            Section::make('Post-show tasarimi')
                ->description('Gonderi ayrinti sayfasinin ana icerik kartini ve basligini duzenler.')
                ->collapsible()
                ->schema([
                    ColorPicker::make('component_styles.post_show.background')->label('Icerik arka plani')->live(),
                    ColorPicker::make('component_styles.post_show.text_color')->label('Metin rengi')->live(),
                    ColorPicker::make('component_styles.post_show.border_color')->label('Kenarlik rengi')->live(),
                    TextInput::make('component_styles.post_show.radius')->label('Kose yuvarlakligi (px)')->numeric()->minValue(0)->maxValue(32)->live(),
                    TextInput::make('component_styles.post_show.padding')->label('Icer bosluk (px)')->numeric()->minValue(0)->maxValue(48)->live(),
                    TextInput::make('component_styles.post_show.max_width')->label('Maksimum icerik genisligi (px)')->numeric()->minValue(640)->maxValue(1200)->live(),
                    TextInput::make('component_styles.post_show.title_size')->label('Baslik boyutu (px)')->numeric()->minValue(18)->maxValue(48)->live(),
                ])
                ->columns(2),

            Section::make('Yorumlar')
                ->description('Yorum paneli ve yorum kartlarinin renk, kenarlik ve bosluk ayarlari.')
                ->collapsible()
                ->schema([
                    ColorPicker::make('component_styles.comments.background')->label('Arka plan')->live(),
                    ColorPicker::make('component_styles.comments.text_color')->label('Yazi rengi')->live(),
                    ColorPicker::make('component_styles.comments.border_color')->label('Kenarlik rengi')->live(),
                    TextInput::make('component_styles.comments.radius')->label('Kose yuvarlakligi (px)')->numeric()->minValue(0)->maxValue(28)->live(),
                    TextInput::make('component_styles.comments.padding')->label('Panel ic boslugu (px)')->numeric()->minValue(4)->maxValue(32)->live(),
                ])
                ->columns(2),

            Section::make('Footer olusturucu')
                ->description('Istege bagli bir alt bilgi alani olusturur. Varsayilan olarak kapali kalir.')
                ->collapsible()
                ->schema([
                    Toggle::make('component_styles.footer.visible')
                        ->label('Footer alanini etkinlestir')
                        ->default(false)
                        ->live(),
                    ColorPicker::make('component_styles.footer.background')->label('Arka plan')->live(),
                    ColorPicker::make('component_styles.footer.text_color')->label('Yazi rengi')->live(),
                    ColorPicker::make('component_styles.footer.border_color')->label('Ust kenarlik')->live(),
                    TextInput::make('component_styles.footer.padding')->label('Ic bosluk (px)')->numeric()->minValue(8)->maxValue(64)->live(),
                    Textarea::make('component_styles.footer.text')
                        ->label('Footer metni')
                        ->rows(2)
                        ->maxLength(180)
                        ->live(),
                    TextInput::make('component_styles.footer.link_label')
                        ->label('Baglanti metni')
                        ->maxLength(100)
                        ->live(),
                    TextInput::make('component_styles.footer.link_url')
                        ->label('Baglanti adresi')
                        ->placeholder('/iletisim veya https://...')
                        ->helperText('Guvenlik icin yalnizca https://, /sayfa veya #bolum adreslerine izin verilir.')
                        ->maxLength(500)
                        ->live(),
                ])
                ->columns(2),
        ];
    }

    public function save(): void
    {
        $settings = ThemeSetting::current();
        $data = $this->form->getState();
        $next = ThemeComponentDesign::normalize(data_get($data, 'component_styles', []));
        $current = ThemeComponentDesign::normalize($settings->component_styles ?? []);

        if ($next === $current) {
            Notification::make()
                ->title('Kaydedilecek yeni bir tasarim degisikligi yok')
                ->info()
                ->send();

            return;
        }

        $this->recordRevision($settings, 'Kayit oncesi otomatik yedek');
        $settings->update(['component_styles' => $next]);
        Cache::forget('theme-appearance-css');
        $this->fillForm();

        Notification::make()
            ->title('Bilesen tasarimi kaydedildi')
            ->body('Degisiklikler public sitedeki header, ana akis, post-card, post-show ve yorum alanlarina uygulandi.')
            ->success()
            ->send();
    }

    public function applyPreset(string $preset): void
    {
        $styles = ThemeComponentDesign::defaults();

        if ($preset === 'soft') {
            $styles['home']['gap'] = 20;
            $styles['post_card']['background'] = '#f8fafc';
            $styles['post_card']['border_color'] = '#e2e8f0';
            $styles['post_card']['radius'] = 16;
            $styles['post_card']['padding'] = 20;
            $styles['post_show']['background'] = '#ffffff';
            $styles['post_show']['border_color'] = '#e2e8f0';
            $styles['post_show']['radius'] = 16;
            $styles['comments']['background'] = '#f8fafc';
            $styles['comments']['border_color'] = '#e2e8f0';
            $styles['comments']['radius'] = 14;
        } elseif ($preset === 'blue') {
            $styles['header']['border_color'] = '#bfdbfe';
            $styles['home']['gap'] = 18;
            $styles['post_card']['border_color'] = '#bfdbfe';
            $styles['post_show']['border_color'] = '#bfdbfe';
            $styles['comments']['border_color'] = '#bfdbfe';
            $styles['footer']['border_color'] = '#bfdbfe';
        }

        $this->previewComponent = 'post_card';
        $this->form->fill(['component_styles' => ThemeComponentDesign::normalize($styles)]);

        Notification::make()
            ->title('Sablon onizlemeye uygulandi')
            ->body('Bu degisiklikler Kaydet dugmesine basana kadar siteye yayinlanmaz.')
            ->success()
            ->send();
    }

    public function resetToDefaults(): void
    {
        $this->form->fill([
            'component_styles' => ThemeComponentDesign::defaults(),
        ]);

        Notification::make()
            ->title('Varsayilan sablon onizlemeye yuklendi')
            ->body('Siteye uygulanmasi icin Tasarimi kaydet ve yayinla dugmesine bas.')
            ->success()
            ->send();
    }

    public function restoreRevision(int $revisionId): void
    {
        $settings = ThemeSetting::current();
        $revision = ThemeDesignRevision::query()
            ->where('theme_setting_id', $settings->id)
            ->find($revisionId);

        if (!$revision) {
            Notification::make()->title('Tasarim surumu bulunamadi')->danger()->send();

            return;
        }

        $currentRaw = $settings->component_styles;
        $targetRaw = is_array($revision->component_styles) ? $revision->component_styles : [];
        $currentHasDesign = is_array($currentRaw) && $currentRaw !== [];
        $targetHasDesign = $targetRaw !== [];
        $current = ThemeComponentDesign::normalize($currentRaw ?? []);
        $next = ThemeComponentDesign::normalize($targetRaw);

        if ($targetHasDesign && (!$currentHasDesign || $current !== $next)) {
            $this->recordRevision($settings, 'Geri alma oncesi otomatik yedek');
            $settings->update(['component_styles' => $next]);
        } elseif (!$targetHasDesign && $currentHasDesign) {
            $this->recordRevision($settings, 'Geri alma oncesi otomatik yedek');
            $settings->update(['component_styles' => null]);
        }

        $this->fillForm();

        Notification::make()
            ->title('Tasarim surumu geri yuklendi')
            ->success()
            ->send();
    }

    public function revisions()
    {
        $settings = ThemeSetting::current();

        return ThemeDesignRevision::query()
            ->with('user:id,name')
            ->where('theme_setting_id', $settings->id)
            ->latest('id')
            ->limit(10)
            ->get();
    }

    private function fillForm(): void
    {
        $settings = ThemeSetting::current();
        $this->form->fill([
            'component_styles' => ThemeComponentDesign::normalize($settings->component_styles ?? []),
        ]);
    }

    private function recordRevision(ThemeSetting $settings, string $label): void
    {
        ThemeDesignRevision::query()->create([
            'theme_setting_id' => $settings->id,
            'user_id' => Auth::id(),
            'label' => $label,
            'component_styles' => is_array($settings->component_styles) ? $settings->component_styles : [],
        ]);

        $keepIds = ThemeDesignRevision::query()
            ->where('theme_setting_id', $settings->id)
            ->latest('id')
            ->limit(30)
            ->pluck('id');

        ThemeDesignRevision::query()
            ->where('theme_setting_id', $settings->id)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }
}
