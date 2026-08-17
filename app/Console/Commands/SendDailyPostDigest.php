<?php

namespace App\Console\Commands;

use App\Mail\DailyPostDigest;
use App\Models\Post;
use App\Models\User;
use App\Support\PrivacyVisibility;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendDailyPostDigest extends Command
{
    protected $signature = 'email:send-daily-digest
        {--user= : Send only to a user ID or username}
        {--dry-run : Show eligible recipients without sending}';

    protected $description = 'Send up to 10 published public posts to daily digest subscribers';

    public function handle(): int
    {
        $posts = Post::query()
            ->where('is_published', true)
            ->where('is_nsfw', false)
            ->where(function (Builder $query): void {
                $query->whereBetween('published_at', [now()->subDay(), now()])
                    ->orWhere(function (Builder $query): void {
                        $query->whereNull('published_at')
                            ->where('created_at', '>=', now()->subDay());
                    });
            })
            ->whereHas('author', function (Builder $query): void {
                $query->where('posts_visibility', PrivacyVisibility::PUBLIC);
            })
            ->with([
                'category:id,name,slug',
                'author:id,name,username',
            ])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        if ($posts->isEmpty()) {
            $this->info('Gönderilecek uygun yayın bulunamadı.');

            return self::SUCCESS;
        }

        $recipients = User::query()
            ->where('daily_digest_enabled', true)
            ->whereNotNull('daily_digest_email')
            ->whereNotNull('daily_digest_email_verified_at')
            ->where(function (Builder $query): void {
                $query->whereNull('daily_digest_last_sent_at')
                    ->orWhere('daily_digest_last_sent_at', '<', now()->startOfDay());
            });

        if ($user = trim((string) $this->option('user'))) {
            $recipients->where(function (Builder $query) use ($user): void {
                $query->whereKey($user)->orWhere('username', $user);
            });
        }

        $sent = 0;
        $failed = 0;

        $recipients->orderBy('id')->chunkById(50, function ($users) use ($posts, &$sent, &$failed): void {
            foreach ($users as $user) {
                if ($this->option('dry-run')) {
                    $this->line('Uygun: '.$user->daily_digest_email);
                    continue;
                }

                try {
                    Mail::to($user->daily_digest_email)->send(
                        new DailyPostDigest($user, $posts)
                    );

                    $user->forceFill(['daily_digest_last_sent_at' => now()])->save();
                    $sent++;
                } catch (Throwable $exception) {
                    report($exception);
                    $failed++;
                    $this->error($user->daily_digest_email.': '.$exception->getMessage());
                }
            }
        });

        if ($this->option('dry-run')) {
            $this->info('Kuru çalışma tamamlandı; e-posta gönderilmedi.');

            return self::SUCCESS;
        }

        $this->info("Günlük özet tamamlandı. Gönderilen: {$sent}, başarısız: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
