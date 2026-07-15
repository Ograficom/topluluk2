<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PendingRegistrationCodeNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly string $code)
    {
    }

    /**
     * Create a new notification instance.
     */
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ografi üyelik doğrulama kodunuz')
            ->greeting('Merhaba,')
            ->line('Ografi üyeliğinize devam etmek için doğrulama kodunuz:')
            ->line('**'.$this->code.'**')
            ->line('Bu kod 10 dakika geçerlidir. Kod doğrulanmadan üyelik oluşturulmaz.')
            ->line('Bu isteği siz yapmadıysanız e-postayı yok sayabilirsiniz.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
