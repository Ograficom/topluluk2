<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
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
