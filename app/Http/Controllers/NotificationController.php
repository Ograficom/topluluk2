<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function pollPush(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'since' => ['required', 'date'],
        ]);

        $user = $request->user();
        $preferences = $user->notification_preferences ?? [];

        $items = $user->notifications()
            ->where('created_at', '>', \Illuminate\Support\Carbon::parse($validated['since']))
            ->oldest('created_at')
            ->limit(30)
            ->get()
            ->filter(function ($notification) use ($preferences): bool {
                $category = data_get($notification->data, 'category');

                return is_string($category)
                    && (bool) data_get($preferences, 'push.'.$category, $category === 'security');
            })
            ->values()
            ->map(function ($notification): array {
                $url = data_get($notification->data, 'url', route('posts.index'));
                if (! is_string($url) || (! str_starts_with($url, '/') && ! str_starts_with($url, url('/')))) {
                    $url = route('posts.index');
                }

                return [
                    'id' => $notification->id,
                    'title' => data_get($notification->data, 'title', 'Yeni bildirim'),
                    'body' => data_get($notification->data, 'message', data_get($notification->data, 'body', 'OgraFi hesabında yeni bir hareket var.')),
                    'url' => $url,
                ];
            });

        return response()->json([
            'notifications' => $items,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function open(string $notification): RedirectResponse
    {
        abort_unless(Str::isUuid($notification), 404);

        $item = auth()->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        $target = data_get($item->data, 'url');
        if (! is_string($target) || ! str_starts_with($target, '/') || str_starts_with($target, '//')) {
            $target = route('posts.index');
        }

        return redirect($target);
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'Tüm bildirimler okundu olarak işaretlendi.');
    }

    public function markRead(string $notification): RedirectResponse
    {
        $this->notificationForCurrentUser($notification)->markAsRead();

        return back();
    }

    public function markUnread(string $notification): RedirectResponse
    {
        $this->notificationForCurrentUser($notification)->markAsUnread();

        return back();
    }

    public function destroy(string $notification): RedirectResponse
    {
        $this->notificationForCurrentUser($notification)->delete();

        return back()->with('status', 'Bildirim silindi.');
    }

    private function notificationForCurrentUser(string $notification)
    {
        abort_unless(Str::isUuid($notification), 404);

        return auth()->user()->notifications()->whereKey($notification)->firstOrFail();
    }
}
