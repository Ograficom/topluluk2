<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PostReportController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        abort_unless($post->is_published && (! $post->published_at || $post->published_at->isPast()), 404);

        $validated = $request->validate([
            'reason' => ['required', 'in:spam,misinformation,harassment,copyright,inappropriate,other'],
            'details' => ['nullable', 'string', 'max:2000'],
        ], [
            'reason.required' => 'Lütfen bildirim nedenini seçin.',
        ]);

        $report = PostReport::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        $recipient = config('mail.from.address');
        if (filled($recipient)) {
            try {
                Mail::raw(
                    "Yeni yazı bildirimi\n\nYazı: {$post->title}\nBağlantı: ".route('posts.show', $post)
                    ."\nBildiren: ".$request->user()->displayName()
                    ."\nNeden: {$report->reason}\nAçıklama: ".($report->details ?: 'Belirtilmedi')
                    ."\nBildirim No: {$report->id}",
                    fn ($message) => $message->to($recipient)->subject('[OgraFi] Yeni yazı bildirimi')
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return back()->with('status', 'Bildirimin alındı. İnceleme için yönetime iletildi.');
    }
}
