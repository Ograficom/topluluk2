<?php

namespace App\Console\Commands;

use App\Services\GoogleDrive\GoogleDriveBackupCryptor;
use App\Services\GoogleDrive\GoogleDriveStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Throwable;

class GoogleDriveRestoreCommand extends Command
{
    protected $signature = 'google-drive:restore {fileId} {targetDatabase} {--confirm-target}';
    protected $description = 'Restore an encrypted Google Drive backup into a separate empty database';

    public function handle(GoogleDriveBackupCryptor $cryptor, GoogleDriveStorage $storage): int
    {
        if (! $this->option('confirm-target')) {
            $this->error('Geri yükleme için --confirm-target seçeneği zorunludur.');
            return self::FAILURE;
        }

        $target = (string) $this->argument('targetDatabase');
        $current = (string) config('database.connections.'.config('database.default').'.database');

        if (! preg_match('/^[A-Za-z0-9_]+$/', $target) || hash_equals($current, $target)) {
            $this->error('Hedef veritabanı geçersiz veya canlı veritabanıyla aynı.');
            return self::FAILURE;
        }

        if (! config('google-drive.enabled')) {
            $this->error('Google Drive entegrasyonu etkin değil.');
            return self::FAILURE;
        }

        $db = config('database.connections.'.config('database.default'));
        if (! in_array($db['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            $this->error('Geri yükleme yalnızca MySQL/MariaDB bağlantısını destekler.');
            return self::FAILURE;
        }

        $directory = storage_path('app/private/google-drive-restore');
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            $this->error('Geçici geri yükleme klasörü oluşturulamadı.');
            return self::FAILURE;
        }

        $encrypted = $directory.'/restore-'.uniqid('', true).'.enc';
        $plain = $directory.'/restore-'.uniqid('', true).'.sql';

        try {
            $storage->downloadToPath((string) $this->argument('fileId'), $encrypted);
            $cryptor->decryptFile($encrypted, $plain);

            $pdo = DB::connection(config('database.default'))->getPdo();
            $statement = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?');
            $statement->execute([$target]);

            if ((int) $statement->fetchColumn() !== 0) {
                throw new \RuntimeException('Hedef veritabanı boş değil. Mevcut tabloların üzerine yazılmadı.');
            }

            $command = $this->mysqlCommand($db, $target);
            $input = fopen($plain, 'rb');
            $process = new Process($command, base_path(), $this->processEnvironment($db), $input);
            $process->setTimeout(null);
            $process->run();
            fclose($input);

            if (! $process->isSuccessful()) {
                throw new \RuntimeException('mysql geri yüklemesi başarısız: '.$process->getErrorOutput());
            }

            $this->info('Yedek ayrı ve boş hedef veritabanına geri yüklendi: '.$target);
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        } finally {
            foreach ([$encrypted, $plain] as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    private function mysqlCommand(array $db, string $target): array
    {
        $binary = trim((string) env('MYSQL_PATH', 'mysql'));
        $command = [$binary, '--default-character-set=utf8mb4'];

        if (! empty($db['unix_socket'])) {
            $command[] = '--socket='.$db['unix_socket'];
        } else {
            $command[] = '--host='.($db['host'] ?? '127.0.0.1');
            $command[] = '--port='.($db['port'] ?? '3306');
        }

        $command[] = '--user='.($db['username'] ?? '');
        $command[] = '--database='.$target;

        return $command;
    }

    private function processEnvironment(array $db): array
    {
        return array_merge($_SERVER, $_ENV, [
            'MYSQL_PWD' => (string) ($db['password'] ?? ''),
        ]);
    }
}
