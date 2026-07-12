<?php

namespace Database\Seeders;

use App\Models\LicenseKey;
use Illuminate\Database\Seeder;

class LicenseKeySeeder extends Seeder
{
    public function run(): void
    {
        if (LicenseKey::count() === 0) {
            $key = LicenseKey::generate('Default license', maxDomains: 5);

            $message = "License key generated: {$key} — Save this, it won't be shown again.";
            $this->command?->info($message);
            \Illuminate\Support\Facades\Log::info($message);
        }
    }
}
