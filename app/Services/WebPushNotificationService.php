<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class WebPushNotificationService
{
    public static function send(User $recipient, string $category, string $title, string $body, string $url): void
    {
        $preferences = $recipient->notification_preferences ?? [];

        if (! (bool) data_get($preferences, 'push.'.$category, $category === 'security')) {
            return;
        }

        $publicKey = config('webpush.public_key');
        $privateKey = config('webpush.private_key');

        if (! filled($publicKey) || ! filled($privateKey) || ! class_exists(WebPush::class)) {
            Log::notice('Web Push is not configured; browser push was skipped.', [
                'user_id' => $recipient->id,
                'category' => $category,
            ]);

            return;
        }

        $subscriptions = PushSubscription::query()->where('user_id', $recipient->id)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => config('webpush.subject'),
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ]);

            foreach ($subscriptions as $storedSubscription) {
                $subscription = Subscription::create([
                    'endpoint' => $storedSubscription->endpoint,
                    'publicKey' => $storedSubscription->public_key,
                    'authToken' => $storedSubscription->auth_token,
                    'contentEncoding' => $storedSubscription->content_encoding ?: 'aes128gcm',
                ]);

                $webPush->queueNotification($subscription, json_encode([
                    'title' => $title,
                    'body' => $body,
                    'url' => $url,
                    'icon' => asset('assets/img/meta/logo.svg'),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }

            foreach ($webPush->flush() as $report) {
                if ($report->isSubscriptionExpired()) {
                    $endpoint = $report->getEndpoint();
                    PushSubscription::query()
                        ->where('user_id', $recipient->id)
                        ->where('endpoint', $endpoint)
                        ->delete();
                } elseif (! $report->isSuccess()) {
                    Log::warning('Web Push delivery failed.', [
                        'user_id' => $recipient->id,
                        'reason' => $report->getReason(),
                    ]);
                }
            }
        } catch (Throwable $exception) {
            Log::warning('Web Push delivery error.', [
                'user_id' => $recipient->id,
                'category' => $category,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
