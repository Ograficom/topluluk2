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
        'brand_background_color',
        'brand_surface_color',
        'brand_button_color',
        'brand_button_hover_color',
        'brand_button_text_color',
        'brand_text_color',
        'brand_font_family',
        'widget_comments_enabled',
        'widget_comments_count',
        'widget_tags_enabled',
        'widget_tags_count',
        'widget_trending_enabled',
        'widget_trending_count',
        'categories_name_color',
        'categories_stats_color',
        'categories_description_color',
        'categories_accent_color',
        'categories_hover_bg_color',
        'categories_border_color',
        'categories_avatar_size',
        'categories_name_font_size',
        'categories_stats_font_size',
        'categories_description_font_size',
        'font_heading_file',
        'font_heading_fallback',
        'font_body_file',
        'font_body_fallback',
        'font_button_file',
        'font_button_fallback',
        'font_nav_file',
        'font_nav_fallback',
        'font_code_file',
        'font_code_fallback',
        'global_text_scale',
    ];

    protected $casts = [
        'widget_comments_enabled' => 'boolean',
        'widget_tags_enabled' => 'boolean',
        'widget_trending_enabled' => 'boolean',
        'widget_comments_count' => 'integer',
        'widget_tags_count' => 'integer',
        'widget_trending_count' => 'integer',
        'categories_avatar_size' => 'integer',
        'categories_name_font_size' => 'float',
        'categories_stats_font_size' => 'float',
        'categories_description_font_size' => 'float',
        'global_text_scale' => 'integer',
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
