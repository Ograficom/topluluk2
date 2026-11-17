<?php

namespace Tests\Unit;

use App\Services\GoogleDrive\GoogleDriveBackupCryptor;
use Tests\TestCase;

class GoogleDriveBackupCryptorTest extends TestCase
{
    public function test_encrypted_backup_can_be_decrypted_without_changing_content(): void
    {
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);

        $source = tempnam(sys_get_temp_dir(), 'ografi-source-');
        $encrypted = tempnam(sys_get_temp_dir(), 'ografi-encrypted-');
        $decrypted = tempnam(sys_get_temp_dir(), 'ografi-decrypted-');
        $contents = str_repeat("CREATE TABLE sample (id INT);\nINSERT INTO sample VALUES (1);\n", 3000);
        file_put_contents($source, $contents);

        try {
            $cryptor = app(GoogleDriveBackupCryptor::class);
            $input = fopen($source, 'rb');
            $output = fopen($encrypted, 'wb');
            $cryptor->encryptStream($input, $output);
            fclose($input);
            fclose($output);

            $cryptor->decryptFile($encrypted, $decrypted);

            $this->assertSame($contents, file_get_contents($decrypted));
            $this->assertNotSame($contents, file_get_contents($encrypted));
        } finally {
            foreach ([$source, $encrypted, $decrypted] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function test_tampered_encrypted_backup_is_rejected(): void
    {
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);

        $source = tempnam(sys_get_temp_dir(), 'ografi-source-');
        $encrypted = tempnam(sys_get_temp_dir(), 'ografi-encrypted-');
        $decrypted = tempnam(sys_get_temp_dir(), 'ografi-decrypted-');

        try {
            file_put_contents($source, 'sensitive database content');
            $cryptor = app(GoogleDriveBackupCryptor::class);
            $input = fopen($source, 'rb');
            $output = fopen($encrypted, 'wb');
            $cryptor->encryptStream($input, $output);
            fclose($input);
            fclose($output);

            $bytes = file_get_contents($encrypted);
            $bytes[strlen($bytes) - 1] = chr(ord($bytes[strlen($bytes) - 1]) ^ 1);
            file_put_contents($encrypted, $bytes);

            $this->expectException(\RuntimeException::class);
            $cryptor->decryptFile($encrypted, $decrypted);
        } finally {
            foreach ([$source, $encrypted, $decrypted] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}
