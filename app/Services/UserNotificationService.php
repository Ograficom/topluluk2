<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SiteActivityNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class UserNotificationService
{
    public static function send(
        User $recipient,
        string $category,
        string $title,
        string $message,
        string $url,
        ?string $senderName = null,
        ?string $mailSummary = null,
    ): void {
        if (! in_array($category, ['replies', 'likes', 'posts', 'announcements', 'followers', 'messages', 'mentions', 'security'], true)) {
            throw new \InvalidArgumentException('Unknown notification category: '.$category);
        }

        $notification = new SiteActivityNotification($title, $message, $url, null, $category, $senderName, $mailSummary);
        $notification->id = (string) Str::uuid();

        try {
            $channels = $notification->via($recipient);
        } catch (Throwable $exception) {
            Log::warning('Notification channel configuration failed.', ['user_id' => $recipient->id, 'category' => $category, 'exception' => $exception->getMessage()]);
            $channels = [];
        }

        foreach ($channels as $channel) {
            try {
                Notification::sendNow($recipient, $notification, [$channel]);
            } catch (Throwable $exception) {
                Log::warning('User notification delivery failed.', ['user_id' => $recipient->id, 'category' => $category, 'channel' => $channel, 'exception' => $exception->getMessage()]);
            }
        }

        try {
            WebPushNotificationService::send($recipient, $category, $title, $message, $url);
        } catch (Throwable $exception) {
            Log::warning('Web Push configuration failed.', ['user_id' => $recipient->id, 'category' => $category, 'exception' => $exception->getMessage()]);
        }
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
