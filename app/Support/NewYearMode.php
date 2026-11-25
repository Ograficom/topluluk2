<?php

namespace App\Support;

use App\Models\SiteSetting;
use Carbon\CarbonImmutable;

final class NewYearMode
{
    public const ZONE = 'Europe/Istanbul';

    public static function defaults(): array
    {
        return [
            'enabled' => false,
            'snow' => true,
            'lights' => true,
            'logo' => true,
            'countdown' => true,
            'confetti' => true,
            'start_date' => '2026-12-01',
            'end_date' => '2027-01-05',
            'target_year' => 2027,
        ];
    }

    public static function settings(?SiteSetting $site): array
    {
        $raw = $site?->getAttributes()['new_year_settings'] ?? null;
        $saved = is_string($raw) ? json_decode($raw, true) : $raw;

        return array_replace(self::defaults(), is_array($saved) ? array_intersect_key($saved, self::defaults()) : []);
    }

    public static function active(array $settings): bool
    {
        if (! $settings['enabled']) {
            return false;
        }

        $now = CarbonImmutable::now(self::ZONE);

        return $now->gte(CarbonImmutable::parse($settings['start_date'], self::ZONE)->startOfDay())
            && $now->lte(CarbonImmutable::parse($settings['end_date'], self::ZONE)->endOfDay());
    }

    public static function target(array $settings): CarbonImmutable
    {
        return CarbonImmutable::create((int) $settings['target_year'], 1, 1, 0, 0, 0, self::ZONE);
    }
}
