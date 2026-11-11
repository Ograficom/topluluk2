<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
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
                    $allowed = in_array($host, ['fcm.googleapis.com', 'web.push.apple.com'], true)
                        || (is_string($host) && str_ends_with($host, '.push.services.mozilla.com'));

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
