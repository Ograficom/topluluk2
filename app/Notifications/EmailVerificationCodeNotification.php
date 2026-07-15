<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationCodeNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly string $code)
    {
    }

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
            ->subject('Ografi e-posta doğrulama kodunuz')
            ->greeting('Merhaba '.$notifiable->name.',')
            ->line('Ografi üyeliğinizi tamamlamak için doğrulama kodunuz:')
            ->line('**'.$this->code.'**')
            ->line('Bu kod 10 dakika geçerlidir. Kodu kimseyle paylaşmayın.')
            ->line('Bu üyeliği siz oluşturmadıysanız bu e-postayı yok sayabilirsiniz.');
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
