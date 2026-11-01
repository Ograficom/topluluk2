<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pages')) {
            return;
        }

        $page = DB::table('pages')->where('slug', 'hakkimizda')->first(['id', 'content']);

        if (! $page) {
            return;
        }

        $content = (string) $page->content;

        if (str_contains($content, 'datetime="2025-12-25"')) {
            return;
        }

        $updatedContent = str_replace(
            '25 Aralık 2025',
            '<time datetime="2025-12-25">25 Aralık 2025</time>',
            $content,
        );

        if ($updatedContent !== $content) {
            DB::table('pages')->where('id', $page->id)->update([
                'content' => $updatedContent,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Preserve editorial content changes.
    }
};
