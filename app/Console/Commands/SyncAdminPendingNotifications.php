<?php

namespace App\Console\Commands;

use App\Services\AdminNotificationService;
use Illuminate\Console\Command;

class SyncAdminPendingNotifications extends Command
{
    protected $signature = 'admin-notifications:sync-pending';

    protected $description = 'Create Filament database notifications for existing pending admin records';

    public function handle(): int
    {
        $count = AdminNotificationService::syncPendingToAdmins();

        $this->info($count.' yeni yönetim bildirimi oluşturuldu.');

        return self::SUCCESS;
    }
}
