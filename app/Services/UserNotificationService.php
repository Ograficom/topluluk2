<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SiteActivityNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserNotificationService
{
    public static function send(
        User $recipient,
        string $category,
        string $title,
        string $message,
        string $url,
    ): void {
        if (! in_array($category, ['replies', 'likes', 'posts', 'announcements', 'followers', 'messages', 'mentions', 'security'], true)) {
            throw new \InvalidArgumentException('Unknown notification category: '.$category);
        }

        try {
            $recipient->notify(new SiteActivityNotification($title, $message, $url, null, $category));
        } catch (Throwable $exception) {
            // A mail transport outage must not break the user's main action.
            Log::warning('User notification delivery failed.', [
                'user_id' => $recipient->id,
                'category' => $category,
                'title' => $title,
                'exception' => $exception->getMessage(),
            ]);
        }

        WebPushNotificationService::send($recipient, $category, $title, $message, $url);
    }

    /**
     * Notify users mentioned with @username in user-authored text.
     */
    public static function notifyMentions(User $sender, string $text, string $url): void
    {
        preg_match_all('/(?<![\pL\pN_])@([A-Za-z0-9_.-]{3,30})/u', $text, $matches);

        $usernames = collect($matches[1] ?? [])->map(fn (string $name) => mb_strtolower($name))->unique();

        if ($usernames->isEmpty()) {
            return;
        }

        User::query()
            ->whereIn('username', $usernames)
            ->where('id', '<>', $sender->id)
            ->get()
            ->each(fn (User $recipient) => self::send(
                $recipient,
                'mentions',
                'Senden bahsedildi',
                $sender->displayName().' bir paylaşımda senden bahsetti.',
                $url,
            ));
    }
}
