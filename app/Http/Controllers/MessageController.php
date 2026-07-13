<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessagePreference;
use App\Models\MessageSetting;
use App\Models\MessagePin;
use App\Models\User;
use App\Notifications\MessageReceivedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $settings = MessageSetting::current();
        if (!$settings->is_enabled) {
            abort(403);
        }

        return view('messages.index', $this->buildInboxViewData($request->user(), $settings));
    }

    public function contacts(Request $request)
    {
        $settings = MessageSetting::current();
        if (!$settings->is_enabled) {
            abort(403);
        }

        return view('messages.contacts', $this->buildInboxViewData($request->user(), $settings));
    }

    public function dropdown(Request $request)
    {
        $settings = MessageSetting::current();
        if (!$settings->is_enabled) {
            return response()->json([
                'data' => [
                    'enabled' => false,
                    'items' => [],
                    'unreadCount' => 0,
                ],
            ]);
        }

        $user = $request->user();

        $pinnedIds = MessagePin::query()
            ->where('user_id', $user->id)
            ->pluck('other_user_id')
            ->all();

        $messages = Message::query()
            ->with([
                'sender:id,name,username,profile_photo_path',
                'recipient:id,name,username,profile_photo_path',
            ])
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('recipient_id', $user->id);
            })
            ->where(function ($query) use ($user) {
                $query->where(function ($inner) use ($user) {
                    $inner->where('sender_id', $user->id)->where('deleted_by_sender', false);
                })->orWhere(function ($inner) use ($user) {
                    $inner->where('recipient_id', $user->id)->where('deleted_by_recipient', false);
                });
            })
            ->orderByDesc('created_at')
            ->get();

        $unreadCounts = Message::query()
            ->selectRaw('sender_id, count(*) as cnt')
            ->where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->where('deleted_by_recipient', false)
            ->groupBy('sender_id')
            ->pluck('cnt', 'sender_id');

        $threads = [];
        foreach ($messages as $message) {
            $other = $message->sender_id === $user->id ? $message->recipient : $message->sender;
            if (!$other) {
                continue;
            }

            if (!isset($threads[$other->id])) {
                $threads[$other->id] = [
                    'user' => $other,
                    'last_message' => $message,
                    'unread' => (int) ($unreadCounts[$other->id] ?? 0),
                    'pinned' => in_array($other->id, $pinnedIds, true),
                ];
            }
        }

        $threads = array_values($threads);
        usort($threads, function ($a, $b) {
            if ($a['pinned'] !== $b['pinned']) {
                return $a['pinned'] ? -1 : 1;
            }
            return $b['last_message']->created_at <=> $a['last_message']->created_at;
        });

        $items = collect($threads)
            ->take(10)
            ->map(function ($thread) {
                $user = $thread['user'];
                $last = $thread['last_message'];
                $body = $last->body ?: ($last->attachment_path ? __('messages.thread.file') : '');
                $snippet = Str::limit((string) $body, 80);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'avatar' => $user->profile_photo_url,
                    'message' => $snippet,
                    'time' => optional($last->created_at)->diffForHumans(),
                    'unread' => (int) ($thread['unread'] ?? 0),
                    'url' => route('messages.show', $user),
                    'delete_url' => route('messages.delete', $user),
                ];
            })
            ->values();

        $totalUnread = (int) Message::query()
            ->where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->where('deleted_by_recipient', false)
            ->count();

        return response()->json([
            'data' => [
                'enabled' => true,
                'items' => $items,
                'unreadCount' => $totalUnread,
            ],
        ]);
    }

    public function show(Request $request, User $user)
    {
        $settings = MessageSetting::current();
        if (!$settings->is_enabled) {
            abort(403);
        }

        $viewer = $request->user();
        if ($viewer->id === $user->id) {
            return redirect()->route('messages.index');
        }

        $blockedFromMessages = $viewer->isBlockedFrom('messages');

        if ($viewer->hasBlocked($user) || $viewer->isBlockedBy($user)) {
            abort(403);
        }

        $preferences = MessagePreference::forUser($viewer);
        $recipientPrefs = MessagePreference::forUser($user);
        if (!$recipientPrefs->allow_messages) {
            abort(403);
        }

        $canMessage = true;
        if ($settings->allow_following_only || $recipientPrefs->allow_following_only) {
            $canMessage = $viewer->followings()->where('followed_id', $user->id)->exists();
        }
        if ($blockedFromMessages) {
            $canMessage = false;
        }

        $messages = Message::query()
            ->with(['sender:id,name,username,profile_photo_path'])
            ->where(function ($query) use ($viewer, $user) {
                $query->where('sender_id', $viewer->id)
                    ->where('recipient_id', $user->id);
            })
            ->orWhere(function ($query) use ($viewer, $user) {
                $query->where('sender_id', $user->id)
                    ->where('recipient_id', $viewer->id);
            })
            ->where(function ($query) use ($viewer) {
                $query->where(function ($inner) use ($viewer) {
                    $inner->where('sender_id', $viewer->id)->where('deleted_by_sender', false);
                })->orWhere(function ($inner) use ($viewer) {
                    $inner->where('recipient_id', $viewer->id)->where('deleted_by_recipient', false);
                });
            })
            ->orderBy('created_at')
            ->get();

        if ($messages->isEmpty() && !$canMessage) {
            abort(403);
        }

        Message::query()
            ->where('sender_id', $user->id)
            ->where('recipient_id', $viewer->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->boolean('partial') || $request->expectsJson()) {
            return response()->json([
                'html' => view('messages.partials.thread', [
                    'messages' => $messages,
                ])->render(),
                'last_id' => $messages->last()?->id,
            ]);
        }

        $inboxData = $this->buildInboxViewData($viewer, $settings);

        return view('messages.show', array_merge($inboxData, [
            'otherUser' => $user,
            'messages' => $messages,
            'canMessage' => $canMessage,
            'preferences' => $preferences,
            'blockedFromMessages' => $blockedFromMessages,
        ]));
    }

    public function store(Request $request, User $user)
    {
        $settings = MessageSetting::current();
        if (!$settings->is_enabled) {
            abort(403);
        }

        $viewer = $request->user();
        if ($viewer->id === $user->id) {
            return redirect()->route('messages.index');
        }

        if ($viewer->isBlockedFrom('messages')) {
            return back()->with('status', __('messages.permissions.viewer_blocked'));
        }

        if ($viewer->hasBlocked($user) || $viewer->isBlockedBy($user)) {
            abort(403);
        }

        $recipientPrefs = MessagePreference::forUser($user);
        if (!$recipientPrefs->allow_messages) {
            abort(403);
        }

        if ($settings->allow_following_only || $recipientPrefs->allow_following_only) {
            $isFollowing = $viewer->followings()
                ->where('followed_id', $user->id)
                ->exists();
            if (!$isFollowing) {
                abort(403);
            }
        }

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:2000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'max:20480', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,audio/mpeg,audio/mp3,audio/wav,audio/webm'],
        ]);

        $body = trim((string) ($data['body'] ?? ''));
        if ($body === '' && !$request->hasFile('attachment')) {
            return back()->withErrors(['body' => __('messages.empty_body')]);
        }

        $attachmentPath = null;
        $attachmentMime = null;
        $attachmentName = null;
        $attachmentSize = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('messages', 'public');
            $attachmentMime = $file->getMimeType();
            $attachmentName = $file->getClientOriginalName();
            $attachmentSize = $file->getSize();
        }

        $message = Message::create([
            'sender_id' => $viewer->id,
            'recipient_id' => $user->id,
            'body' => $body,
            'attachment_path' => $attachmentPath,
            'attachment_mime' => $attachmentMime,
            'attachment_name' => $attachmentName,
            'attachment_size' => $attachmentSize,
        ]);

        $user->notify(new MessageReceivedNotification($message));

        return redirect()->route('messages.show', $user);
    }

    public function settings(Request $request)
    {
        $settings = MessageSetting::current();
        if (!$settings->is_enabled) {
            abort(403);
        }

        $preferences = MessagePreference::forUser($request->user());

        return view('messages.settings', [
            'preferences' => $preferences,
            'globalSettings' => $settings,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $settings = MessageSetting::current();
        if (!$settings->is_enabled) {
            abort(403);
        }

        $preferences = MessagePreference::forUser($request->user());
        $preferences->update([
            'allow_messages' => $request->boolean('allow_messages'),
            'allow_following_only' => $request->boolean('allow_following_only'),
        ]);

        return back()->with('status', __('messages.status_updated'));
    }

    public function togglePin(Request $request, User $user)
    {
        $viewer = $request->user();
        if ($viewer->id === $user->id) {
            return redirect()->route('messages.index');
        }

        $pin = MessagePin::query()
            ->where('user_id', $viewer->id)
            ->where('other_user_id', $user->id)
            ->first();

        if ($pin) {
            $pin->delete();
        } else {
            MessagePin::create([
                'user_id' => $viewer->id,
                'other_user_id' => $user->id,
            ]);
        }

        return redirect()->route('messages.index');
    }

    public function deleteThread(Request $request, User $user)
    {
        $viewer = $request->user();
        if ($viewer->id === $user->id) {
            return redirect()->route('messages.index');
        }

        $messages = Message::query()
            ->where(function ($query) use ($viewer, $user) {
                $query->where('sender_id', $viewer->id)
                    ->where('recipient_id', $user->id);
            })
            ->orWhere(function ($query) use ($viewer, $user) {
                $query->where('sender_id', $user->id)
                    ->where('recipient_id', $viewer->id);
            })
            ->get();

        foreach ($messages as $message) {
            if ($message->attachment_path) {
                @unlink(public_path('storage/' . $message->attachment_path));
            }
        }

        Message::query()
            ->where('sender_id', $viewer->id)
            ->where('recipient_id', $user->id)
            ->update(['deleted_by_sender' => true]);

        Message::query()
            ->where('sender_id', $user->id)
            ->where('recipient_id', $viewer->id)
            ->update(['deleted_by_recipient' => true]);

        MessagePin::query()
            ->where('user_id', $viewer->id)
            ->where('other_user_id', $user->id)
            ->delete();

        return redirect()->route('messages.index');
    }

    public function deleteMessage(Request $request, Message $message)
    {
        $viewer = $request->user();
        if ($viewer->id !== $message->sender_id && $viewer->id !== $message->recipient_id) {
            abort(403);
        }

        if ($viewer->id === $message->sender_id) {
            $message->update(['deleted_by_sender' => true]);
        } else {
            $message->update(['deleted_by_recipient' => true]);
        }

        return back();
    }

    private function buildInboxViewData(User $user, MessageSetting $settings): array
    {
        $preferences = MessagePreference::forUser($user);
        $pinnedIds = MessagePin::query()
            ->where('user_id', $user->id)
            ->pluck('other_user_id')
            ->all();

        $messages = Message::query()
            ->with([
                'sender:id,name,username,profile_photo_path',
                'recipient:id,name,username,profile_photo_path',
            ])
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('recipient_id', $user->id);
            })
            ->where(function ($query) use ($user) {
                $query->where(function ($inner) use ($user) {
                    $inner->where('sender_id', $user->id)->where('deleted_by_sender', false);
                })->orWhere(function ($inner) use ($user) {
                    $inner->where('recipient_id', $user->id)->where('deleted_by_recipient', false);
                });
            })
            ->orderByDesc('created_at')
            ->get();

        $unreadCounts = Message::query()
            ->selectRaw('sender_id, count(*) as cnt')
            ->where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->groupBy('sender_id')
            ->pluck('cnt', 'sender_id');

        $threads = [];
        foreach ($messages as $message) {
            $other = $message->sender_id === $user->id ? $message->recipient : $message->sender;
            if (!$other) {
                continue;
            }

            if (!isset($threads[$other->id])) {
                $threads[$other->id] = [
                    'user' => $other,
                    'last_message' => $message,
                    'unread' => (int) ($unreadCounts[$other->id] ?? 0),
                    'pinned' => in_array($other->id, $pinnedIds, true),
                ];
            }
        }

        $threads = array_values($threads);
        usort($threads, function ($a, $b) {
            if ($a['pinned'] !== $b['pinned']) {
                return $a['pinned'] ? -1 : 1;
            }

            return $b['last_message']->created_at <=> $a['last_message']->created_at;
        });

        $threadMap = collect($threads)->keyBy(fn ($thread) => $thread['user']->id);
        $blockedFromMessages = $user->isBlockedFrom('messages');
        $followingUsers = $user->followings()
            ->select('users.id', 'users.name', 'users.username', 'users.profile_photo_path')
            ->orderBy('users.name')
            ->get();
        $followerUsers = $user->followers()
            ->select('users.id', 'users.name', 'users.username', 'users.profile_photo_path')
            ->orderBy('users.name')
            ->get();

        $contactIds = $followingUsers->pluck('id')
            ->merge($followerUsers->pluck('id'))
            ->unique()
            ->values();

        $preferenceMap = MessagePreference::query()
            ->whereIn('user_id', $contactIds)
            ->get()
            ->keyBy('user_id');

        $followingIds = $followingUsers->pluck('id')->all();
        $blockedIds = $user->blockedUsers()->pluck('users.id')->all();
        $blockedByIds = $user->blockers()->pluck('users.id')->all();

        $mapContacts = function ($contacts, string $source) use (
            $settings,
            $preferenceMap,
            $followingIds,
            $threadMap,
            $blockedIds,
            $blockedByIds,
            $blockedFromMessages
        ) {
            return $contacts->map(function ($contact) use (
                $source,
                $settings,
                $preferenceMap,
                $followingIds,
                $threadMap,
                $blockedIds,
                $blockedByIds,
                $blockedFromMessages
            ) {
                $preference = $preferenceMap->get($contact->id);
                $allowMessages = $preference ? (bool) $preference->allow_messages : true;
                $requiresFollowing = (bool) ($settings->allow_following_only || ($preference?->allow_following_only ?? true));
                $isFollowing = in_array($contact->id, $followingIds, true);
                $isBlocked = in_array($contact->id, $blockedIds, true) || in_array($contact->id, $blockedByIds, true);
                $canMessage = !$blockedFromMessages && !$isBlocked && $allowMessages && (!$requiresFollowing || $isFollowing);
                $thread = $threadMap->get($contact->id);

                return [
                    'user' => $contact,
                    'source' => $source,
                    'can_message' => $canMessage,
                    'is_blocked' => $isBlocked,
                    'viewer_blocked' => $blockedFromMessages,
                    'requires_following' => $requiresFollowing,
                    'allow_messages' => $allowMessages,
                    'thread_url' => route('messages.show', $contact),
                    'thread_exists' => (bool) $thread,
                    'unread' => (int) ($thread['unread'] ?? 0),
                    'last_message' => $thread['last_message'] ?? null,
                ];
            })->values();
        };

        return [
            'threads' => $threads,
            'preferences' => $preferences,
            'blockedFromMessages' => $blockedFromMessages,
            'followingContacts' => $mapContacts($followingUsers, 'following'),
            'followerContacts' => $mapContacts($followerUsers, 'follower'),
        ];
    }
}
