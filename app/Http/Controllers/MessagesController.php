<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Traits\LogsActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MessagesController extends Controller
{
    public function unreadCount()
    {
        $count = Auth::user()->totalUnreadMessages();

        return response()->json(['count' => $count]);
    }

    public function index()
    {
        $conversations = Auth::user()->conversations()
            ->with(['users', 'latestMessage.user'])
            ->get()
            ->map(function ($conversation) {
                $otherUser = $conversation->users->where('id', '!=', Auth::id())->first();
                $conversation->other_user = $otherUser;
                $conversation->unread_count = $conversation->unreadCountForUser(Auth::id());

                return $conversation;
            });

        return view('messages.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        if (! $conversation->users->contains(Auth::id())) {
            abort(403);
        }

        $conversation->markAsReadForUser(Auth::id());

        $conversation->load(['users', 'messages.user']);
        $otherUser = $conversation->users->where('id', '!=', Auth::id())->first();

        return view('messages.show', [
            'conversation' => $conversation,
            'otherUser' => $otherUser,
            'messages' => $conversation->messages()->with('user')->paginate(30),
        ]);
    }

    public function create(Request $request)
    {
        $users = collect();
        $recipient = null;

        if ($request->has('username')) {
            $recipient = User::where('username', $request->username)->first();
        }

        if ($request->has('search') && strlen($request->search) >= 2) {
            $users = User::where('id', '!=', Auth::id())
                ->where(function ($q) use ($request) {
                    $q->where('username', 'like', "%{$request->search}%")
                        ->orWhere('name', 'like', "%{$request->search}%");
                })
                ->limit(20)
                ->get();
        }

        if ($request->wantsJson() || $request->has('search')) {
            return view('messages.partials.user-search-results', compact('users'));
        }

        return view('messages.create', compact('users', 'recipient'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'body' => 'required|min:1|max:5000',
            'subject' => 'nullable|max:255',
        ]);

        $recipient = User::findOrFail($request->recipient_id);

        if ($recipient->id === Auth::id()) {
            return back()->withErrors(['recipient_id' => __('You cannot send a message to yourself.')]);
        }

        $existingConversation = Conversation::whereHas('users', function ($q) use ($recipient) {
            $q->where('user_id', Auth::id());
        })->whereHas('users', function ($q) use ($recipient) {
            $q->where('user_id', $recipient->id);
        })->first();

        if ($existingConversation) {
            $conversation = $existingConversation;
        } else {
            $conversation = Conversation::create([
                'subject' => $request->subject,
            ]);
            $conversation->users()->attach([Auth::id(), $recipient->id]);
        }

        $conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);

        $conversation->touch();

        LogsActivity::logActivity('message_sent', __(':user sent a message to :recipient', [
            'user' => Auth::user()->username,
            'recipient' => $recipient->username,
        ]));

        return redirect()->route('messages.show', $conversation)
            ->with('success', __('Message sent.'));
    }

    public function reply(Request $request, Conversation $conversation)
    {
        if (! $conversation->users->contains(Auth::id())) {
            abort(403);
        }

        $request->validate([
            'body' => 'required|min:1|max:5000',
        ]);

        $conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);

        $conversation->touch();
        $conversation->users()->updateExistingPivot(Auth::id(), [
            'last_read_at' => now(),
        ]);

        return redirect()->route('messages.show', $conversation)
            ->with('success', __('Reply sent.'));
    }

    public function destroy(Conversation $conversation)
    {
        if (! $conversation->users->contains(Auth::id())) {
            abort(403);
        }

        $conversation->users()->detach(Auth::id());

        if ($conversation->users()->count() === 0) {
            $conversation->delete();
        }

        return redirect()->route('messages.index')
            ->with('success', __('Conversation deleted.'));
    }
}
