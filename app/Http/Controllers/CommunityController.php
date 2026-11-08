<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Comment;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\SitePage;
use App\Models\SupportTicket;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function subscriptions(Request $request): View
    {
        $viewer = $request->user();
        $channels = $viewer->channels()->withCount('subscribers')->orderBy('name')->get();
        $authors = $viewer->following()->withCount('posts')->orderBy('name')->get();
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
        $posts = $this->postQuery($viewer)->whereIn('posts.user_id', $viewer->following()->select('users.id'))
            ->latest('published_at')->paginate(10)->withQueryString();
        $this->setViewerReactions($posts);
        return view('community.following', compact('posts'));
    }

    public function favorites(Request $request): View
    {
        $viewer = $request->user();
        $posts = $this->postQuery($viewer)->whereIn('posts.id', $viewer->bookmarks()->select('posts.id'))
            ->latest('published_at')->paginate(10)->withQueryString();
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
        abort_if($viewer->blockedUsers()->whereKey($target->id)->exists() || $viewer->blockedByUsers()->whereKey($target->id)->exists(), 403, 'Engellenmiş hesaplar arasında takip işlemi yapılamaz.');
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
        abort_if($viewer->blockedUsers()->whereKey($recipient->id)->exists() || $viewer->blockedByUsers()->whereKey($recipient->id)->exists(), 403, 'Engellenmiş hesaplara mesaj gönderilemez.');
        $conversation = Conversation::query()
            ->whereHas('participants', fn (Builder $query) => $query->where('users.id', $viewer->id))
            ->whereHas('participants', fn (Builder $query) => $query->where('users.id', $recipient->id))
            ->withCount('participants')->get()
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
            ->with(['participants' => fn ($query) => $query->where('users.id', '<>', $viewer->id), 'latestMessage.user'])
            ->withCount([
                'messages',
                'messages as unread_count' => fn ($query) => $query
                    ->where('user_id', '<>', $viewer->id)
                    ->whereNull('read_at'),
            ])
            ->orderByDesc('conversations.updated_at')
            ->paginate(20);
        $messageUsageCount = Message::query()->where('user_id', $viewer->id)->count();
        $messageUsagePercent = min(100, (int) round(($messageUsageCount / 50) * 100));
        return view('messenger.index', compact('conversations', 'messageUsageCount', 'messageUsagePercent'));
    }

    public function conversationAction(Request $request, Conversation $conversation): RedirectResponse
    {
        $viewer = $request->user();
        abort_unless($conversation->participants()->where('users.id', $viewer->id)->exists(), 404);
        $validated = $request->validate(['action' => ['required', 'in:read,unread,delete']]);

        if ($validated['action'] === 'read') {
            $conversation->messages()->where('user_id', '<>', $viewer->id)->whereNull('read_at')->update(['read_at' => now()]);
            $conversation->participants()->updateExistingPivot($viewer->id, ['last_read_at' => now()]);
            return back()->with('status', 'Konuşma okundu olarak işaretlendi.');
        }

        if ($validated['action'] === 'unread') {
            $latestIncoming = $conversation->messages()->where('user_id', '<>', $viewer->id)->latest('created_at')->first();
            if ($latestIncoming) {
                $latestIncoming->update(['read_at' => null]);
                $conversation->participants()->updateExistingPivot($viewer->id, ['last_read_at' => null]);
            }
            return back()->with('status', 'Konuşma okunmamış olarak işaretlendi.');
        }

        $conversation->participants()->detach($viewer->id);
        return redirect()->route('messenger.index')->with('status', 'Konuşma mesaj listenden kaldırıldı.');
    }

    public function showConversation(Request $request, Conversation $conversation): View
    {
        $viewer = $request->user();
        abort_unless($conversation->participants()->where('users.id', $viewer->id)->exists(), 404);
        $conversation->messages()->where('user_id', '<>', $viewer->id)->whereNull('read_at')->update(['read_at' => now()]);
        $conversation->participants()->updateExistingPivot($viewer->id, ['last_read_at' => now()]);
        $conversation->load(['participants' => fn ($query) => $query->where('users.id', '<>', $viewer->id)]);
        $other = $conversation->participants->first();
        $messages = $conversation->messages()->with('user')->paginate(50);
        $messageUsageCount = Message::query()->where('user_id', $viewer->id)->count();
        $messageUsagePercent = min(100, (int) round(($messageUsageCount / 50) * 100));
        return view('messenger.show', compact('conversation', 'other', 'messages', 'messageUsageCount', 'messageUsagePercent'));
    }

    public function sendMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        $viewer = $request->user();
        abort_unless($conversation->participants()->where('users.id', $viewer->id)->exists(), 404);
        $data=$request->validate(['body'=>['nullable','string','max:5000'],'attachment'=>['nullable','file','max:10240'],'gif_url'=>['nullable','url','max:2048']]);
        abort_if(!filled($data['body']??null)&&!$request->hasFile('attachment')&&!filled($data['gif_url']??null),422,'Mesaj, dosya veya GIF eklemelisiniz.');
        $recipient=$conversation->participants()->where('users.id','<>',$viewer->id)->first();
        abort_if($recipient&&($viewer->blockedUsers()->whereKey($recipient->id)->exists()||$viewer->blockedByUsers()->whereKey($recipient->id)->exists()),403,'Bu kullanıcıyla mesajlaşma engellenmiş.');
        $attrs=['user_id'=>$viewer->id,'body'=>trim((string)($data['body']??''))?:null];
        if($request->hasFile('attachment')){$file=$request->file('attachment');$attrs['attachment_path']=$file->store('messages','local');$attrs['attachment_name']=$file->getClientOriginalName();$attrs['attachment_mime']=$file->getMimeType();$attrs['attachment_size']=$file->getSize();}
        if(filled($data['gif_url']??null)){$host=parse_url($data['gif_url'],PHP_URL_HOST);abort_unless($host&&(str_ends_with($host,'.giphy.com')||$host==='giphy.com'),422,'Geçersiz GIF adresi.');$attrs['gif_url']=$data['gif_url'];}
        $conversation->messages()->create($attrs);$conversation->touch();return redirect()->route('messenger.show',$conversation);
    }

    public function searchGifs(Request $request)
    {
        $data=$request->validate(['q'=>['required','string','min:2','max:80']]);$key=config('services.giphy.key');abort_unless(filled($key),503,'GIF servisi yapılandırılmamış.');
        $response=Http::timeout(8)->get('https://api.giphy.com/v1/gifs/search',['api_key'=>$key,'q'=>$data['q'],'limit'=>18,'rating'=>'pg-13','lang'=>'tr']);
        if(!$response->successful())return response()->json(['results'=>[]],502);
        $results=collect($response->json('data',[]))->map(function(array $gif):?array{$preview=$gif['images']['fixed_width_small']['url']??$gif['images']['fixed_width']['url']??null;$url=$gif['images']['original']['url']??null;return $preview&&$url?['preview'=>$preview,'url'=>$url,'title'=>$gif['title']??'GIF']:null;})->filter()->values();
        return response()->json(['results'=>$results]);
    }
    public function downloadMessageAttachment(Request $request,Conversation $conversation,Message $message)
    {
        abort_unless($conversation->participants()->where('users.id',$request->user()->id)->exists(),404);abort_unless((int)$message->conversation_id===(int)$conversation->id&&filled($message->attachment_path),404);abort_unless(Storage::disk('local')->exists($message->attachment_path),404);
        return Storage::disk('local')->download($message->attachment_path,$message->attachment_name?:basename($message->attachment_path));
    }

    public function updateMessage(Request $request, Conversation $conversation, Message $message): RedirectResponse
    {
        abort_unless($conversation->participants()->where('users.id', $request->user()->id)->exists(), 404);
        abort_unless((int) $message->conversation_id === (int) $conversation->id && (int) $message->user_id === (int) $request->user()->id, 403);
        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        $message->update(['body' => trim($validated['body'])]);
        return redirect()->route('messenger.show', $conversation)->with('status', 'Mesaj güncellendi.');
    }

    public function deleteMessage(Request $request, Conversation $conversation, Message $message): RedirectResponse
    {
        abort_unless($conversation->participants()->where('users.id', $request->user()->id)->exists(), 404);
        abort_unless((int) $message->conversation_id === (int) $conversation->id && (int) $message->user_id === (int) $request->user()->id, 403);
        $message->delete();
        return redirect()->route('messenger.show', $conversation)->with('status', 'Mesaj silindi.');
    }

    public function latestPosts(Request $request): View
    {
        $posts = $this->postQuery($request->user())->latest('published_at')->paginate(15)->withQueryString();
        $this->setViewerReactions($posts);
        return view('pages.latest-posts', compact('posts'));
    }

    public function latestComments(Request $request): View
    {
        $viewer = $request->user();
        $comments = Comment::query()->with(['user', 'post', 'replies.user'])->withCount(['reactions', 'replies'])
            ->whereHas('post', fn (Builder $query) => $query->published()
                ->when(! $viewer?->show_adult_content, fn (Builder $q) => $q->where('is_adult', false))
                ->when($viewer, fn (Builder $q) => $q->whereNotIn('user_id', $viewer->blockedUsers()->select('users.id'))
                    ->whereNotIn('user_id', $viewer->blockedByUsers()->select('users.id'))))
            ->when($viewer, fn (Builder $query) => $query->withExists(['reactions as viewer_reacted' => fn (Builder $r) => $r->where('user_id', $viewer->id)])
                ->with(['replies' => fn ($replies) => $replies->withCount('reactions')
                    ->withExists(['reactions as viewer_reacted' => fn (Builder $r) => $r->where('user_id', $viewer->id)])]))
            ->whereNull('parent_id')->latest()->paginate(30)->withQueryString();
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
        Comment::query()->whereIn('id', $validated['selected_comments'])->delete();
        return back()->with('status', 'Seçilen yorumlar silindi.');
    }

    public function tags(): View
    {
        $tags = Tag::query()->withCount(['posts' => fn (Builder $query) => $query->published()])
            ->having('posts_count', '>', 0)->orderByDesc('posts_count')->limit(150)->get();
        return view('pages.tags', compact('tags'));
    }

    public function tag(Request $request, string $tag): View
    {
        $tagRecord = Tag::query()->where('slug', $tag)->orWhere('name', $tag)->firstOrFail();
        $posts = $this->postQuery($request->user())->whereHas('tagRecords', fn (Builder $query) => $query->whereKey($tagRecord->id))
            ->latest('published_at')->paginate(15)->withQueryString();
        $posts->getCollection()->each(fn (Post $post) => $post->load('tagRecords'));
        $this->setViewerReactions($posts);
        $tag = $tagRecord->name;
        return view('pages.tag', compact('tag', 'posts'));
    }

    public function page(string $slug): View
    {
        $page = SitePage::query()->published()->where('slug', $slug)->firstOrFail();
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
            'name' => [$request->user() ? 'nullable' : 'required', 'string', 'max:35'],
            'email' => [$request->user() ? 'nullable' : 'required', 'email', 'max:254'],
            'subject' => ['required', 'string', 'max:45'],
            'recipient' => ['required', 'in:support,editorial,partnerships,bug,suggestion'],
            'message' => ['required', 'string', 'max:10000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:2048', 'mimes:jpg,jpeg,png,pdf,txt,doc,docx'],
        ]);

        $departments = [
            'support' => 'Teknik destek',
            'editorial' => 'Editör ekibi',
            'partnerships' => 'İş birlikleri',
            'bug' => 'Hata bildirimi',
            'suggestion' => 'Görüş ve öneri',
        ];

        $attachmentPaths = [];
        foreach ($validated['attachments'] ?? [] as $attachment) {
            $attachmentPaths[] = $attachment->store('support-tickets', 'local');
        }

        $ticket = SupportTicket::create([
            'user_id' => $request->user()?->id,
            'name' => $request->user()?->displayName() ?? $validated['name'],
            'email' => $request->user()?->email ?? $validated['email'],
            'subject' => $validated['subject'],
            'department' => $validated['recipient'],
            'message' => trim($validated['message']),
            'attachments' => $attachmentPaths,
            'status' => SupportTicket::STATUS_NEW,
        ]);

        $supportEmail = config('mail.from.address');
        if (blank($supportEmail)) {
            return back()->with('status', 'Mesajın kaydedildi. Destek e-posta adresi yapılandırılmadığı için e-posta bildirimi gönderilemedi.');
        }

        $body = "Talep No: #{$ticket->id}\nGönderen: {$ticket->name}\nE-posta: {$ticket->email}\nBirim: {$departments[$ticket->department]}\n\n{$ticket->message}";
        try {
            Mail::raw($body, function ($mail) use ($supportEmail, $ticket, $departments, $validated): void {
                $mail->to($supportEmail)->replyTo($ticket->email, $ticket->name)
                    ->subject('[OgraFi Destek #' . $ticket->id . '] ' . $departments[$ticket->department] . ' - ' . $ticket->subject);
                foreach ($validated['attachments'] ?? [] as $attachment) {
                    $mail->attach($attachment->getRealPath(), [
                        'as' => $attachment->getClientOriginalName(),
                        'mime' => $attachment->getMimeType(),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            report($exception);
            return back()->with('status', 'Mesajın kaydedildi ancak e-posta bildirimi gönderilemedi. Destek ekibi talebini yönetim panelinden görebilir.');
        }

        return back()->with('status', 'Mesajın destek ekibine gönderildi.');
    }

    private function postQuery(?User $viewer): Builder
    {
        return Post::query()->published()->with(['user', 'channel', 'tagRecords'])
            ->with(['comments' => fn ($query) => $query->with('user')->limit(1)])
            ->withCount('comments')
            ->withCount([
                'reactions as likes_count' => fn (Builder $q) => $q->where('type', PostReaction::LIKE),
                'reactions as dislikes_count' => fn (Builder $q) => $q->where('type', PostReaction::DISLIKE),
            ])
            ->when($viewer, fn (Builder $q) => $q->whereNotIn('posts.user_id', $viewer->blockedUsers()->select('users.id'))
                ->whereNotIn('posts.user_id', $viewer->blockedByUsers()->select('users.id')))
            ->when(! $viewer?->show_adult_content, fn (Builder $q) => $q->where('posts.is_adult', false))
            ->when($viewer, fn (Builder $q) => $q->with(['reactions' => fn ($r) => $r->where('user_id', $viewer->id)]))
            ->when($viewer, fn (Builder $q) => $q->withExists(['bookmarkedBy as is_bookmarked' => fn (Builder $r) => $r->where('user_id', $viewer->id)]));
    }

    private function setViewerReactions($paginator): void
    {
        $paginator->getCollection()->each(function (Post $post): void {
            if ($post->relationLoaded('reactions')) $post->setAttribute('viewer_reaction', $post->reactions->first()?->type);
        });
    }
}
