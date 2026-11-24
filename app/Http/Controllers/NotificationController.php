<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public static function visibleFilter(\App\Models\User $notificationUser): \Closure
    {
        $visibleNotificationCategories = collect(['replies', 'likes', 'posts', 'announcements', 'followers', 'messages', 'mentions', 'security'])
           ->filter(fn (string $category): bool => (bool) data_get($notificationUser->notification_preferences ?? [], 'stream.'.$category, true))
           ->values()
           ->all();
                        return fn ($query) => $query->where(function ($items) use ($visibleNotificationCategories): void {
           $items->whereNull('data->category');
           if ($visibleNotificationCategories !== []) {
               $items->orWhereIn('data->category', $visibleNotificationCategories);
           }
                        });
    }

    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        $visible = self::visibleFilter($user);
        $headerNotifications = $user->notifications()->where($visible)->latest()->limit(30)->get();

        return response()->json([
            'unread' => $user->unreadNotifications()->where($visible)->count(),
            'html' => view('layouts._notification-items', compact('headerNotifications'))->render(),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function storePushSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => [
                'required',
                'url',
                'max:4096',
                'starts_with:https://',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $host = parse_url((string) $value, PHP_URL_HOST);
                    $allowed = in_array($host, ['fcm.googleapis.com', 'web.push.apple.com', 'wns.windows.com'], true)
                        || (is_string($host) && (str_ends_with($host, '.push.services.mozilla.com') || str_ends_with($host, '.notify.windows.com') || str_ends_with($host, '.push.apple.com')));

                    if (! $allowed) {
                        $fail('Geçersiz tarayıcı push uç noktası.');
                    }
                },
            ],
            'keys.p256dh' => ['required', 'string', 'max:512'],
            'keys.auth' => ['required', 'string', 'max:512'],
            'contentEncoding' => ['nullable', 'in:aes128gcm,aesgcm'],
        ]);

        $endpointHash = hash('sha256', $validated['endpoint']);

        PushSubscription::query()->updateOrCreate(
            ['endpoint_hash' => $endpointHash],
            [
                'user_id' => $request->user()->id,
                'endpoint' => $validated['endpoint'],
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => $validated['contentEncoding'] ?? 'aes128gcm',
            ],
        );

        return response()->json(['saved' => true]);
    }

    public function destroyPushSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url', 'max:4096'],
        ]);

        PushSubscription::query()
            ->where('user_id', $request->user()->id)
            ->where('endpoint_hash', hash('sha256', $validated['endpoint']))
            ->delete();

        return response()->json(['deleted' => true]);
    }

    public function open(string $notification): RedirectResponse
    {
        abort_unless(Str::isUuid($notification), 404);

        $item = auth()->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        $target = data_get($item->data, 'url');
        $isLocalPath = is_string($target) && str_starts_with($target, '/') && ! str_starts_with($target, '//');
        $targetHost = is_string($target) ? parse_url($target, PHP_URL_HOST) : null;
        $isSameHostUrl = is_string($targetHost) && hash_equals(request()->getHost(), $targetHost);

        if (! $isLocalPath && ! $isSameHostUrl) {
            $target = route('posts.index');
        }

        return redirect($target);
    }

    public function markAllRead(): RedirectResponse|JsonResponse
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        if (request()->expectsJson()) return response()->json(['ok' => true]);

        return back()->with('status', 'Tüm bildirimler okundu olarak işaretlendi.');
    }

    public function markAllUnread(): RedirectResponse|JsonResponse
    {
        auth()->user()->notifications()->whereNotNull('read_at')->update(['read_at' => null]);

        if (request()->expectsJson()) return response()->json(['ok' => true]);

        return back()->with('status', 'Tüm bildirimler okunmadı olarak işaretlendi.');
    }

    public function destroyAll(): RedirectResponse|JsonResponse
    {
        auth()->user()->notifications()->delete();

        if (request()->expectsJson()) return response()->json(['ok' => true]);

        return back()->with('status', 'Tüm bildirimler silindi.');
    }

    public function markRead(string $notification): RedirectResponse|JsonResponse
    {
        $this->notificationForCurrentUser($notification)->markAsRead();

        if (request()->expectsJson()) return response()->json(['ok' => true]);

        return back();
    }

    public function markUnread(string $notification): RedirectResponse|JsonResponse
    {
        $this->notificationForCurrentUser($notification)->markAsUnread();

        if (request()->expectsJson()) return response()->json(['ok' => true]);

        return back();
    }

    public function destroy(string $notification): RedirectResponse|JsonResponse
    {
        $this->notificationForCurrentUser($notification)->delete();

        if (request()->expectsJson()) return response()->json(['ok' => true]);

        return back()->with('status', 'Bildirim silindi.');
    }

    private function notificationForCurrentUser(string $notification)
    {
        abort_unless(Str::isUuid($notification), 404);

        return auth()->user()->notifications()->whereKey($notification)->firstOrFail();
    }
}
