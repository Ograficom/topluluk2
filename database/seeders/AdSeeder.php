<?php

namespace Database\Seeders;

use App\Models\Ad;
use Illuminate\Database\Seeder;

class AdSeeder extends Seeder
{
    public function run(): void
    {
        Ad::firstOrCreate(['alias' => 'head_code'], ['position' => 'Head Code', 'code' => '', 'status' => false]);
        Ad::firstOrCreate(['alias' => 'feed_page_top'], ['position' => 'Feed Page (Top)', 'code' => '', 'status' => false]);
        Ad::firstOrCreate(['alias' => 'sidebar_sticky'], ['position' => 'Right Sidebar (Sticky)', 'code' => '', 'status' => false]);
        Ad::firstOrCreate(['alias' => 'post_page_top'], ['position' => 'Post Page (Top)', 'code' => '', 'status' => false]);
        Ad::firstOrCreate(['alias' => 'post_page_before_comments'], ['position' => 'Post Page (Before Comments)', 'code' => '', 'status' => false]);
        Ad::firstOrCreate(['alias' => 'post_page_after_comments'], ['position' => 'Post Page (After Comments)', 'code' => '', 'status' => false]);
        Ad::firstOrCreate(['alias' => 'static_page_top'], ['position' => 'Static Page (Top)', 'code' => '', 'status' => false]);
        Ad::firstOrCreate(['alias' => 'static_page_bottom'], ['position' => 'Static Page (Bottom)', 'code' => '', 'status' => false]);
    }
}
