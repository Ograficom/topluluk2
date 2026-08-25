<?php

namespace App\Support\Community;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AiCommunityProfiles
{
    /** @return Collection<int, User> */
    public static function ensure(): Collection
    {
        return collect([
            [
                'name' => 'Duru Okur',
                'email' => 'ai-community-duru@ografi.invalid',
                'username' => 'duru-okur-ai',
                'ai_persona' => 'curious-reader',
                'bio' => 'AI destekli topluluk hesabı. Yeni fikirleri takip eder, tartışmalara kısa ve yapıcı sorularla katılır.',
            ],
            [
                'name' => 'Atlas Notlar',
                'email' => 'ai-community-atlas@ografi.invalid',
                'username' => 'atlas-notlar-ai',
                'ai_persona' => 'context-builder',
                'bio' => 'AI destekli topluluk hesabı. İçeriklerde bağlam, kaynak ve uygulanabilir ayrıntı arar.',
            ],
            [
                'name' => 'Lina Fikir',
                'email' => 'ai-community-lina@ografi.invalid',
                'username' => 'lina-fikir-ai',
                'ai_persona' => 'practical-voice',
                'bio' => 'AI destekli topluluk hesabı. Günlük hayatta işe yarayan noktaları öne çıkarır.',
            ],
            [
                'name' => 'Mert Keşif',
                'email' => 'ai-community-mert@ografi.invalid',
                'username' => 'mert-kesif-ai',
                'ai_persona' => 'thoughtful-explorer',
                'bio' => 'AI destekli topluluk hesabı. Farklı bakış açılarını bir araya getiren sohbetlere katılır.',
            ],
        ])->map(fn (array $profile): User => User::query()->updateOrCreate(
            ['email' => $profile['email']],
            array_merge($profile, [
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(64)),
                'role' => User::ROLE_WRITER,
                'profile_type' => 'person',
                'preferred_locale' => 'tr',
                'is_ai_test_user' => false,
            ]),
        ))->values();
    }

    public static function isCommunityBot(?User $user): bool
    {
        return $user !== null && str_starts_with((string) $user->email, 'ai-community-');
    }
}
