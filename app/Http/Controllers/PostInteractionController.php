<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\PostReaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostInteractionController extends Controller
{
    public function react(Request $request, Post $post, string $type): RedirectResponse
    {
        abort_unless(in_array($type, [PostReaction::LIKE, PostReaction::DISLIKE], true), 404);
        $this->ensurePublished($post);

        DB::transaction(function () use ($request, $post, $type): void {
            $reaction = PostReaction::query()
                ->where('post_id', $post->id)
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->first();

            if ($reaction?->type === $type) {
                $reaction->delete();

                return;
            }

            if ($reaction) {
                $reaction->update(['type' => $type]);

                return;
            }

            PostReaction::create([
                'post_id' => $post->id,
                'user_id' => $request->user()->id,
                'type' => $type,
            ]);
        });

        return back();
    }

    public function comment(Request $request, Post $post): RedirectResponse
    {
        $this->ensurePublished($post);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        Comment::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return redirect()->route('posts.show', $post)->withFragment('comments');
    }

    private function ensurePublished(Post $post): void
    {
        abort_unless(
            $post->is_published && (! $post->published_at || $post->published_at->isPast()),
            404,
        );
    }
}
