<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('rss_feeds')
            ->where(function ($query): void {
                $query->whereNull('ai_model')
                    ->orWhere('ai_model', '')
                    ->orWhere('ai_model', 'like', '%:%')
                    ->orWhere('ai_model', 'like', 'gpt-oss%')
                    ->orWhere('ai_model', 'like', 'llama%')
                    ->orWhere('ai_model', 'like', 'qwen%')
                    ->orWhere('ai_model', 'like', 'gemma%');
            })
            ->update(['ai_model' => 'gpt-5.6-luna']);
    }

    public function down(): void
    {
        // Data migration: eski model adini guvenli bicimde geri kurmak mumkun degil.
    }
};
