<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GrantAdminAccess extends Command
{
    protected $signature = 'admin:grant {email : The existing account email address}';

    protected $description = 'Grant Filament administrator access to an existing account';

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->components->error('Geçerli bir e-posta adresi girin.');

            return self::INVALID;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->components->error('Bu e-posta adresiyle kayıtlı bir kullanıcı bulunamadı.');

            return self::FAILURE;
        }

        if ($user->is_admin) {
            $this->components->info('Bu hesap zaten yönetici.');

            return self::SUCCESS;
        }

        if (! $this->confirm("{$user->email} hesabına Filament yönetici erişimi verilsin mi?")) {
            $this->components->warn('İşlem iptal edildi.');

            return self::FAILURE;
        }

        $user->forceFill(['is_admin' => true])->save();

        $this->components->info('Yönetici erişimi verildi. Hesapla yeniden giriş yapıp /admin adresini açabilirsiniz.');

        return self::SUCCESS;
    }
}
