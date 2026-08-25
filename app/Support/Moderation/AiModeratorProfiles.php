<?php

namespace App\Support\Moderation;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AiModeratorProfiles
{
    /** @return Collection<int, User> */
    public static function ensure(): Collection
    {
        return collect([
            [
                'name' => 'Ografi AI Spam Denetimi',
                'email' => 'ai-moderation-spam@ografi.invalid',
                'username' => 'ografi-ai-spam-denetim',
                'ai_persona' => 'spam-moderator',
                'ai_system_prompt' => 'Istenmeyen ileti, dolandiricilik, kimlik avi, zararlı baglanti ve tekrarlayan reklam risklerini incele.',
            ],
            [
                'name' => 'Ografi AI Guvenlik Denetimi',
                'email' => 'ai-moderation-safety@ografi.invalid',
                'username' => 'ografi-ai-guvenlik-denetim',
                'ai_persona' => 'safety-moderator',
                'ai_system_prompt' => 'Taciz, zorbalik, nefret, tehdit, cinsel icerik ve ciddi guvenlik risklerini incele.',
            ],
        ])->map(fn (array $profile): User => User::query()->firstOrCreate(
            ['email' => $profile['email']],
            array_merge($profile, [
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(64)),
                'role' => User::ROLE_BANNED,
                'bio' => 'Sistem tarafindan yonetilen, herkese kapali AI moderasyon hesabi.',
                'profile_type' => 'organization',
                'is_ai_test_user' => false,
            ]),
        ))->values();
    }
}
