<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Activate the supplied new bundled logo once. Keep old uploaded
        // files on disk; subsequent admin uploads override these defaults.
        DB::table('site_settings')->where('id', 1)->update([
            'pwa_icon_192_path' => null,
            'pwa_icon_512_path' => null,
            'pwa_maskable_icon_512_path' => null,
        ]);
    }

    public function down(): void
    {
        // Previous files were not deleted; the administrator can reselect them.
    }
};
