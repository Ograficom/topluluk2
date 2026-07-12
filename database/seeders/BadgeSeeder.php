<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        Badge::firstOrCreate(['name' => 'Verified Account'], [
            'alias' => 'verified_account',
            'description' => 'The account has been verified by our team',
            'image' => 'images/badges/badge_verified_account.svg',
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => 'Exclusive Author'], [
            'alias' => 'exclusive_author',
            'description' => 'Writes exclusive posts for our community',
            'image' => 'images/badges/badge_exclusive_author.svg',
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => 'Trend Master'], [
            'alias' => 'trend_master',
            'description' => 'He had post that was trending',
            'image' => 'images/badges/badge_trend_master.svg',
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => 'Reporter'], [
            'alias' => 'reporter',
            'description' => 'He reported SPAM or copyrighted content',
            'image' => 'images/badges/badge_reporter.svg',
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => 'Bug Reporter'], [
            'alias' => 'bug_reporter',
            'description' => 'He reported one or more bugs',
            'image' => 'images/badges/badge_bug_reporter.svg',
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => '1 Year Membership'], [
            'alias' => 'membership_years',
            'description' => 'He joined a year ago',
            'image' => 'images/badges/badge_membership_1.svg',
            'membership_years' => 1,
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => '3 Year Membership'], [
            'alias' => 'membership_years',
            'description' => 'He joined 3 years ago',
            'image' => 'images/badges/badge_membership_3.svg',
            'membership_years' => 3,
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => '5 Year Membership'], [
            'alias' => 'membership_years',
            'description' => 'He joined 5 years ago',
            'image' => 'images/badges/badge_membership_5.svg',
            'membership_years' => 5,
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => 'Author level 1'], [
            'alias' => 'author_level',
            'description' => 'Made 100 or more points',
            'image' => 'images/badges/badge_level_1.svg',
            'level_id' => 1,
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => 'Author level 2'], [
            'alias' => 'author_level',
            'description' => 'Made 1000 or more points',
            'image' => 'images/badges/badge_level_2.svg',
            'level_id' => 2,
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => 'Author level 3'], [
            'alias' => 'author_level',
            'description' => 'Made 10000 or more points',
            'image' => 'images/badges/badge_level_3.svg',
            'level_id' => 3,
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => 'Author level 4'], [
            'alias' => 'author_level',
            'description' => 'Made 50000 or more points',
            'image' => 'images/badges/badge_level_4.svg',
            'level_id' => 4,
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => 'Author level 5'], [
            'alias' => 'author_level',
            'description' => 'Made 100000 or more points',
            'image' => 'images/badges/badge_level_5.svg',
            'level_id' => 5,
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => 'Author level 6'], [
            'alias' => 'author_level',
            'description' => 'Made 200000 or more points',
            'image' => 'images/badges/badge_level_6.svg',
            'level_id' => 6,
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => 'Author level 7'], [
            'alias' => 'author_level',
            'description' => 'Made 300000 or more points',
            'image' => 'images/badges/badge_level_7.svg',
            'level_id' => 7,
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => 'Author level 8'], [
            'alias' => 'author_level',
            'description' => 'Made 500000 or more points',
            'image' => 'images/badges/badge_level_8.svg',
            'level_id' => 8,
            'is_default' => true,
        ]);

        Badge::firstOrCreate(['name' => 'Author level 9'], [
            'alias' => 'author_level',
            'description' => 'Made 1000000 or more points',
            'image' => 'images/badges/badge_level_9.svg',
            'level_id' => 9,
            'is_default' => true,
        ]);
    }
}
