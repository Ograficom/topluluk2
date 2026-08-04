<?php

use App\Models\ThemeSetting;
use Illuminate\Database\Migrations\Migration;

/**
 * Until now, "Gorunum Ayarlari" (ThemeAppearance) showed empty fields even though the
 * site clearly has a look - every brand/categories/font value used by the frontend was a
 * hardcoded fallback baked into layouts/app.blade.php and blog/categories.blade.php,
 * applied only when the corresponding ThemeSetting column was null. That made the admin
 * panel misleading (nothing to edit/export) and meant "Temayi Indir" produced a package of
 * mostly-null values instead of the design actually running in production.
 *
 * This one-time data migration writes those exact fallback values into the ThemeSetting
 * row - same colors/fonts/sizes, so the rendered site is visually unchanged - but now as
 * explicit, editable, exportable settings. Only currently-null columns are touched, so an
 * admin who already customized a value through the panel is never overwritten.
 */
return new class extends Migration
{
    private const CURRENT_LIVE_DEFAULTS = [
        'brand_background_color' => '#fafafa',
        'brand_surface_color' => '#ffffff',
        'brand_button_color' => '#2563eb',
        'brand_button_hover_color' => '#1d4ed8',
        'brand_button_text_color' => '#ffffff',
        'brand_text_color' => '#0d0d10',
        'brand_font_family' => 'Inter',
        'global_text_scale' => 100,

        'categories_name_color' => '#000000',
        'categories_stats_color' => '#71717a',
        'categories_description_color' => '#5f6472',
        'categories_accent_color' => '#2563eb',
        'categories_hover_bg_color' => '#f9fafb',
        'categories_border_color' => '#e4e4e7',
        'categories_avatar_size' => 36,
        'categories_name_font_size' => 14.5,
        'categories_stats_font_size' => 12.5,
        'categories_description_font_size' => 13,

        'font_heading_fallback' => 'Inter, Arial, Helvetica, sans-serif',
        'font_body_fallback' => 'Inter, Arial, Helvetica, sans-serif',
        'font_button_fallback' => 'Inter, Arial, Helvetica, sans-serif',
        'font_nav_fallback' => 'Inter, Arial, Helvetica, sans-serif',
        'font_code_fallback' => 'ui-monospace, Consolas, Menlo, monospace',
    ];

    public function up(): void
    {
        $settings = ThemeSetting::current();

        $toFill = collect(self::CURRENT_LIVE_DEFAULTS)
            ->filter(fn ($value, $field) => is_null($settings->{$field}))
            ->all();

        if ($toFill !== []) {
            $settings->update($toFill);
        }
    }

    public function down(): void
    {
        $settings = ThemeSetting::current();

        $stillMatchesSeededDefault = collect(self::CURRENT_LIVE_DEFAULTS)
            ->filter(fn ($value, $field) => (string) $settings->{$field} === (string) $value)
            ->map(fn () => null)
            ->all();

        if ($stillMatchesSeededDefault !== []) {
            $settings->update($stillMatchesSeededDefault);
        }
    }
};
