<?php

namespace App\Services;

use App\Models\AccountDeletionRequest;
use App\Models\Advertisement;
use App\Models\Comment;
use App\Models\PostReport;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\SiteActivityNotification;

class AdminNotificationService
{
    public static function sendToAdmins(
        string $title,
        string $body,
        string $url,
        ?int $exceptUserId = null,
    ): void {
        User::query()
            ->where('is_admin', true)
            ->when($exceptUserId !== null, fn ($query) => $query->where('id', '<>', $exceptUserId))
            ->each(fn (User $admin) => $admin->notify(new SiteActivityNotification($title, $body, $url)));
    }

    public static function syncAllRecordsToAdmins(): int
    {
        $created = 0;

        $reports = PostReport::query()->with(['post', 'user'])->get();
        foreach ($reports as $report) {
            $created += self::sendOnce(
                'post-report:'.$report->id,
                'Yeni yazı şikayeti',
                ($report->user?->displayName() ?? 'Bir kullanıcı').' şu yazıyı şikayet etti: '.($report->post?->title ?? 'Silinmiş yazı'),
                \App\Filament\Resources\PostReports\PostReportResource::getUrl(),
            );
        }

        foreach (SupportTicket::query()->get() as $ticket) {
            $created += self::sendOnce(
                'support-ticket:'.$ticket->id,
                'Yeni destek talebi',
                $ticket->name.' yeni bir destek talebi gönderdi: '.$ticket->subject,
                \App\Filament\Resources\SupportTickets\SupportTicketResource::getUrl(),
            );
        }

        foreach (AccountDeletionRequest::query()->get() as $request) {
            $created += self::sendOnce(
                'account-deletion:'.$request->id,
                'Yeni hesap silme talebi',
                $request->user_name.' hesabının kalıcı olarak silinmesini istedi.',
                \App\Filament\Resources\AccountDeletionRequests\AccountDeletionRequestResource::getUrl(),
            );
        }

        foreach (Comment::query()->with(['user', 'post'])->get() as $comment) {
            $created += self::sendOnce(
                'comment:'.$comment->id,
                $comment->is_spam ? 'İncelenecek spam yorum' : 'Yeni yorum',
                ($comment->user?->displayName() ?? 'Bir kullanıcı').' yorum gönderdi: '.str($comment->body)->limit(100),
                \App\Filament\Resources\Comments\CommentResource::getUrl(),
            );
        }

        foreach (Advertisement::query()->with('user')->get() as $advertisement) {
            $created += self::sendOnce(
                'advertisement:'.$advertisement->id,
                'Yeni reklam başvurusu',
                ($advertisement->user?->displayName() ?? 'Bir kullanıcı').' yeni bir reklam gönderdi: '.$advertisement->title,
                \App\Filament\Resources\Advertisements\AdvertisementResource::getUrl(),
            );
        }

        return $created;
    }

    private static function sendOnce(string $sourceKey, string $title, string $body, string $url): int
    {
        $created = 0;

        User::query()->where('is_admin', true)->each(function (User $admin) use ($sourceKey, $title, $body, $url, &$created): void {
            $exists = $admin->notifications()
                ->where('data->source_key', $sourceKey)
                ->exists();

            if ($exists) {
                return;
            }

            $admin->notify(new SiteActivityNotification($title, $body, $url, $sourceKey));
            $created++;
        });

        return $created;
    }
}
