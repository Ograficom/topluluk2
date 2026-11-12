<?php

namespace App\Filament\Pages;

use App\Models\NotificationSystemSetting;
use App\Models\User;
use App\Services\UserNotificationService;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class NotificationManagement extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Ayarlar';
    protected static ?string $navigationLabel = 'Bildirim merkezi';
    protected static ?string $title = 'Bildirim merkezi';
    protected static ?int $navigationSort = 3;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;
    protected string $view = 'filament.pages.notification-management';

    public ?array $data = [];
    public array $recentNotifications = [];
    public bool $pushConfigured = false;
    public ?string $testRecipient = null;
    public string $announcementTitle = '';
    public string $announcementMessage = '';

    public function mount(): void
    {
        $settings = NotificationSystemSetting::current();
        $values = $settings->settings ?? [];

        $this->pushConfigured = filled(config('webpush.public_key')) && filled(config('webpush.private_key'));

        $this->form->fill([
            'enabled' => data_get($values, 'enabled', true),
            'stream_enabled' => data_get($values, 'stream_enabled', true),
            'email_enabled' => data_get($values, 'email_enabled', true),
            'push_enabled' => data_get($values, 'push_enabled', true),
            'category_replies' => data_get($values, 'categories.replies', true),
            'category_posts' => data_get($values, 'categories.posts', true),
            'category_announcements' => data_get($values, 'categories.announcements', true),
            'category_followers' => data_get($values, 'categories.followers', true),
            'category_messages' => data_get($values, 'categories.messages', true),
            'category_mentions' => data_get($values, 'categories.mentions', true),
            'category_security' => data_get($values, 'categories.security', true),
        ]);

        $this->loadRecentNotifications();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Genel bildirim kontrolü')
                    ->description('Bu ana anahtar kapatılırsa OgraFi kullanıcılarına yeni bildirim gönderilmez. Yönetici panelinin kendi sistem bildirimleri bundan etkilenmez.')
                    ->icon(Heroicon::OutlinedBell)
                    ->schema([
                        Toggle::make('enabled')->label('Bildirim sistemini etkinleştir')->default(true)->inline(false),
                    ]),
                Section::make('Gönderim kanalları')
                    ->description('Her kanal bağımsız olarak açılıp kapatılabilir. Kullanıcının kendi tercihleri de ayrıca dikkate alınır.')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->schema([
                        Toggle::make('stream_enabled')->label('Site içi bildirim akışı')->default(true),
                        Toggle::make('email_enabled')->label('E-posta bildirimleri')->default(true),
                        Toggle::make('push_enabled')->label('Tarayıcı Push bildirimleri')->default(true),
                    ])->columns(3),
                Section::make('Bildirim türleri')
                    ->description('Sistemde hangi olaylar için bildirim üretileceğini belirleyin.')
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->schema([
                        Toggle::make('category_replies')->label('Yorumlar ve yanıtlar')->default(true),
                        Toggle::make('category_posts')->label('Yeni yazılar')->default(true),
                        Toggle::make('category_announcements')->label('Site duyuruları')->default(true),
                        Toggle::make('category_followers')->label('Yeni takipçiler')->default(true),
                        Toggle::make('category_messages')->label('Özel mesajlar')->default(true),
                        Toggle::make('category_mentions')->label('@bahsetmeler')->default(true),
                        Toggle::make('category_security')->label('Hesap güvenliği')->default(true),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        NotificationSystemSetting::current()->update([
            'settings' => [
                'enabled' => (bool) ($data['enabled'] ?? false),
                'stream_enabled' => (bool) ($data['stream_enabled'] ?? false),
                'email_enabled' => (bool) ($data['email_enabled'] ?? false),
                'push_enabled' => (bool) ($data['push_enabled'] ?? false),
                'categories' => [
                    'replies' => (bool) ($data['category_replies'] ?? false),
                    'posts' => (bool) ($data['category_posts'] ?? false),
                    'announcements' => (bool) ($data['category_announcements'] ?? false),
                    'followers' => (bool) ($data['category_followers'] ?? false),
                    'messages' => (bool) ($data['category_messages'] ?? false),
                    'mentions' => (bool) ($data['category_mentions'] ?? false),
                    'security' => (bool) ($data['category_security'] ?? false),
                ],
            ],
        ]);

        Notification::make()->title('Bildirim ayarları kaydedildi')->success()->send();
    }

    public function sendTest(): void
    {
        $user = auth()->user();

        if (! $user) {
            Notification::make()->title('Oturum bulunamadı')->danger()->send();
            return;
        }

        try {
            UserNotificationService::send(
                $user,
                'security',
                'OgraFi test bildirimi',
                'Bildirim Merkezi üzerinden test bildirimi gönderildi.',
                url('/admin'),
            );

            Notification::make()
                ->title('Test bildirimi oluşturuldu')
                ->body('Site içi bildirim ve etkin Push kanalı kullanıcı tercihleri ile VAPID yapılandırmasına göre gönderildi.')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()->title('Test bildirimi gönderilemedi')->body('Ayrıntılar uygulama günlüklerine kaydedildi.')->danger()->send();
        }

        $this->loadRecentNotifications();
    }

    public function sendAnnouncement(): void
    {
        $validated = Validator::make([
            'title' => $this->announcementTitle,
            'message' => $this->announcementMessage,
        ], [
            'title' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'title.required' => 'Duyuru başlığı gerekli.',
            'message.required' => 'Duyuru metni gerekli.',
        ])->validate();

        $settings = NotificationSystemSetting::current();
        if (! $settings->enabled('enabled') || ! $settings->enabled('categories.announcements')) {
            Notification::make()
                ->title('Duyuru bildirimleri kapalı')
                ->body('Önce genel bildirim sistemini ve duyuru bildirim türünü etkinleştirin.')
                ->warning()
                ->send();

            return;
        }

        $sent = 0;
        User::query()->select('id')->chunkById(100, function ($users) use (&$sent, $validated): void {
            foreach ($users as $user) {
                UserNotificationService::send(
                    $user,
                    'announcements',
                    $validated['title'],
                    $validated['message'],
                    url('/'),
                );
                $sent++;
            }
        });

        $this->announcementTitle = '';
        $this->announcementMessage = '';

        Notification::make()
            ->title('Duyuru bildirimi gönderim kuyruğuna alındı')
            ->body(number_format($sent).' kullanıcı için bildirim işlemi başlatıldı. Her kullanıcının kanal tercihleri ve sistem ayarları uygulandı.')
            ->success()
            ->send();

        $this->loadRecentNotifications();
    }

    public function refreshNotifications(): void
    {
        $this->loadRecentNotifications();
        Notification::make()->title('Bildirim listesi yenilendi')->success()->send();
    }

    private function loadRecentNotifications(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('notifications')) {
            $this->recentNotifications = [];
            return;
        }

        $this->recentNotifications = DB::table('notifications')
            ->leftJoin('users', function ($join): void {
                $join->on('users.id', '=', 'notifications.notifiable_id')
                    ->where('notifications.notifiable_type', '=', \App\Models\User::class);
            })
            ->select([
                'notifications.id',
                'notifications.data',
                'notifications.created_at',
                'notifications.read_at',
                'users.name as recipient_name',
                'users.email as recipient_email',
            ])
            ->orderByDesc('notifications.created_at')
            ->limit(50)
            ->get()
            ->map(function ($row): array {
                $data = is_array($row->data) ? $row->data : (json_decode((string) $row->data, true) ?: []);

                return [
                    'id' => $row->id,
                    'title' => data_get($data, 'title', 'Bildirim'),
                    'message' => data_get($data, 'message', data_get($data, 'body', '')),
                    'category' => data_get($data, 'category', 'sistem'),
                    'recipient' => $row->recipient_name ?: 'Silinmiş kullanıcı',
                    'email' => $row->recipient_email ?: '—',
                    'created_at' => $row->created_at,
                    'is_read' => filled($row->read_at),
                ];
            })
            ->all();
    }
}
