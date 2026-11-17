<?php

namespace App\Services\GoogleDrive;

use RuntimeException;

class GoogleDriveBackupCryptor
{
    private const MAGIC = "OGRAFI-GDRIVE-BACKUP-V1\n";
    private const CHUNK_SIZE = 65536;

    private function key(): string
    {
        $appKey = (string) config('app.key');

        if ($appKey === '') {
            throw new RuntimeException('APP_KEY tanımlı değil; yedek şifrelenemez.');
        }

        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);
            $appKey = $decoded === false ? '' : $decoded;
        }

        if ($appKey === '') {
            throw new RuntimeException('APP_KEY çözümlenemedi.');
        }

        return hash_hkdf('sha256', $appKey, SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_KEYBYTES, 'ografi-google-drive-backup-v1');
    }

    public function encryptStream($input, $output): void
    {
        if (! function_exists('sodium_crypto_secretstream_xchacha20poly1305_init_push')) {
            throw new RuntimeException('PHP sodium eklentisi gerekli.');
        }

        [$state, $header] = sodium_crypto_secretstream_xchacha20poly1305_init_push($this->key());
        fwrite($output, self::MAGIC.$header);

        while (! feof($input)) {
            $chunk = fread($input, self::CHUNK_SIZE);

            if ($chunk === false) {
                throw new RuntimeException('Yedek akışı okunamadı.');
            }

            if ($chunk === '' && feof($input)) {
                break;
            }

            $encrypted = sodium_crypto_secretstream_xchacha20poly1305_push(
                $state,
                $chunk,
                '',
                SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_MESSAGE
            );
            fwrite($output, pack('N', strlen($encrypted)).$encrypted);
        }

        $final = sodium_crypto_secretstream_xchacha20poly1305_push(
            $state,
            '',
            '',
            SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL
        );
        fwrite($output, pack('N', strlen($final)).$final);
    }

    public function decryptFile(string $source, string $destination): void
    {
        if (! function_exists('sodium_crypto_secretstream_xchacha20poly1305_init_pull')) {
            throw new RuntimeException('PHP sodium eklentisi gerekli.');
        }

        $input = fopen($source, 'rb');
        $output = fopen($destination, 'wb');

        if (! $input || ! $output) {
            throw new RuntimeException('Yedek dosyası açılamadı.');
        }

        try {
            $magic = fread($input, strlen(self::MAGIC));
            $header = fread($input, SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES);

            if ($magic !== self::MAGIC || strlen($header) !== SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES) {
                throw new RuntimeException('Dosya Google Drive yedek formatında değil.');
            }

            $state = sodium_crypto_secretstream_xchacha20poly1305_init_pull($header, $this->key());
            $finished = false;

            while (! feof($input)) {
                $lengthBytes = fread($input, 4);

                if ($lengthBytes === '' || $lengthBytes === false) {
                    break;
                }

                if (strlen($lengthBytes) !== 4) {
                    throw new RuntimeException('Yedek dosyası bozuk (blok uzunluğu).');
                }

                $length = unpack('Nlength', $lengthBytes)['length'];

                if ($length < SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES || $length > self::CHUNK_SIZE + 1024) {
                    throw new RuntimeException('Yedek dosyası bozuk (blok boyutu).');
                }

                $ciphertext = '';
                while (strlen($ciphertext) < $length && ! feof($input)) {
                    $part = fread($input, $length - strlen($ciphertext));
                    if ($part === false || $part === '') {
                        break;
                    }
                    $ciphertext .= $part;
                }

                if (strlen($ciphertext) !== $length) {
                    throw new RuntimeException('Yedek dosyası eksik indirilmiş.');
                }

                $pulled = sodium_crypto_secretstream_xchacha20poly1305_pull($state, $ciphertext);

                if ($pulled === false) {
                    throw new RuntimeException('Yedek doğrulaması veya şifre çözme başarısız.');
                }

                [$plain, $tag] = $pulled;
                if ($plain !== '') {
                    fwrite($output, $plain);
                }

                if ($tag === SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL) {
                    $finished = true;
                    break;
                }
            }

            if (! $finished) {
                throw new RuntimeException('Yedek tamamlanmamış veya son doğrulama bloğu eksik.');
            }
        } finally {
            fclose($input);
            fclose($output);
        }

        @chmod($destination, 0600);
    }
}
