<?php

namespace App\Filament\Pages;

use App\Models\MailSetting;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Throwable;

class MailSettings extends Page
{
    protected static ?string $navigationLabel = 'E-posta / SMTP';
    protected static ?string $title = 'E-posta gönderim ayarları';
    protected static string|\UnitEnum|null $navigationGroup = 'Site ayarları';
    protected static ?int $navigationSort = 90;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;
    protected string $view = 'filament.pages.mail-settings';

    public ?array $data = [];
    public ?string $testRecipient = null;

    public function mount(): void
    {
        $settings = MailSetting::current();

        $this->form->fill([
            'enabled' => $settings->enabled,
            'mailer' => $settings->mailer,
            'scheme' => $settings->scheme,
            'host' => $settings->host,
            'port' => $settings->port,
            'username' => $settings->username,
            'password' => null,
            'from_address' => $settings->from_address,
            'from_name' => $settings->from_name,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('E-posta gönderimini etkinleştir')
                    ->description('SMTP ayarlarını kaydettikten sonra OgraFi sistem e-postaları bu sunucu üzerinden gönderilir.')
                    ->icon(Heroicon::OutlinedCog6Tooth)
                    ->schema([
                        Toggle::make('enabled')
                            ->label('SMTP gönderimini etkinleştir')
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),
                    ]),

                Section::make('SMTP sunucu bilgileri')
                    ->description('Hostinger e-posta hizmetinizin bağlantı bilgilerini girin.')
                    ->icon(Heroicon::OutlinedGlobeAlt)
                    ->schema([
                        Select::make('mailer')
                            ->label('Gönderim yöntemi')
                            ->options(['smtp' => 'SMTP'])
                            ->default('smtp')
                            ->required(),
                        TextInput::make('host')
                            ->label('SMTP sunucusu')
                            ->placeholder('smtp.hostinger.com')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('port')
                            ->label('Port')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(65535)
                            ->required(),
                        Select::make('scheme')
                            ->label('Şifreleme')
                            ->options([
                                'smtps' => 'SSL (önerilen · 465)',
                                'smtp' => 'STARTTLS (587)',
                            ])
                            ->required()
                            ->live(),
                        TextInput::make('username')
                            ->label('SMTP kullanıcı adı')
                            ->placeholder('info@ografi.com')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('SMTP şifresi')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->dehydrated()
                            ->helperText('Güvenlik nedeniyle kayıtlı şifre burada gösterilmez. Değiştirmek için yeni şifre girin; boş bırakırsanız mevcut şifre korunur.')
                            ->maxLength(1000),
                    ])
                    ->columns(2),

                Section::make('Gönderici kimliği')
                    ->description('Alıcıların gelen kutusunda göreceği gönderen bilgileri.')
                    ->icon(Heroicon::OutlinedAtSymbol)
                    ->schema([
                        TextInput::make('from_address')
                            ->label('Gönderici e-posta adresi')
                            ->email()
                            ->required()
                            ->placeholder('info@ografi.com')
                            ->maxLength(255),
                        TextInput::make('from_name')
                            ->label('Gönderici adı')
                            ->required()
                            ->placeholder('OgraFi')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = MailSetting::current();

        $payload = [
            'enabled' => (bool) ($data['enabled'] ?? false),
            'mailer' => $data['mailer'],
            'scheme' => $data['scheme'],
            'host' => trim($data['host']),
            'port' => (int) $data['port'],
            'username' => trim($data['username']),
            'from_address' => trim($data['from_address']),
            'from_name' => trim($data['from_name']),
        ];

        if (filled($data['password'] ?? null)) {
            $payload['password'] = $data['password'];
        } elseif (! filled($settings->password)) {
            Notification::make()
                ->title('SMTP şifresi gerekli')
                ->body('İlk kurulumu tamamlamak için SMTP şifresini girin.')
                ->danger()
                ->send();

            return;
        }

        $settings->update($payload);
        $this->applyMailConfig($settings->fresh());

        Notification::make()
            ->title('E-posta ayarları kaydedildi')
            ->body($settings->enabled ? 'SMTP gönderimi etkin.' : 'Ayarlar kaydedildi; SMTP gönderimi şu anda kapalı.')
            ->success()
            ->send();

        $this->data['password'] = null;
    }

    public function sendTest(): void
    {
        $recipient = filter_var($this->testRecipient, FILTER_VALIDATE_EMAIL);

        if (! $recipient) {
            Notification::make()->title('Geçerli bir test e-posta adresi girin.')->warning()->send();
            return;
        }

        $settings = MailSetting::current();

        if (! $settings->exists || ! filled($settings->host) || ! filled($settings->username) || ! filled($settings->password)) {
            Notification::make()->title('SMTP ayarları eksik')->body('Önce sunucu, kullanıcı adı ve şifre bilgilerini kaydedin.')->danger()->send();
            return;
        }

        try {
            $this->applyMailConfig($settings);
            config(['mail.default' => 'smtp']);
            Mail::purge('smtp');

            Mail::raw(
                'OgraFi SMTP bağlantı testi başarılı. E-posta gönderim ayarlarınız çalışıyor.',
                function ($message) use ($recipient, $settings): void {
                    $message->to($recipient)
                        ->subject('OgraFi - SMTP bağlantı testi')
                        ->from($settings->from_address, $settings->from_name);
                },
            );

            Notification::make()
                ->title('Test e-postası gönderildi')
                ->body('Alıcı gelen kutusunu ve spam klasörünü kontrol edebilir.')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()
                ->title('Test e-postası gönderilemedi')
                ->body('SMTP bağlantısı başarısız oldu. Sunucu, port, şifreleme ve e-posta şifresini kontrol edin. Ayrıntı uygulama günlüklerine kaydedildi.')
                ->danger()
                ->persistent()
                ->send();
        } finally {
            Mail::purge('smtp');
        }
    }

    private function applyMailConfig(MailSetting $settings): void
    {
        config([
            'mail.default' => $settings->enabled ? $settings->mailer : 'log',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.scheme' => $settings->scheme,
            'mail.mailers.smtp.host' => $settings->host,
            'mail.mailers.smtp.port' => $settings->port,
            'mail.mailers.smtp.username' => $settings->username,
            'mail.mailers.smtp.password' => $settings->password,
            'mail.from.address' => $settings->from_address,
            'mail.from.name' => $settings->from_name,
        ]);
    }
}
