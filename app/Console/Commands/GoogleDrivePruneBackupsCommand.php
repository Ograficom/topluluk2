<?php

namespace App\Console\Commands;

use App\Models\GoogleDriveConnection;
use App\Services\GoogleDrive\GoogleDriveStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class GoogleDrivePruneBackupsCommand extends Command
{
    protected $signature = 'google-drive:prune';
    protected $description = 'Delete encrypted Google Drive backups older than the configured retention period';

    public function handle(GoogleDriveStorage $storage): int
    {
        if (! config('google-drive.enabled') || ! config('google-drive.backup_enabled')) {
            $this->warn('Google Drive yedeklemesi etkin değil.');
            return self::SUCCESS;
        }

        $connection = GoogleDriveConnection::current();
        if (! $connection?->isConnected() || ! $connection->root_folder_id) {
            $this->error('Google Drive bağlantısı veya kök klasörü bulunamadı.');
            return self::FAILURE;
        }

        $retention = max(1, (int) config('google-drive.backup_retention', 30));
        $cutoff = now()->subDays($retention);
        $deleted = 0;

        try {
            foreach ($storage->listFiles($connection->root_folder_id, 'ografi-db-') as $file) {
                if (! str_starts_with($file['name'], 'ografi-db-') || ! str_ends_with($file['name'], '.sql.enc') || empty($file['created_time'])) {
                    continue;
                }

                if (Carbon::parse($file['created_time'])->lt($cutoff)) {
                    $storage->delete($file['id']);
                    $deleted++;
                }
            }

            $this->info("Saklama süresi: {$retention} gün. Silinen eski yedek: {$deleted}.");
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            $connection->forceFill(['last_error' => mb_substr($exception->getMessage(), 0, 2000)])->save();
            return self::FAILURE;
        }
    }
}
