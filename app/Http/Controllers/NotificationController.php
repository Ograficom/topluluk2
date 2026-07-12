<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Comment;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function getNotificationsBox(Request $request): mixed
    {
        $unreadNotifications = $request->user()->unreadNotifications()->count() > 0
            ? $request->user()->unreadNotifications()->latest()->take(10)->get()
            : $request->user()->notifications()->latest()->take(10)->get();

        return NotificationResource::collection($unreadNotifications);
    }

    public function index(Request $request): mixed
    {
        $notifications = $request->user()->notifications()->simplePaginate(10);

        if ($request->wantsJson()) {
            return NotificationResource::collection($notifications);
        }

        return Inertia::render('Notification/Index', [
            'notifications' => NotificationResource::collection($notifications),
        ]);
    }

    public function unreadedNotificationsCount(Request $request)
    {
        if ($request->user()->unreadNotifications()) {
            return $request->user()->unreadNotifications->count();
        }
    }

    public function markAsReadFollower($notificationId)
    {
        if (auth()->guest()) {
            return;
        }

        $notification = DatabaseNotification::findOrFail($notificationId);

        if (! $notification) {
            toast_warning(__('Notification not found'));

            return back();
        }

        $notification->markAsRead();

        $user = User::where('username', $notification->data['user']['nickname'])->first();

        if (! $user) {
            toast_warning(__('This user no longer exists!'));

            return back();
        }

        return to_route('user.show', [
            'user' => $notification->data['user']['nickname'],
        ]);
    }

    public function markAsReadComment($notificationId)
    {
        if (auth()->guest()) {
            return;
        }

        $notification = DatabaseNotification::findOrFail($notificationId);

        if (! $notification) {
            toast_warning(__('Notification not found'));

            return back();
        }

        $notification->markAsRead();

        $story = Story::find($notification->data['story']['id']);
        $comment = Comment::find($notification->data['comment']['id']);

        if (! $story) {
            toast_warning(__('This story no longer exists!'));

            return back();
        }

        if (! $comment) {
            toast_warning(__('This comment no longer exists!'));

            return back();
        }

        session()->flash('scrollToComment', $comment->id);

        return to_route('story.show', [
            'story' => $notification->data['story']['slug'],
        ])->with('scrollToComment', $comment->id);
    }

    public function markAsReadCommentReply($notificationId)
    {
        if (auth()->guest()) {
            return;
        }

        $notification = DatabaseNotification::findOrFail($notificationId);

        if (! $notification) {
            toast_warning(__('Notification not found'));

            return back();
        }

        $notification->markAsRead();

        $story = Story::find($notification->data['story']['id']);
        $comment = Comment::find($notification->data['comment']['id']);

        if (! $story) {
            toast_warning(__('This story no longer exists!'));

            return back();
        }

        if (! $comment) {
            toast_warning(__('This comment no longer exists!'));

            return back();
        }

        session()->flash('scrollToComment', $comment->id);

        return to_route('story.show', [
            'story' => $notification->data['story']['slug'],
        ])->with('scrollToComment', $comment->id);
    }

    public function markAsReadCommentMentioned($notificationId)
    {
        if (auth()->guest()) {
            return;
        }

        $notification = DatabaseNotification::findOrFail($notificationId);

        if (! $notification) {
            toast_warning(__('Notification not found'));

            return back();
        }

        $notification->markAsRead();

        $story = Story::find($notification->data['story']['id']);
        $comment = Comment::find($notification->data['comment']['id']);

        if (! $story) {
            toast_warning(__('This story no longer exists!'));

            return back();
        }

        if (! $comment) {
            toast_warning(__('This comment no longer exists!'));

            return back();
        }

        session()->flash('scrollToComment', $comment->id);

        return to_route('story.show', [
            'story' => $notification->data['story']['slug'],
        ])->with('scrollToComment', $comment->id);
    }

    public function markAsReadStoryFavorited($notificationId)
    {
        if (auth()->guest()) {
            return;
        }

        $notification = DatabaseNotification::findOrFail($notificationId);

        if (! $notification) {
            toast_warning(__('Notification not found'));

            return back();
        }

        $notification->markAsRead();

        $user = User::where('username', $notification->data['user']['nickname'])->first();

        if (! $user) {
            toast_warning(__('This user no longer exists!'));

            return back();
        }

        return to_route('user.show', [
            'user' => $notification->data['user']['nickname'],
        ]);
    }

    public function markAsReadReportedStory($notificationId)
    {
        if (auth()->guest()) {
            return;
        }

        $notification = DatabaseNotification::findOrFail($notificationId);

        if (! $notification) {
            toast_warning(__('Notification not found'));

            return back();
        }

        $notification->markAsRead();

        $story = Story::find($notification->data['story']['id']);

        if (! $story) {
            toast_warning(__('This story no longer exists!'));

            return back();
        }

        return to_route('story.show', [
            'story' => $story->slug,
        ]);
    }

    public function markAsReadReportedComment($notificationId)
    {
        if (auth()->guest()) {
            return;
        }

        $notification = DatabaseNotification::findOrFail($notificationId);

        if (! $notification) {
            toast_warning(__('Notification not found'));

            return back();
        }

        $notification->markAsRead();

        $story = Story::find($notification->data['story']['id']);
        $comment = Comment::find($notification->data['comment']['id']);

        if (! $story) {
            toast_warning(__('This story no longer exists!'));

            return back();
        }

        if (! $comment) {
            toast_warning(__('This comment no longer exists!'));

            return back();
        }

        session()->flash('scrollToComment', $comment->id);

        return to_route('story.show', [
            'story' => $notification->data['story']['slug'],
        ])->with('scrollToComment', $comment->id);
    }

    public function markAsReadReportedUser($notificationId)
    {
        if (auth()->guest()) {
            return;
        }

        $notification = DatabaseNotification::findOrFail($notificationId);

        if (! $notification) {
            toast_warning(__('Notification not found'));

            return back();
        }

        $notification->markAsRead();

        $user = User::where('username', $notification->data['reported']['nickname'])->first();

        if (! $user) {
            toast_warning(__('This user no longer exists!'));

            return back();
        }

        return to_route('user.show', [
            'user' => $user->username,
        ]);
    }

    public function markAsReadLikedStory($notificationId)
    {
        if (auth()->guest()) {
            return;
        }

        $notification = DatabaseNotification::findOrFail($notificationId);

        if (! $notification) {
            toast_warning(__('Notification not found'));

            return back();
        }

        $notification->markAsRead();

        $user = User::where('username', $notification->data['liker']['nickname'])->first();

        if (! $user) {
            toast_warning(__('This user no longer exists!'));

            return back();
        }

        return to_route('user.show', [
            'user' => $user->username,
        ]);
    }

    public function markAsReadLikedComment($notificationId)
    {
        if (auth()->guest()) {
            return;
        }

        $notification = DatabaseNotification::findOrFail($notificationId);

        if (! $notification) {
            toast_warning(__('Notification not found'));

            return back();
        }

        $notification->markAsRead();

        $user = User::where('username', $notification->data['liker']['nickname'])->first();

        if (! $user) {
            toast_warning(__('This user no longer exists!'));

            return back();
        }

        return to_route('user.show', [
            'user' => $user->username,
        ]);
    }

    public function markAsRead(Request $request)
    {
        if ($request->user()->unreadNotifications()) {
            $request->user()->unreadNotifications->markAsRead();

            return to_route('notifications.index');
        }
    }

    public function deleteAllNotifications(Request $request): RedirectResponse
    {
        if ($request->user()->notifications()->get()->isNotEmpty()) {
            $request->user()->notifications()->delete();
            toast_success(__('All notifications deleted successfully'));
        }

        return back();
    }
}
