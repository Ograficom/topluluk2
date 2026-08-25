<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\Report;
use App\Models\User;
use App\Support\Moderation\AiModeratorProfiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiLiveModerationService
{
    public function __construct(private OllamaService $ollama)
    {
    }

    /** @return array{scanned: int, flagged: int, errors: int} */
    public function scan(int $limit): array
    {
        if (! config('ai-moderation.enabled')) {
            return ['scanned' => 0, 'flagged' => 0, 'errors' => 0];
        }

        $bots = AiModeratorProfiles::ensure();
        $targets = $this->targets(max(1, $limit));
        $result = ['scanned' => 0, 'flagged' => 0, 'errors' => 0];

        foreach ($targets as $target) {
            foreach ($bots as $bot) {
                try {
                    $result['scanned']++;
                    if ($this->review($bot, $target)) {
                        $result['flagged']++;
                    }
                } catch (\Throwable $exception) {
                    $result['errors']++;
                    Log::warning('AI live moderation scan failed', [
                        'bot' => $bot->ai_persona,
                        'target' => $target::class,
                        'target_id' => $target->getKey(),
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return $result;
    }

    /** @return array<int, Model> */
    private function targets(int $limit): array
    {
        $since = now()->subDays(max(1, (int) config('ai-moderation.lookback_days', 14)));
        $botEmails = AiModeratorProfiles::ensure()->pluck('email');

        return Post::query()->where('updated_at', '>=', $since)->latest('updated_at')->limit($limit)->get()
            ->concat(Comment::withoutGlobalScopes()->where('updated_at', '>=', $since)->latest('updated_at')->limit($limit)->get())
            ->concat(User::query()->where('updated_at', '>=', $since)->where('is_ai_test_user', false)->whereNotIn('email', $botEmails)->latest('updated_at')->limit($limit)->get())
            ->sortByDesc('updated_at')->take($limit)->values()->all();
    }

    private function review(User $bot, Model $target): bool
    {
        [$type, $author, $text] = $this->targetData($target);
        if (! $author || blank($text)) {
            return false;
        }

        $hash = hash('sha256', $type . '|' . $target->getKey() . '|' . $text);
        if ($this->alreadyReviewed($bot, $target, $author, $hash)) {
            return false;
        }

        $assessment = $this->ollama->chatStructured([
            ['role' => 'system', 'content' => (string) $bot->ai_system_prompt . ' Yalnizca insan moderatorune inceleme onerisi ver; yaptirim uygulama.'],
            ['role' => 'user', 'content' => "Tur: {$type}\nIcerik:\n" . Str::limit($text, 6000, '')],
        ], [
            'type' => 'object',
            'properties' => [
                'flag' => ['type' => 'boolean'],
                'topic' => ['type' => 'string'],
                'risk_score' => ['type' => 'integer'],
                'reason' => ['type' => 'string'],
            ],
            'required' => ['flag', 'topic', 'risk_score', 'reason'],
        ]);

        if (! (bool) data_get($assessment, 'flag') || (int) data_get($assessment, 'risk_score', 0) < 60) {
            return false;
        }

        $topic = $this->topic((string) data_get($assessment, 'topic'));
        $description = sprintf('[AI-MOD:%s] Risk: %d/100. %s', $hash, min(100, max(0, (int) data_get($assessment, 'risk_score'))), Str::limit((string) data_get($assessment, 'reason'), 800, ''));

        if ($target instanceof Post) {
            PostReport::query()->create([
                'reporter_id' => $bot->id,
                'post_id' => $target->id,
                'topic' => $topic,
                'description' => $description,
                'status' => 'pending',
            ]);
        } else {
            Report::query()->create([
                'reporter_id' => $bot->id,
                'reported_user_id' => $author->id,
                'topic' => $topic,
                'description' => $description . ($target instanceof Comment ? " Yorum #{$target->id}." : ' Profil incelemesi.'),
                'show_username' => true,
                'status' => 'pending',
            ]);
        }

        if ((int) data_get($assessment, 'risk_score') >= (int) config('ai-moderation.auto_quarantine_score', 85)) {
            $this->quarantine($target, $author);
        }

        return true;
    }

    /** @return array{string, ?User, string} */
    private function targetData(Model $target): array
    {
        return match (true) {
            $target instanceof Post => ['gonderi', $target->author, trim($target->title . "\n" . $target->excerpt . "\n" . strip_tags($target->content))],
            $target instanceof Comment => ['yorum', $target->user, (string) $target->content],
            $target instanceof User => ['profil', $target, trim($target->name . "\n" . $target->username . "\n" . $target->bio . "\n" . $target->website_url)],
        };
    }

    private function alreadyReviewed(User $bot, Model $target, User $author, string $hash): bool
    {
        $marker = '[AI-MOD:' . $hash . ']';

        return $target instanceof Post
            ? PostReport::query()->where('reporter_id', $bot->id)->where('post_id', $target->id)->where('description', 'like', '%' . $marker . '%')->exists()
            : Report::query()->where('reporter_id', $bot->id)->where('reported_user_id', $author->id)->where('description', 'like', '%' . $marker . '%')->exists();
    }

    private function topic(string $candidate): string
    {
        $candidate = Str::lower($candidate);

        return match (true) {
            str_contains($candidate, 'taciz'), str_contains($candidate, 'zorbal') => 'Taciz',
            str_contains($candidate, 'cinsel') => 'Cinsel icerik',
            str_contains($candidate, 'kimlik'), str_contains($candidate, 'dolandir'), str_contains($candidate, 'phishing') => 'Kimlik avi',
            default => 'Istenmeyen',
        };
    }

    private function quarantine(Model $target, User $author): void
    {
        if ($target instanceof Post) {
            $target->update([
                'is_published' => false,
                'published_at' => null,
                'comments_disabled' => true,
            ]);

            return;
        }

        if ($target instanceof Comment) {
            $target->update(['is_approved' => false]);

            return;
        }

        $author->update([
            'block_posts' => true,
            'block_comments' => true,
            'block_reactions' => true,
        ]);
    }
}
