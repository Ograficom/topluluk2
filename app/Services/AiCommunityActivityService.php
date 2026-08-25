<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\ReactionType;
use App\Models\User;
use App\Support\Community\AiCommunityProfiles;
use Illuminate\Support\Collection;

class AiCommunityActivityService
{
    /** @return array{comments: int, reactions: int, skipped: int} */
    public function engage(int $limit = 2): array
    {
        $result = ['comments' => 0, 'reactions' => 0, 'skipped' => 0];
        $bots = AiCommunityProfiles::ensure();
        $botIds = $bots->pluck('id')->all();

        if ($botIds === []) {
            return $result;
        }

        $posts = Post::query()
            ->published()
            ->where('comments_disabled', false)
            ->whereNotIn('author_id', $botIds)
            ->where('published_at', '<=', now()->subMinutes(10))
            ->where('published_at', '>=', now()->subDays(7))
            ->with('category')
            ->latest('published_at')
            ->limit(max(1, $limit) * 8)
            ->get();

        foreach ($posts as $post) {
            if (($result['comments'] + $result['reactions']) >= max(1, $limit)) {
                break;
            }

            if (Comment::withoutGlobalScopes()->where('post_id', $post->id)->whereIn('user_id', $botIds)->exists()) {
                $result['skipped']++;

                continue;
            }

            $bot = $this->nextEligibleBot($bots, $post);
            if (! $bot) {
                $result['skipped']++;

                continue;
            }

            $comment = $post->comments()->create([
                'user_id' => $bot->id,
                'author_name' => $bot->name,
                'author_email' => $bot->email,
                'content' => $this->commentFor($bot, $post),
                'is_approved' => true,
            ]);
            $result['comments']++;

            // A comment is the primary interaction; add at most one matching reaction.
            $reactionType = ReactionType::query()->where('is_active', true)->inRandomOrder()->first();
            if ($reactionType && ! $post->reactions()->where('user_id', $bot->id)->exists()) {
                $post->reactions()->create([
                    'user_id' => $bot->id,
                    'reaction_type_id' => $reactionType->id,
                ]);
                $result['reactions']++;
            }

            $comment->load('user');
        }

        return $result;
    }

    private function nextEligibleBot(Collection $bots, Post $post): ?User
    {
        return $bots
            ->sortBy(fn (User $bot) => $bot->comments()->where('created_at', '>=', now()->subHour())->count())
            ->first();
    }

    private function commentFor(User $bot, Post $post): string
    {
        $title = trim((string) $post->title);
        $subject = $title !== '' ? '“'.str($title)->limit(90, '').'”' : 'bu paylaşım';
        $category = trim((string) optional($post->category)->name);

        return match ($bot->ai_persona) {
            'curious-reader' => $subject.' için dikkat çeken nokta şu: '.($category !== '' ? $category.' bağlamında ' : '').'bu fikrin pratikte nasıl karşılık bulacağını merak ediyorum. Sizce ilk adım ne olmalı?',
            'context-builder' => $subject.' yararlı bir başlangıç sunuyor. Konuyu takip edecekler için kısa bir kaynak veya somut örnek eklenirse tartışma daha da güçlenir.',
            'practical-voice' => $subject.' içindeki yaklaşım günlük kullanımda işe yarayabilir. Küçük ölçekte deneyip sonucu paylaşmak, fikri daha uygulanabilir hale getirir.',
            default => $subject.' farklı bir bakış açısı açıyor. Katılanların kendi deneyimlerinden bir örnek eklemesi, konuyu daha zengin bir sohbete dönüştürebilir.',
        };
    }
}
