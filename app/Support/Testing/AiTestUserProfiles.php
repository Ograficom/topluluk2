<?php

namespace App\Support\Testing;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class AiTestUserProfiles
{
    /**
     * Stable personas let automated tests cover realistic account states without an LLM call.
     * The prompt can be consumed by future browser or agent test runners.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function definitions(): array
    {
        return [
            'admin' => self::profile('Ada Denetim', 'ai-test-admin@example.test', User::ROLE_ADMIN, 'Admin panelindeki kaynak, ayar ve yetki akıslarini dikkatle denetleyen yonetici.'),
            'editor' => self::profile('Deniz Editoryal', 'ai-test-editor@example.test', User::ROLE_EDITOR, 'Taslaklari inceler, baslik ve okunabilirlik odakli geri bildirim verir.'),
            'writer' => self::profile('Ece Arastirma', 'ai-test-writer@example.test', User::ROLE_WRITER, 'Kaynak isteyen, yapici yorumlar yazan ve yeni icerik ureten aktif topluluk yazari.'),
            'private-writer' => self::profile('Mert Gizlilik', 'ai-test-private@example.test', User::ROLE_WRITER, 'Gizlilik tercihlerini dikkatle kullanan, yalnizca arkadaslariyla etkilesime giren yazar.', [
                'following_visibility' => 'friends',
                'posts_visibility' => 'friends',
                'comments_visibility' => 'private',
            ]),
            'restricted-writer' => self::profile('Selin Sinirli', 'ai-test-restricted@example.test', User::ROLE_WRITER, 'Yazi okuyabilen ancak yorum ve tepkileri yonetim tarafindan kisitlanmis kullanici.', [
                'block_comments' => true,
                'block_reactions' => true,
            ]),
            'banned' => self::profile('Bora Engelli', 'ai-test-banned@example.test', User::ROLE_BANNED, 'Erisim engellerinin her ekranda dogru uygulandigini denetlemek icin kullanilan hesap.'),
        ];
    }

    /** @return Collection<int, User> */
    public static function seed(): Collection
    {
        return collect(self::definitions())
            ->map(fn (array $attributes): User => User::query()->updateOrCreate(
                ['email' => $attributes['email']],
                $attributes,
            ))
            ->values();
    }

    /** @param array<string, mixed> $overrides
     *  @return array<string, mixed>
     */
    private static function profile(string $name, string $email, string $role, string $prompt, array $overrides = []): array
    {
        $persona = str($email)->before('@')->after('ai-test-')->toString();

        return array_merge([
            'name' => $name,
            'username' => 'ai-test-' . $persona,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('testing-password'),
            'role' => $role,
            'bio' => 'Yalnizca yerel ve otomatik test senaryolari icin olusturulmus AI test kullanicisi.',
            'location' => 'Test Ortami',
            'preferred_locale' => 'tr',
            'profile_type' => 'person',
            'is_ai_test_user' => true,
            'ai_persona' => $persona,
            'ai_system_prompt' => $prompt,
        ], $overrides);
    }
}
