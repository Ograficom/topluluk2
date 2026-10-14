<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Avoid duplicate-column failures if a prior deploy partially applied
        // the schema change but did not record the migration.
        $themeSettingColumns = array_values(array_filter(
            ['component_styles', 'page_layouts'],
            static fn (string $column): bool => !Schema::hasColumn('theme_settings', $column),
        ));

        if ($themeSettingColumns !== []) {
            Schema::table('theme_settings', static function (Blueprint $table) use ($themeSettingColumns): void {
                if (in_array('component_styles', $themeSettingColumns, true)) {
                    $table->json('component_styles')->nullable();
                }

                if (in_array('page_layouts', $themeSettingColumns, true)) {
                    $table->json('page_layouts')->nullable();
                }
            });
        }

        if (!Schema::hasTable('theme_design_revisions')) {
            Schema::create('theme_design_revisions', static function (Blueprint $table): void {
                $table->id();
                $table->foreignId('theme_setting_id')->constrained('theme_settings')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('label', 120)->default('Otomatik yedek');
                $table->json('component_styles');
                $table->json('page_layouts')->nullable();
                $table->timestamps();

                $table->index(['theme_setting_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_design_revisions');

        $columns = array_values(array_filter(
            ['component_styles', 'page_layouts'],
            static fn (string $column): bool => Schema::hasColumn('theme_settings', $column),
        ));

        if ($columns !== []) {
            Schema::table('theme_settings', static function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
