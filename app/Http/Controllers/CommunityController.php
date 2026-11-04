<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Comment;
use App\Models\Conversation;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\SitePage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function subscriptions(Request $request): View
    {
        $viewer = $request->user();

        $channels = $viewer->channels()
            ->withCount('subscribers')
            ->orderBy('name')
            ->get();

        $authors = $viewer->following()
            ->withCount('posts')
            ->orderBy('name')
            ->get();

        return view('community.subscriptions', compact('channels', 'authors'));
    }

    public function toggleChannelSubscription(Request $request, Channel $channel): RedirectResponse
    {
        abort_unless($channel->is_active, 404);

        $subscriptions = $request->user()->channels();

        if ($subscriptions->whereKey($channel->id)->exists()) {
            $subscriptions->detach($channel->id);
            $message = 'Kanal aboneliği kaldırıldı.';
        } else {
            $subscriptions->syncWithoutDetaching([$channel->id]);
            $message = 'Kanala abone oldunuz.';
        }

        return back()->with('status', $message);
    }

    public function following(Request $request): View
    {
        $viewer = $request->user();
        $posts = $this->postQuery($viewer)
            ->whereIn('posts.user_id', $viewer->following()->select('users.id'))
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        $this->setViewerReactions($posts);

        return view('community.following', compact('posts'));
    }

    public function favorites(Request $request): View
    {
        $viewer = $request->user();
        $posts = $this->postQuery($viewer)
            ->whereIn('posts.id', $viewer->bookmarks()->select('posts.id'))
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        $this->setViewerReactions($posts);

        return view('posts.favorites', compact('posts'));
    }

    public function toggleBookmark(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->is_published && (! $post->published_at || $post->published_at->isPast()), 404);

        $bookmarks = $request->user()->bookmarks();
        if ($bookmarks->whereKey($post->id)->exists()) {
            $bookmarks->detach($post->id);
            $message = 'Yazı favorilerinden kaldırıldı.';
        } else {
            $bookmarks->syncWithoutDetaching([$post->id]);
            $message = 'Yazı favorilerine eklendi.';
        }

        return back()->with('status', $message);
    }

    public function toggleFollow(Request $request, User $target): RedirectResponse
    {
        $viewer = $request->user();
        abort_if($viewer->is($target), 422, 'Kendi hesabınızı takip edemezsiniz.');
        abort_if(
            $viewer->blockedUsers()->whereKey($target->id)->exists()
                || $viewer->blockedByUsers()->whereKey($target->id)->exists(),
            403,
            'Engellenmiş hesaplar arasında takip işlemi yapılamaz.'
        );

        if ($viewer->following()->whereKey($target->id)->exists()) {
            $viewer->following()->detach($target->id);
            $message = 'Takip bırakıldı.';
        } else {
            $viewer->following()->syncWithoutDetaching([$target->id]);
            $message = 'Kullanıcı takip ediliyor.';
        }

        return back()->with('status', $message);
    }

    public function startConversation(Request $request, User $recipient): RedirectResponse
    {
        $viewer = $request->user();
        abort_if($viewer->is($recipient), 422, 'Kendinize mesaj gönderemezsiniz.');
        abort_if(
            $viewer->blockedUsers()->whereKey($recipient->id)->exists()
                || $viewer->blockedByUsers()->whereKey($recipient->id)->exists(),
            403,
            'Engellenmiş hesaplara mesaj gönderilemez.'
        );

        $conversation = Conversation::query()
            ->whereHas('participants', fn (Builder $query) => $query->where('users.id', $viewer->id))
            ->whereHas('participants', fn (Builder $query) => $query->where('users.id', $recipient->id))
            ->withCount('participants')
            ->get()
            ->first(fn (Conversation $item): bool => (int) $item->participants_count === 2);

        if (! $conversation) {
            $conversation = Conversation::create(['created_by' => $viewer->id]);
            $conversation->participants()->attach([$viewer->id, $recipient->id]);
        }

        return redirect()->route('messenger.show', $conversation);
    }

    public function messenger(Request $request): View
    {
        $viewer = $request->user();
        $conversations = $viewer->conversations()
            ->with([
                'participants' => fn ($query) => $query->where('users.id', '<>', $viewer->id),
                'latestMessage.user',
            ])
            ->withCount('messages')
            ->orderByDesc('conversations.updated_at')
            ->paginate(20);

        return view('messenger.index', compact('conversations'));
    }

    public function showConversation(Request $request, Conversation $conversation): View
    {
        $viewer = $request->user();
        abort_unless($conversation->participants()->where('users.id', $viewer->id)->exists(), 404);

        $conversation->messages()
            ->where('user_id', '<>', $viewer->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->participants()->updateExistingPivot($viewer->id, ['last_read_at' => now()]);
        $conversation->load([
            'participants' => fn ($query) => $query->where('users.id', '<>', $viewer->id),
        ]);

        $other = $conversation->participants->first();
        $messages = $conversation->messages()->with('user')->paginate(50);

        return view('messenger.show', compact('conversation', 'other', 'messages'));
    }

    public function sendMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        $viewer = $request->user();
        abort_unless($conversation->participants()->where('users.id', $viewer->id)->exists(), 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $recipient = $conversation->participants()
            ->where('users.id', '<>', $viewer->id)
            ->first();

        abort_if(
            $recipient && (
                $viewer->blockedUsers()->whereKey($recipient->id)->exists()
                || $viewer->blockedByUsers()->whereKey($recipient->id)->exists()
            ),
            403,
            'Bu kullanıcıyla mesajlaşma engellenmiş.'
        );

        $conversation->messages()->create([
            'user_id' => $viewer->id,
            'body' => trim($validated['body']),
        ]);
        $conversation->touch();

        return redirect()->route('messenger.show', $conversation);
    }

    public function latestPosts(Request $request): View
    {
        $posts = $this->postQuery($request->user())
            ->latest('published_at')
            ->paginate(15)
            ->withQueryString();
        $this->setViewerReactions($posts);

        return view('pages.latest-posts', compact('posts'));
    }

    public function latestComments(Request $request): View
    {
        $viewer = $request->user();
        $comments = Comment::query()
            ->with(['user', 'post', 'replies.user'])
            ->withCount(['reactions', 'replies'])
            ->whereHas('post', fn (Builder $query) => $query
                ->published()
                ->when(! $viewer?->show_adult_content, fn (Builder $postQuery) => $postQuery->where('is_adult', false))
                ->when($viewer, fn (Builder $query) => $query
                    ->whereNotIn('user_id', $viewer->blockedUsers()->select('users.id'))
                    ->whereNotIn('user_id', $viewer->blockedByUsers()->select('users.id'))))
            ->when($viewer, function (Builder $query) use ($viewer): void {
                $query->withExists([
                    'reactions as viewer_reacted' => fn (Builder $reactions) => $reactions->where('user_id', $viewer->id),
                ])->with([
                    'replies' => fn ($replies) => $replies
                        ->withCount('reactions')
                        ->withExists([
                            'reactions as viewer_reacted' => fn (Builder $reactions) => $reactions->where('user_id', $viewer->id),
                        ]),
                ]);
            })
            ->whereNull('parent_id')
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('pages.latest-comments', compact('comments'));
    }

    public function moderateLatestComments(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->is_admin, 403);

        $validated = $request->validate([
            'action' => ['required', 'in:delete'],
            'selected_comments' => ['required', 'array', 'min:1', 'max:100'],
            'selected_comments.*' => ['integer', 'distinct', 'exists:comments,id'],
        ]);

        Comment::query()
            ->whereIn('id', $validated['selected_comments'])
            ->delete();

        return back()->with('status', 'Seçilen yorumlar silindi.');
    }

    public function tags(): View
    {
        $tags = Post::query()->published()->get(['tags'])
            ->flatMap(fn (Post $post) => $post->tags ?? [])
            ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
            ->countBy()
            ->sortDesc()
            ->take(150);

        return view('pages.tags', compact('tags'));
    }

    public function tag(Request $request, string $tag): View
    {
        $posts = $this->postQuery($request->user())
            ->whereJsonContains('posts.tags', $tag)
            ->latest('published_at')
            ->paginate(15)
            ->withQueryString();
        $this->setViewerReactions($posts);

        return view('pages.tag', compact('tag', 'posts'));
    }

    public function page(string $slug): View
    {
        $page = SitePage::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $page->increment('views');
        $page->refresh();

        return view('pages.show', compact('page', 'slug'));
    }

    public function contacts(): View
    {
        return view('pages.contacts', ['supportEmail' => config('mail.from.address')]);
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:35'],
            'email' => ['required', 'email', 'max:254'],
            'subject' => ['required', 'string', 'max:45'],
            'recipient' => ['required', 'in:support,editorial,partnerships'],
            'message' => ['required', 'string', 'max:10000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:2048', 'mimes:jpg,jpeg,png,pdf,txt,doc,docx'],
        ]);

        $supportEmail = config('mail.from.address');
        abort_unless(filled($supportEmail), 503, 'Destek e-posta adresi yapılandırılmamış.');

        $departments = [
            'support' => 'Teknik destek',
            'editorial' => 'Editör ekibi',
            'partnerships' => 'İş birlikleri',
        ];

        $body = "Gönderen: {$validated['name']}\n";
        $body .= "E-posta: {$validated['email']}\n";
        $body .= "Birim: {$departments[$validated['recipient']]}\n\n";
        $body .= $validated['message'];

        Mail::raw($body, function ($mail) use ($supportEmail, $validated, $departments): void {
            $mail->to($supportEmail)
                ->replyTo($validated['email'], $validated['name'])
                ->subject('[OgraFi] '.$departments[$validated['recipient']].' - '.$validated['subject']);

            foreach ($validated['attachments'] ?? [] as $attachment) {
                $mail->attach($attachment->getRealPath(), [
                    'as' => $attachment->getClientOriginalName(),
                    'mime' => $attachment->getMimeType(),
                ]);
            }
        });

        return back()->with('status', 'Mesajın destek ekibine gönderildi.');
    }

    private function postQuery(?User $viewer): Builder
    {
        return Post::query()
            ->published()
            ->with(['user', 'channel'])
            ->withCount('comments')
            ->withCount([
                'reactions as likes_count' => fn (Builder $query) => $query->where('type', PostReaction::LIKE),
                'reactions as dislikes_count' => fn (Builder $query) => $query->where('type', PostReaction::DISLIKE),
            ])
            ->when($viewer, fn (Builder $query) => $query
                ->whereNotIn('posts.user_id', $viewer->blockedUsers()->select('users.id'))
                ->whereNotIn('posts.user_id', $viewer->blockedByUsers()->select('users.id')))
            ->when(! $viewer?->show_adult_content, fn (Builder $query) => $query->where('posts.is_adult', false))
            ->when($viewer, fn (Builder $query) => $query->with([
                'reactions' => fn ($relation) => $relation->where('user_id', $viewer->id),
            ]))
            ->when($viewer, fn (Builder $query) => $query->withExists([
                'bookmarkedBy as is_bookmarked' => fn (Builder $bookmarkQuery) => $bookmarkQuery->where('user_id', $viewer->id),
            ]));
    }

    private function setViewerReactions($paginator): void
    {
        $paginator->getCollection()->each(function (Post $post): void {
            if ($post->relationLoaded('reactions')) {
                $post->setAttribute('viewer_reaction', $post->reactions->first()?->type);
            }
        });
    }
}
