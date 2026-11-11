<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateWebPushKeys extends Command
{
    protected $signature = 'webpush:generate-keys';

    protected $description = 'Generate VAPID keys for OgraFi browser push notifications';

    public function handle(): int
    {
        if (! class_exists(VAPID::class)) {
            $this->error('minishlink/web-push paketi kurulu değil. Önce composer update minishlink/web-push --with-all-dependencies çalıştırın.');

            return self::FAILURE;
        }

        $keys = VAPID::createVapidKeys();

        $this->line('VAPID_PUBLIC_KEY='.$keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY='.$keys['privateKey']);
        $this->line('VAPID_SUBJECT='.config('app.url'));

        $this->newLine();
        $this->warn('Bu anahtarları sunucudaki .env dosyasına ekleyin. PRIVATE KEY değerini kimseyle paylaşmayın.');

        return self::SUCCESS;
    }
}
