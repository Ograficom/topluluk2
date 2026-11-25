<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SitePageFeedbackController extends Controller
{
    public function show(Request $request, string $slug): JsonResponse
    {
        $page = $this->publishedPage($slug);

        return $this->state($page, $this->voterKey($request));
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $page = $this->publishedPage($slug);
        $data = $request->validate([
            'action' => 'required|in:vote,feedback',
            'vote' => 'required|in:like,dislike',
            'email' => 'nullable|email|max:254',
            'message' => 'required_if:action,feedback|nullable|string|max:3000',
        ], [
            'vote.required' => 'Lütfen olumlu veya olumsuz değerlendirmeyi seçin.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'message.required_if' => 'Lütfen geri bildiriminizi yazın.',
            'message.max' => 'Geri bildiriminiz en fazla 3000 karakter olabilir.',
        ]);

        $key = $this->voterKey($request);
        $now = now();
        $row = [
            'site_page_id' => $page->getKey(),
            'voter_key' => $key,
            'vote' => $data['vote'],
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $update = ['vote', 'updated_at'];

        if ($data['action'] === 'feedback') {
            $row['email'] = $data['email'] ?? null;
            $row['message'] = $data['message'];
            $row['submitted_at'] = $now;
            $update = [...$update, 'email', 'message', 'submitted_at'];
        }

        DB::table('site_page_feedback')->upsert([$row], ['site_page_id', 'voter_key'], $update);

        return $this->state($page, $key);
    }

    private function publishedPage(string $slug): SitePage
    {
        $page = SitePage::query()->published()->where('slug', $slug)->firstOrFail();
        abort_unless(Schema::hasTable('site_page_feedback'), 503, 'Geri bildirim sistemi hazırlanıyor.');

        return $page;
    }

    private function voterKey(Request $request): string
    {
        if ($request->user()) {
            $identity = 'user:'.$request->user()->getAuthIdentifier();
        } else {
            $identity = 'guest:'.$request->session()->remember('site_feedback_voter', fn () => (string) Str::uuid());
        }

        return hash_hmac('sha256', $identity, (string) config('app.key'));
    }

    private function state(SitePage $page, string $key): JsonResponse
    {
        $query = DB::table('site_page_feedback')->where('site_page_id', $page->getKey());
        $counts = (clone $query)->select('vote', DB::raw('COUNT(*) as total'))->groupBy('vote')->pluck('total', 'vote');

        return response()->json([
            'like' => (int) ($counts['like'] ?? 0),
            'dislike' => (int) ($counts['dislike'] ?? 0),
            'vote' => (clone $query)->where('voter_key', $key)->value('vote'),
        ])->header('Cache-Control', 'private, no-store');
    }
}
