<?php

namespace App\Http\Controllers;

use App\Events\Comment\CreatedComment;
use App\Events\Comment\CreatedCommentReply;
use App\Events\Comment\DeletedComment;
use App\Events\Comment\DeletedCommentReply;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Story;
use App\Notifications\Comment\CommentMentionNotification;
use App\Services\UploadCommentMediaService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Story $story, Request $request)
    {
        $sorting = $request->sorting ?? 'popular';
        $comments = CommentResource::collection(
            Comment::whereBelongsTo($story)
                ->with(['user', 'story', 'replies'])
                ->withTrashed()
                ->withCount(['favoriters', 'likers'])
                ->parent()
                ->when($sorting === 'popular', function ($query) {
                    return $query->orderByRaw('likers_count DESC');
                })
                ->when($sorting === 'latest', function ($query) {
                    return $query->orderByDesc('id');
                })
                ->paginate(20)
        );

        return $comments;
    }

    public function store(Story $story, Request $request)
    {
        if (auth()->guest()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:5000'],
            'media' => ['nullable', 'max:1024'],
        ]);

        $comment = $story->comments()->make([
            'comment' => $validated['body'],
        ]);

        $comment->user()->associate(auth()->user());
        $comment->save();

        foreach ($comment->mentions->users() as $user) {
            if ($user->id !== $request->user()->id) {
                $notifyEnabled = $user->notify_settings !== null && $user->notify_settings['mentions'] !== false;

                if ($notifyEnabled) {
                    $user->notify(new CommentMentionNotification($request->user(), $comment));
                }
            }
        }

        if ($request->hasFile('media')) {
            $comment = (new UploadCommentMediaService())->update($comment, $request);
            $comment->save();
        }

        event(new CreatedComment($comment));

        $comment
            ->load(['user', 'story', 'replies.user', 'replies.replies', 'replies.replies.user', 'replies.replies.replies.user'])
            ->loadCount(['favoriters']);

        return CommentResource::make($comment);
    }

    public function update(Comment $comment, Request $request)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2'],
        ]);

        $comment->update([
            'comment' => $validated['body'],
        ]);

        return $comment->comment;
    }

    public function reply(Comment $comment, Request $request)
    {
        if (auth()->guest()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:5000'],
            'media' => ['nullable', 'max:1024'],
        ]);

        $reply = $comment->replies()->make([
            'comment' => $validated['body'],
        ]);

        $reply->user()->associate(auth()->user());
        $reply->commentable()->associate($comment->commentable);
        $reply->save();

        foreach ($reply->mentions->users() as $user) {
            if ($user->id !== $request->user()->id) {
                $notifyEnabled = $user->notify_settings !== null && $user->notify_settings['mentions'] !== false;

                if ($notifyEnabled) {
                    $user->notify(new CommentMentionNotification($request->user(), $reply));
                }
            }
        }

        if ($request->hasFile('media')) {
            $reply = (new UploadCommentMediaService())->update($reply, $request);
            $reply->save();
        }

        event(new CreatedCommentReply($reply));

        $reply
            ->load(['user', 'story', 'replies.user', 'replies.replies', 'replies.replies.user', 'replies.replies.replies.user'])
            ->loadCount(['favoriters']);

        return CommentResource::make($reply);
    }

    public function destroy(Comment $comment)
    {
        // Important! All related models need to be deleted, before deleting a comment
        // $comment->likes()->where('likeable_type, '=', 'App\Models\Comment)->where('likeable_id', '=', $id)->delete();
        // $comment->favorites()->where('favoriteable_id', '=', $id)->delete();

        // $comment->replies()->delete();
        $comment->delete();

        if ($comment->isReply()) {
            event(new DeletedCommentReply($comment));
        } else {
            event(new DeletedComment($comment));
        }

        return response()->json(['success' => true, 'message' => __('Comment was deleted!')], 200);
    }
}
