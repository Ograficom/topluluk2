<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ThemeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'header_html',
        'header_height',
        'header_padding_x',
        'header_padding_y',
        'header_left_width',
        'header_right_width',
        'header_bg_color',
        'header_max_width',
        'header_search_width',
        'header_search_border_color',
        'header_search_input_bg_color',
        'header_search_dropdown_bg_color',
        'header_search_text_color',
        'header_logo_text',
        'header_logo_image',
        'header_logo_url',
        'header_logo_alt',
        'header_login_label',
        'header_login_url',
        'header_user_menu_html',
        'left_column_html',
        'right_column_html',
        'home_html',
        'messages_html',
        'notifications_html',
        'categories_html',
        'tags_html',
        'profile_html',
        'index_html',
        'post_show_html',
        'layout_bg_color',
        'left_column_bg_color',
        'main_column_bg_color',
        'right_column_bg_color',
        'layout_max_width',
        'layout_padding_x',
        'layout_padding_y',
        'layout_gap',
        'layout_left_width',
        'layout_main_width',
        'layout_right_width',
        'layout_max_width_custom',
        'layout_padding_x_custom',
        'layout_padding_y_custom',
        'layout_gap_custom',
        'layout_left_width_custom',
        'layout_main_width_custom',
        'layout_right_width_custom',
        'dark_bg_color',
        'dark_surface_color',
        'dark_surface2_color',
        'dark_text_color',
        'dark_muted_color',
        'dark_border_color',
        'dark_primary_color',
        'global_shadow',
        'header_shadow',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public static function currentOrNull(): ?self
    {
        if (!Schema::hasTable('theme_settings')) {
            return null;
        }

        return static::current();
    }

    public static function render(string $section): string
    {
        $settings = static::currentOrNull();
        if (!$settings) {
            return '';
        }

        $map = [
            'header' => $settings->header_html,
            'left' => $settings->left_column_html,
            'right' => $settings->right_column_html,
            'home' => $settings->home_html,
            'messages' => $settings->messages_html,
            'notifications' => $settings->notifications_html,
            'categories' => $settings->categories_html,
            'tags' => $settings->tags_html,
            'profile' => $settings->profile_html,
            'index' => $settings->index_html,
            'post_show' => $settings->post_show_html,
        ];

        return (string) ($map[$section] ?? '');
    }
}
