<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyDailyDigestEmail extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $user,
        private readonly string $email,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'digest.email.verify',
            now()->addMinutes(60),
            [
                'userId' => $this->user->getKey(),
                'hash' => sha1(mb_strtolower(trim($this->email))),
            ],
        );

        return (new MailMessage)
            ->subject('Günlük özet e-posta adresinizi doğrulayın')
            ->greeting('Merhaba '.$this->user->name.',')
            ->line('Ografinin günlük gönderi özetini bu adrese almak istediğinizi doğrulayın.')
            ->action('E-posta adresini doğrula', $url)
            ->line('Bu isteği siz yapmadıysanız bu e-postayı yok sayabilirsiniz.');
    }
}
