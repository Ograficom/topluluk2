<?php

namespace App\Support;

class Totp
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function secret(int $length = 32): string
    {
        $bytes = random_bytes((int) ceil($length * 5 / 8));
        $bits = '';
        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }
        $secret = '';
        foreach (str_split($bits, 5) as $chunk) {
            if (strlen($chunk) === 5) {
                $secret .= self::ALPHABET[bindec($chunk)];
            }
        }
        return substr($secret, 0, $length);
    }

    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        if (! preg_match('/^\d{6}$/', $code)) return false;
        $key = self::decodeBase32($secret);
        $counter = (int) floor(time() / 30);
        for ($i = -$window; $i <= $window; $i++) {
            $binaryCounter = pack('N*', 0, $counter + $i);
            $hash = hash_hmac('sha1', $binaryCounter, $key, true);
            $offset = ord($hash[19]) & 0x0f;
            $number = ((ord($hash[$offset]) & 0x7f) << 24)
                | ((ord($hash[$offset + 1]) & 0xff) << 16)
                | ((ord($hash[$offset + 2]) & 0xff) << 8)
                | (ord($hash[$offset + 3]) & 0xff);
            $expected = str_pad((string) ($number % 1000000), 6, '0', STR_PAD_LEFT);
            if (hash_equals($expected, $code)) return true;
        }
        return false;
    }

    public static function recoveryCodes(int $count = 8): array
    {
        return array_map(fn () => strtoupper(bin2hex(random_bytes(5))), range(1, $count));
    }

    private static function decodeBase32(string $secret): string
    {
        $bits = '';
        foreach (str_split(strtoupper(rtrim($secret, '='))) as $character) {
            $position = strpos(self::ALPHABET, $character);
            if ($position === false) throw new \InvalidArgumentException('Invalid Base32 secret.');
            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }
        $binary = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) $binary .= chr(bindec($chunk));
        }
        return $binary;
    }
}
