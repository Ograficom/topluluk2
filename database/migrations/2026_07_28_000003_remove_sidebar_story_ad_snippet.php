<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('snippets')->where('key', 'ads_sidebar_story')->delete();
    }

    public function down(): void
    {
        // Kasitli olarak geri alinmiyor - bu yerlesim kaldirildi.
    }
};
