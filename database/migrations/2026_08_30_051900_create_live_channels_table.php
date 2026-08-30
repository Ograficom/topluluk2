<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_channels', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('category', 80)->default('Genel')->index();
            $table->text('stream_url');
            $table->string('featured_image')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique('stream_url', 'live_channels_stream_url_unique');
        });

        $playlistPath = public_path('streams/turkiye.m3u');

        if (! is_file($playlistPath)) {
            return;
        }

        $lines = preg_split('/\R/u', (string) file_get_contents($playlistPath)) ?: [];
        $pending = null;
        $position = 0;

        foreach ($lines as $rawLine) {
            $line = trim((string) $rawLine);

            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, '#EXTINF:')) {
                $name = Str::afterLast($line, ',');
                $category = 'Genel';

                if (preg_match('/group-title=(?:"([^"]*)"|\'([^\']*)\'|([^\s,]+))/i', $line, $match) === 1) {
                    $category = trim((string) ($match[1] ?? $match[2] ?? $match[3] ?? 'Genel')) ?: 'Genel';
                }

                $pending = [
                    'name' => trim($name) ?: 'Canlı Kanal',
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

            $position += 10;

            DB::table('live_channels')->updateOrInsert(
                ['stream_url' => $line],
                [
                    'name' => $pending['name'] ?? ('Canlı Kanal ' . $position),
                    'category' => $pending['category'] ?? 'Genel',
                    'featured_image' => null,
                    'sort_order' => $position,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );

            $pending = null;
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('live_channels');
    }
};
