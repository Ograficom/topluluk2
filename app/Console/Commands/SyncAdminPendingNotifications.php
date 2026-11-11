<?php

namespace App\Console\Commands;

use App\Services\AdminNotificationService;
use Illuminate\Console\Command;

class SyncAllAdminRecordNotifications extends Command
{
    protected $signature = 'admin-notifications:sync-all';

    protected $description = 'Create persistent Filament database notifications for all existing admin records';

    public function handle(): int
    {
        $count = AdminNotificationService::syncAllRecordsToAdmins();

        $this->info($count.' yeni yönetim bildirimi oluşturuldu.');

        return self::SUCCESS;
    }
}
