<?php

namespace App\Notifications;

use App\Models\NotificationSystemSetting;
use App\Models\MailSetting;
use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SiteActivityNotification extends Notification
{
    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly string $url,
        public readonly ?string $sourceKey = null,
        public readonly ?string $category = null,
        public readonly ?string $senderName = null,
        public readonly ?string $mailSummary = null,
    ) {}

    public function via(object $notifiable): array
    {
        // Administrative notifications are always kept in the Filament database inbox.
        if ($this->category === null) {
            return ['database', 'broadcast'];
        }

        $systemSettings = NotificationSystemSetting::current();
        if (! $systemSettings->enabled('enabled') || ! $systemSettings->enabled('categories.'.$this->category)) {
            return [];
        }

        $preferences = $notifiable->notification_preferences ?? [];
        $channels = [];

        $streamEnabled = $systemSettings->enabled('stream_enabled') && (bool) data_get($preferences, 'stream.'.$this->category, true);

        $legacyEmailDefault = match ($this->category) {
            'replies' => (bool) ($notifiable->notify_replies ?? true),
            'posts' => (bool) ($notifiable->notify_posts ?? true),
            'announcements' => (bool) ($notifiable->notify_announcements ?? true),
            default => true,
        };

        // SMTP settings are stored in the database; apply them on every request
        // because Filament's settings form only changes runtime config for its own request.
        $mailSettings = MailSetting::current();
        $smtpEnabled = $mailSettings->enabled
            && filled($mailSettings->host)
            && filled($mailSettings->username)
            && filled($mailSettings->password);

        if ($smtpEnabled) {
            config([
                'mail.default' => $mailSettings->mailer ?: 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.scheme' => $mailSettings->scheme,
                'mail.mailers.smtp.host' => $mailSettings->host,
                'mail.mailers.smtp.port' => $mailSettings->port,
                'mail.mailers.smtp.username' => $mailSettings->username,
                'mail.mailers.smtp.password' => $mailSettings->password,
                'mail.from.address' => $mailSettings->from_address,
                'mail.from.name' => $mailSettings->from_name,
            ]);
        }

        $emailEnabled = $systemSettings->enabled('email_enabled')
            && $smtpEnabled
            && (bool) data_get($preferences, 'email.'.$this->category, $legacyEmailDefault);
        $pushEnabled = $systemSettings->enabled('push_enabled') && (bool) data_get($preferences, 'push.'.$this->category, $this->category === 'security');

        // The database row powers the in-site stream and is also the durable push event record.
        if ($streamEnabled || $pushEnabled) {
            $channels[] = 'database';
        }

        if ($streamEnabled) {
            $channels[] = 'broadcast';
        }

        if ($emailEnabled) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            ...FilamentNotification::make()
                ->title($this->title)
                ->body($this->message)
                ->actions([
                    Action::make('view')
                        ->label('Görüntüle')
                        ->button()
                        ->url($this->url)
                        ->markAsRead(),
                ])
                ->getDatabaseMessage(),
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'category' => $this->category,
            'source_key' => $this->sourceKey,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'category' => $this->category,
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $target = str_starts_with($this->url, 'http')
            ? $this->url
            : url('/'.ltrim($this->url, '/'));

        $siteSettings = SiteSetting::current();
        $siteName = filled($siteSettings?->brand_name) ? $siteSettings->brand_name : 'OgraFi';
        $logoUrl = filled($siteSettings?->brandLogoUrl())
            ? url($siteSettings->brandLogoUrl())
            : url('/assets/img/meta/logo.svg');

        return (new MailMessage)
            ->subject($this->title.' — '.$siteName)
            ->view('emails.notifications.activity', [
                'recipientName' => $notifiable->displayName(),
                'senderName' => $this->senderName ?: 'OgraFi',
                'summary' => $this->mailSummary ?: 'Hesabında yeni bir etkinlik var.',
                'sentAt' => now()->timezone(config('app.timezone'))->locale('tr')->translatedFormat('d F Y, H:i'),
                'target' => $target,
                'siteName' => $siteName,
                'logoUrl' => $logoUrl,
            ]);
    }
}
