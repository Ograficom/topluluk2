<?php

namespace App\Console\Commands;

use App\Models\GoogleDriveConnection;
use App\Services\GoogleDrive\GoogleDriveBackupCryptor;
use App\Services\GoogleDrive\GoogleDriveStorage;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Throwable;

class GoogleDriveBackupCommand extends Command
{
    protected $signature = 'google-drive:backup';
    protected $description = 'Create an encrypted MariaDB backup and upload it to Google Drive';

    public function handle(GoogleDriveBackupCryptor $cryptor, GoogleDriveStorage $storage): int
    {
        if (! config('google-drive.enabled') || ! config('google-drive.backup_enabled')) {
            $this->warn('Google Drive yedeklemesi yapılandırmada kapalı.');
            return self::SUCCESS;
        }

        $connection = config('database.connections.'.config('database.default'));

        if (! in_array($connection['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            $this->error('Otomatik yedekleme yalnızca MySQL/MariaDB bağlantısını destekler.');
            return self::FAILURE;
        }

        if (! GoogleDriveConnection::current()?->isConnected()) {
            $this->error('Google Drive bağlantısı bulunamadı.');
            return self::FAILURE;
        }

        $directory = storage_path('app/private/google-drive-backups');
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            $this->error('Geçici yedek klasörü oluşturulamadı.');
            return self::FAILURE;
        }

        $name = 'ografi-db-'.now()->format('Y-m-d_His').'.sql.enc';
        $path = $directory.DIRECTORY_SEPARATOR.$name;
        $output = fopen($path, 'wb');

        if (! $output) {
            $this->error('Şifreli yedek dosyası oluşturulamadı.');
            return self::FAILURE;
        }

        @chmod($path, 0600);
        $process = new Process($this->dumpCommand($connection), base_path(), $this->processEnvironment($connection));
        $process->setTimeout(null);
        $exitCode = self::FAILURE;

        try {
            $process->run(function (string $type, string $buffer) use ($output, $cryptor): void {
                if ($type === Process::ERR) {
                    $this->output->write($buffer);
                    return;
                }

                $stream = fopen('php://temp', 'w+b');
                fwrite($stream, $buffer);
                rewind($stream);
                $cryptor->encryptStream($stream, $output);
                fclose($stream);
            });

            if (! $process->isSuccessful()) {
                throw new \RuntimeException('mysqldump başarısız: '.$process->getErrorOutput());
            }

            fflush($output);
            fclose($output);
            $output = null;

            $uploaded = $storage->upload($path, $name);
            GoogleDriveConnection::current()?->forceFill(['last_checked_at' => now(), 'last_error' => null])->save();

            $this->info('Şifreli veritabanı yedeği Google Drive’a yüklendi: '.$uploaded['name']);
            $this->line('Drive file ID: '.$uploaded['id']);
            $exitCode = self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            GoogleDriveConnection::current()?->forceFill(['last_error' => mb_substr($exception->getMessage(), 0, 2000)])->save();
        } finally {
            if (is_resource($output)) {
                fclose($output);
            }
            if (is_file($path)) {
                @unlink($path);
            }
        }

        return $exitCode;
    }

    private function dumpCommand(array $db): array
    {
        $binary = trim((string) env('MYSQLDUMP_PATH', 'mysqldump'));
        $command = [
            $binary,
            '--single-transaction',
            '--routines',
            '--triggers',
            '--events',
            '--hex-blob',
            '--default-character-set=utf8mb4',
        ];

        if (! empty($db['unix_socket'])) {
            $command[] = '--socket='.$db['unix_socket'];
        } else {
            $command[] = '--host='.($db['host'] ?? '127.0.0.1');
            $command[] = '--port='.($db['port'] ?? '3306');
        }

        $command[] = '--user='.($db['username'] ?? '');
        $command[] = (string) ($db['database'] ?? '');

        return $command;
    }

    private function processEnvironment(array $db): array
    {
        return array_merge($_SERVER, $_ENV, [
            'MYSQL_PWD' => (string) ($db['password'] ?? ''),
        ]);
    }
}
