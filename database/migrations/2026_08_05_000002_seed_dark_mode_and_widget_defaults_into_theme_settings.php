<?php

use App\Models\ThemeSetting;
use Illuminate\Database\Migrations\Migration;

/**
 * Follow-up to 2026_08_05_000001: that migration only covered the fields exposed on the
 * "Gorunum Ayarlari" admin page. Tracing every ThemeSetting field's actual read site turned
 * up two more groups with real, currently-live hardcoded values that are missing here:
 *
 * - dark_* (7 fields): read with these exact fallbacks in
 *   resources/views/partials/system-appearance.blade.php (`html.dark { --site-bg: var(--bg-dark, #0b1220); ... }`),
 *   included on every public layout - this genuinely paints the site's dark mode today.
 * - widget_* (6 fields): read with these exact fallbacks in
 *   app/Providers/AppServiceProvider.php's `partials.right` view composer, gating the
 *   "populer yorumlar / trend / etiketler" sidebar widgets.
 *
 * (header_*, layout_*, global_shadow/header_shadow and the *_html fields were deliberately
 * left out - confirmed via a full read-site audit that they are either unused anywhere in
 * the current codebase or only conditionally applied with no fallback value, so there is no
 * real "current value" to seed for them without fabricating data.)
 *
 * Same non-destructive rule as before: only currently-null columns are filled, so any value
 * an admin already set through the panel is left untouched.
 */
return new class extends Migration
{
    private const CURRENT_LIVE_DEFAULTS = [
        'dark_bg_color' => '#0b1220',
        'dark_surface_color' => '#111827',
        'dark_surface2_color' => '#0f172a',
        'dark_text_color' => '#e5e7eb',
        'dark_muted_color' => '#94a3b8',
        'dark_border_color' => 'rgba(148, 163, 184, 0.18)',
        'dark_primary_color' => '#029d71',

        'widget_comments_enabled' => true,
        'widget_comments_count' => 10,
        'widget_tags_enabled' => true,
        'widget_tags_count' => 10,
        'widget_trending_enabled' => true,
        'widget_trending_count' => 5,
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
