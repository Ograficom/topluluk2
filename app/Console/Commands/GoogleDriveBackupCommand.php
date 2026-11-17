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

        $db = config('database.connections.'.config('database.default'));
        if (! in_array($db['driver'] ?? null, ['mysql', 'mariadb'], true)) {
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

        $stamp = now()->format('Y-m-d_His');
        $plainPath = $directory.'/ografi-db-'.$stamp.'.sql';
        $encryptedPath = $directory.'/ografi-db-'.$stamp.'.sql.enc';
        $plain = fopen($plainPath, 'wb');

        if (! $plain) {
            $this->error('Geçici veritabanı akışı oluşturulamadı.');
            return self::FAILURE;
        }

        @chmod($plainPath, 0600);
        $process = new Process($this->dumpCommand($db), base_path(), $this->processEnvironment($db));
        $process->setTimeout(null);
        $exitCode = self::FAILURE;

        try {
            $process->run(function (string $type, string $buffer) use ($plain): void {
                if ($type === Process::ERR) {
                    $this->output->write($buffer);
                    return;
                }

                if (fwrite($plain, $buffer) === false) {
                    throw new \RuntimeException('Veritabanı yedeği geçici dosyaya yazılamadı.');
                }
            });

            fflush($plain);
            fclose($plain);
            $plain = null;

            if (! $process->isSuccessful()) {
                throw new \RuntimeException('mysqldump başarısız: '.$process->getErrorOutput());
            }

            if (! is_file($plainPath) || filesize($plainPath) === 0) {
                throw new \RuntimeException('mysqldump boş yedek oluşturdu.');
            }

            $input = fopen($plainPath, 'rb');
            $encrypted = fopen($encryptedPath, 'wb');
            if (! $input || ! $encrypted) {
                throw new \RuntimeException('Şifreleme dosyaları açılamadı.');
            }

            @chmod($encryptedPath, 0600);
            try {
                $cryptor->encryptStream($input, $encrypted);
                fflush($encrypted);
            } finally {
                fclose($input);
                fclose($encrypted);
            }

            $name = basename($encryptedPath);
            $uploaded = $storage->upload($encryptedPath, $name);
            GoogleDriveConnection::current()?->forceFill([
                'last_checked_at' => now(),
                'last_error' => null,
            ])->save();

            $this->info('Şifreli veritabanı yedeği Google Drive’a yüklendi: '.$uploaded['name']);
            $this->line('Drive file ID: '.$uploaded['id']);
            $exitCode = self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            GoogleDriveConnection::current()?->forceFill([
                'last_error' => mb_substr($exception->getMessage(), 0, 2000),
            ])->save();
        } finally {
            if (is_resource($plain)) {
                fclose($plain);
            }

            foreach ([$plainPath, $encryptedPath] as $temporaryFile) {
                if (is_file($temporaryFile)) {
                    @unlink($temporaryFile);
                }
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
