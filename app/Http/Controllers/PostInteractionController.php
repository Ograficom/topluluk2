<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\User;
use App\Services\CommentSpamFilter;
use App\Notifications\SiteActivityNotification;
use App\Services\AdminNotificationService;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
            'body' => ['required_without:images', 'nullable', 'string', 'max:5000'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        $parent = null;

        if (filled($validated['parent_id'] ?? null)) {
            $parent = Comment::query()
                ->where('post_id', $post->id)
                ->whereNull('parent_id')
                ->find($validated['parent_id']);

            if (! $parent) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Yanıt verilecek yorum bu yazıda bulunamadı.',
                ]);
            }
        }

        $isSpam = app(CommentSpamFilter::class)->shouldFlag($request->user()->id, $validated['body'] ?? '');

        $imagePaths = [];

        foreach ($request->file('images', []) as $image) {
            $path = $image->store('comments', 'public');

            if (! $path) {
                throw new \RuntimeException('The comment image could not be stored.');
            }

            $imagePaths[] = $path;
        }

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'parent_id' => $parent?->id,
            'body' => $validated['body'] ?? '',
            'images' => $imagePaths,
            'is_spam' => $isSpam,
        ]);

        AdminNotificationService::sendToAdmins(
            $isSpam ? 'İncelenecek spam yorum' : 'Yeni yorum',
            $request->user()->displayName().' yorum gönderdi: '.str($validated['body'] ?? 'Görsel içeren yorum')->limit(100),
            \App\Filament\Resources\Comments\CommentResource::getUrl(),
        );

        if (! $isSpam) {
            UserNotificationService::notifyMentions(
                $request->user(),
                (string) ($validated['body'] ?? ''),
                route('posts.show', $post).'#comment-'.$comment->id,
            );

            $recipient = $parent?->user ?? $post->user;
            if ($recipient && ! $recipient->is($request->user())) {
                UserNotificationService::send(
                    $recipient,
                    'replies',
                    $parent ? 'Yorumuna yanıt geldi' : 'Yazına yeni yorum',
                    $request->user()->displayName().' yorum yaptı: '.str($validated['body'] ?? 'Bir görsel paylaştı')->limit(100),
                    route('posts.show', $post).'#comment-'.$comment->id,
                );
            }
        }

        return redirect()->route('posts.show', $post)
            ->withFragment('comment-'.$comment->id)
            ->with('status', $isSpam ? 'Yorumun spam filtresine takıldı ve yönetici incelemesine alındı.' : 'Yorumun yayınlandı.');
    }

    public function moderateComments(Request $request, Post $post): RedirectResponse
    {
        abort_unless($request->user()->is_admin, 403);
        $this->ensurePublished($post);

        $validated = $request->validate([
            'action' => ['required', 'in:delete'],
            'selected_comments' => ['required', 'array', 'min:1', 'max:100'],
            'selected_comments.*' => ['required', 'integer', 'distinct', 'exists:comments,id'],
        ]);
        $selectedIds = array_map('intval', $validated['selected_comments']);
        $selectedComments = $post->comments()->whereIn('id', $selectedIds)->get();

        if ($selectedComments->count() !== count($selectedIds)) {
            throw ValidationException::withMessages([
                'selected_comments' => 'Seçilen yorumlardan biri bu yazıda bulunamadı.',
            ]);
        }

        $commentIds = array_merge(
            $selectedIds,
            Comment::query()->whereIn('parent_id', $selectedIds)->pluck('id')->all(),
        );
        $imagePaths = Comment::query()
            ->whereIn('id', $commentIds)
            ->get()
            ->flatMap(fn (Comment $comment): array => $comment->images ?? [])
            ->filter(fn (string $path): bool => Storage::disk('public')->exists($path))
            ->all();

        DB::transaction(fn () => $post->comments()->whereIn('id', $selectedIds)->delete());

        if ($imagePaths !== [] && ! Storage::disk('public')->delete($imagePaths)) {
            throw new \RuntimeException('One or more deleted comment images could not be removed.');
        }

        return redirect()->route('posts.show', $post)->withFragment('comments')->with('status', 'Seçilen yorumlar silindi.');
    }

    public function updateComment(Request $request, Post $post, Comment $comment): RedirectResponse
    {
        $this->ensurePublished($post);
        abort_unless($comment->post_id === $post->id, 404);
        abort_unless($request->user()->is($comment->user) || $request->user()->is_admin, 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $comment->update(['body' => $validated['body']]);

        return redirect()
            ->route('posts.show', $post)
            ->withFragment('comment-'.$comment->id)
            ->with('status', 'Yorum güncellendi.');
    }

    public function reactToComment(Request $request, Post $post, Comment $comment): RedirectResponse
    {
        $this->ensurePublished($post);
        abort_unless($comment->post_id === $post->id, 404);

        DB::transaction(function () use ($request, $comment): void {
            $reaction = CommentReaction::query()
                ->where('comment_id', $comment->id)
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->first();

            if ($reaction) {
                $reaction->delete();

                return;
            }

            CommentReaction::create([
                'comment_id' => $comment->id,
                'user_id' => $request->user()->id,
            ]);
        });

        return back();
    }

    private function ensurePublished(Post $post): void
    {
        abort_unless(
            $post->is_published && (! $post->published_at || $post->published_at->isPast()),
            404,
        );
    }
}
