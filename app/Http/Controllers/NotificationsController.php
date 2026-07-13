<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->paginate(20);
        $notifications->setCollection(
            $this->presentNotifications($notifications->getCollection())
        );

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function dropdown(Request $request)
    {
        $user = $request->user();
        $items = $this->presentNotifications(
            $user->notifications()
            ->latest()
            ->take(10)
            ->get()
        )
            ->map(function ($notification) {
                return [
                    'id' => $notification['id'],
                    'title' => $notification['title'],
                    'message' => $notification['preview'],
                    'type' => $notification['type'],
                    'url' => $notification['read_url'],
                    'delete_url' => $notification['delete_url'],
                    'time' => $notification['time_human'],
                    'unread' => $notification['is_unread'],
                    'name' => $notification['actor_name'],
                    'avatar' => $notification['actor_avatar'],
                    'action_text' => $notification['action_text'],
                    'subject' => $notification['subject'],
                    'preview' => $notification['preview'],
                ];
            })
            ->values();

        return response()->json([
            'data' => [
                'items' => $items,
                'unreadCount' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    public function read(Request $request, string $notificationId)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $url = Arr::get($notification->data, 'url');
        if ($url) {
            return redirect($url);
        }

        return redirect()->route('notifications.index');
    }

    public function markAll(Request $request)
    {
        $request->user()->unreadNotifications()->update([
            'read_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
            ]);
        }

        return redirect()->route('notifications.index');
    }

    public function destroy(Request $request, string $notificationId)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->delete();

        return back();
    }

    public function destroyAll(Request $request)
    {
        $request->user()->notifications()->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'unreadCount' => 0,
            ]);
        }

        return redirect()->route('notifications.index');
    }

    private function presentNotifications(Collection $notifications): Collection
    {
        $actorIds = $notifications
            ->map(fn ($notification) => (int) Arr::get($notification->data ?? [], 'actor_id', 0))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $actors = User::query()
            ->whereIn('id', $actorIds)
            ->get(['id', 'name', 'username', 'profile_photo_path'])
            ->keyBy('id');

        return $notifications->map(
            fn (DatabaseNotification $notification) => $this->presentNotification($notification, $actors)
        );
    }

    private function presentNotification(DatabaseNotification $notification, Collection $actors): array
    {
        $data = $notification->data ?? [];
        $type = (string) (Arr::get($data, 'type') ?: 'general');
        $actorId = (int) Arr::get($data, 'actor_id', 0);
        $actor = $actors->get($actorId);
        $actorName = (string) (Arr::get($data, 'actor_name') ?: $actor?->name ?: 'Bir kullanici');
        $subject = (string) (Arr::get($data, 'post_title') ?: Arr::get($data, 'category_name') ?: '');
        $body = trim((string) Arr::get($data, 'body', ''));
        $preview = $this->notificationPreview($type, $body, $actorName, $subject);

        return [
            'id' => $notification->id,
            'type' => $type,
            'title' => (string) (Arr::get($data, 'title') ?: 'Bildirim'),
            'actor_name' => $actorName,
            'actor_avatar' => $actor?->profile_photo_url,
            'subject' => $subject,
            'action_text' => $this->notificationActionText($type),
            'preview' => $preview,
            'is_unread' => is_null($notification->read_at),
            'time_human' => optional($notification->created_at)->diffForHumans(),
            'read_url' => route('notifications.read', $notification->id),
            'delete_url' => route('notifications.delete', $notification->id),
        ];
    }

    private function notificationActionText(string $type): string
    {
        return match ($type) {
            'message_received' => 'sana mesaj gonderdi',
            'post_comment' => 'gonderine yorum yapti',
            'post_reaction' => 'gonderine tepki verdi',
            'post_repost' => 'gonderini yeniden paylasti',
            'user_mention' => 'senden bahsetti',
            'category_post' => 'takip ettigin kategoride yeni post paylasti',
            'user_followed' => 'seni takip etmeye basladi',
            default => 'bir bildirim birakti',
        };
    }

    private function notificationPreview(string $type, string $body, string $actorName, string $subject): string
    {
        $preview = $body;

        if ($actorName !== '' && Str::startsWith($preview, $actorName . ':')) {
            $preview = trim((string) Str::after($preview, $actorName . ':'));
        }

        if ($subject !== '' && Str::contains($preview, $subject)) {
            $preview = trim((string) Str::replaceFirst($subject, '', $preview));
        }

        if ($preview === '') {
            return $subject;
        }

        return Str::limit(trim($preview, " \t\n\r\0\x0B-:"), 180);
    }
}
