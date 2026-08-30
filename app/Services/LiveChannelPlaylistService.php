<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LiveChannelPlaylistService
{
    public function parseFile(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            return [];
        }

        return $this->parse((string) file_get_contents($path));
    }

    public function parse(string $content): array
    {
        $lines = preg_split('/\R/u', $content) ?: [];
        $channels = [];
        $pending = null;

        foreach ($lines as $rawLine) {
            $line = trim((string) $rawLine);

            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, '#EXTINF:')) {
                $name = trim(Str::afterLast($line, ','));
                $category = 'Genel';

                if (preg_match('/group-title=(?:"([^"]*)"|\'([^\']*)\'|([^\s,]+))/i', $line, $match) === 1) {
                    $category = trim((string) ($match[1] ?? $match[2] ?? $match[3] ?? 'Genel')) ?: 'Genel';
                }

                $pending = [
                    'name' => $name !== '' ? $name : 'Canlı Kanal',
                    'category' => $category,
                ];

                continue;
            }

            if (str_starts_with($line, '#')) {
                continue;
            }

            if (! filter_var($line, FILTER_VALIDATE_URL)) {
                $pending = null;
                continue;
            }

            $channels[] = [
                'name' => $pending['name'] ?? ('Canlı Kanal ' . (count($channels) + 1)),
                'category' => $pending['category'] ?? 'Genel',
                'stream_url' => $line,
            ];

            $pending = null;
        }

        return collect($channels)
            ->unique('stream_url')
            ->values()
            ->all();
    }

    public function syncFromFile(string $path): array
    {
        return $this->sync($this->parseFile($path));
    }

    public function sync(array $channels): array
    {
        if (! Schema::hasTable('live_channels')) {
            return ['created' => 0, 'existing' => 0, 'total' => 0];
        }

        $created = 0;
        $existing = 0;
        $position = 0;

        foreach ($channels as $channel) {
            $url = trim((string) ($channel['stream_url'] ?? ''));

            if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $position += 10;

            $row = DB::table('live_channels')->where('stream_url', $url)->first();

            if ($row) {
                $existing++;
                continue;
            }

            DB::table('live_channels')->insert([
                'name' => trim((string) ($channel['name'] ?? '')) ?: ('Canlı Kanal ' . $position),
                'category' => trim((string) ($channel['category'] ?? '')) ?: 'Genel',
                'stream_url' => $url,
                'featured_image' => null,
                'sort_order' => $position,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $created++;
        }

        return [
            'created' => $created,
            'existing' => $existing,
            'total' => $created + $existing,
        ];
    }
}
