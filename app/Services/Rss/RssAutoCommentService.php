<?php

namespace App\Services\Rss;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Services\OllamaService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RssAutoCommentService
{
    private ?User $botUser = null;

    public function botUser(): User
    {
        if ($this->botUser !== null) {
            return $this->botUser;
        }

        $email = (string) config('ollama.bot.email', 'ai-editor@ografi.com');

        $this->botUser = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => (string) config('ollama.bot.name', 'Ografi AI'),
                'username' => (string) config('ollama.bot.username', 'ografi-ai'),
                'password' => Hash::make(Str::random(40)),
                'role' => User::ROLE_WRITER,
                'email_verified_at' => now(),
            ],
        );

        return $this->botUser;
    }

    /**
     * @param  array{title: string, summary: string, content: string}  $rewritten
     */
    public function commentOn(Post $post, array $rewritten): void
    {
        try {
            $comment = $this->generateComment($rewritten);
        } catch (\Throwable $e) {
            Log::warning('RSS otomatik yorum uretilemedi', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        if ($comment === '') {
            return;
        }

        $bot = $this->botUser();

        $post->comments()->create([
            'user_id' => $bot->id,
            'author_name' => $bot->name,
            'author_email' => $bot->email,
            'content' => $comment,
            'is_approved' => true,
        ]);
    }

    /**
     * @param  array{title: string, summary: string, content: string}  $rewritten
     */
    private function generateComment(array $rewritten): string
    {
        $plainContent = Str::limit(
            trim(html_entity_decode(strip_tags((string) ($rewritten['content'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            2000,
            '',
        );

        $prompt = <<<PROMPT
Asagidaki haberin altina, editoryal ekip adina profesyonel, kisa bir yorum yaz.
Yorum haberi ozetlemesin; konuya dair ek baglam, onem veya ilgili bir gozlem katsin.
1-3 cumle olsun, resmi ama sicak bir editoryal ton kullan. Emoji kullanma. Kufur, argo veya
noktalama isareti spam (!!!, ??? gibi) icerme. Sadece yorum metnini dondur, tirnak isareti
veya baslik ekleme.

Baslik: {$rewritten['title']}
Ozet: {$rewritten['summary']}
Icerik: {$plainContent}
PROMPT;

        $answer = app(OllamaService::class)->generate($prompt);

        $answer = trim(preg_replace('/\s+/u', ' ', $answer) ?? $answer);
        $answer = trim($answer, "\"'“”");

        return Str::limit($answer, 600, '');
    }
}
