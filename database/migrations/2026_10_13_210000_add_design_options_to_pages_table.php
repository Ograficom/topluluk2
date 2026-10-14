<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recover safely if a previous deploy added some columns but failed
        // before Laravel recorded the migration in the migrations table.
        $columns = [
            'layout_width' => static fn (Blueprint $table) => $table->string('layout_width', 20)->default('contained')->after('blocks'),
            'content_background' => static fn (Blueprint $table) => $table->string('content_background', 32)->nullable()->after('layout_width'),
            'content_text_color' => static fn (Blueprint $table) => $table->string('content_text_color', 32)->nullable()->after('content_background'),
            'content_padding' => static fn (Blueprint $table) => $table->unsignedSmallInteger('content_padding')->default(24)->after('content_text_color'),
            'content_radius' => static fn (Blueprint $table) => $table->string('content_radius', 20)->default('rounded')->after('content_padding'),
            'custom_css' => static fn (Blueprint $table) => $table->longText('custom_css')->nullable()->after('content_radius'),
        ];

        foreach ($columns as $name => $addColumn) {
            if (Schema::hasColumn('pages', $name)) {
                continue;
            }

            Schema::table('pages', static function (Blueprint $table) use ($addColumn): void {
                $addColumn($table);
            });
        }
    }

    public function down(): void
    {
        $columns = [
            'layout_width',
            'content_background',
            'content_text_color',
            'content_padding',
            'content_radius',
            'custom_css',
        ];

        $existing = array_values(array_filter(
            $columns,
            static fn (string $column): bool => Schema::hasColumn('pages', $column),
        ));

        if ($existing !== []) {
            Schema::table('pages', static function (Blueprint $table) use ($existing): void {
                $table->dropColumn($existing);
            });
        }
    }
};
