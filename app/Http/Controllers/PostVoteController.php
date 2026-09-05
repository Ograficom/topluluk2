<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PostVoteController extends Controller
{
    public function summaries(Request $request): JsonResponse
    {
        if (! Schema::hasColumn('posts', 'votes_enabled') || ! Schema::hasTable('post_votes')) {
            return response()->json(['posts' => []]);
        }

        $slugs = collect($request->query('slugs', []))
            ->when(
                is_string($request->query('slugs')),
                fn ($collection) => collect(explode(',', (string) $request->query('slugs')))
            )
            ->map(fn ($slug) => trim((string) $slug))
            ->filter()
            ->unique()
            ->take(100)
            ->values();

        if ($slugs->isEmpty()) {
            return response()->json(['posts' => []]);
        }

        $posts = Post::query()
            ->published()
            ->whereIn('slug', $slugs)
            ->get(['id', 'slug', 'votes_enabled']);

        if ($posts->isEmpty()) {
            return response()->json(['posts' => []]);
        }

        $postIds = $posts->pluck('id');
        $voteRows = DB::table('post_votes')
            ->whereIn('post_id', $postIds)
            ->selectRaw('post_id, SUM(value) as score, SUM(CASE WHEN value = 1 THEN 1 ELSE 0 END) as upvotes, SUM(CASE WHEN value = -1 THEN 1 ELSE 0 END) as downvotes')
            ->groupBy('post_id')
            ->get()
            ->keyBy('post_id');

        $userVotes = collect();
        if ($request->user()) {
            $userVotes = DB::table('post_votes')
                ->whereIn('post_id', $postIds)
                ->where('user_id', $request->user()->id)
                ->pluck('value', 'post_id');
        }

        $payload = $posts->mapWithKeys(function (Post $post) use ($voteRows, $userVotes): array {
            $row = $voteRows->get($post->id);

            return [
                $post->slug => [
                    'enabled' => (bool) $post->votes_enabled,
                    'score' => (int) ($row->score ?? 0),
                    'upvotes' => (int) ($row->upvotes ?? 0),
                    'downvotes' => (int) ($row->downvotes ?? 0),
                    'user_vote' => (int) ($userVotes[$post->id] ?? 0),
                ],
            ];
        });

        return response()->json(['posts' => $payload]);
    }

    public function vote(Request $request, Post $post): JsonResponse
    {
        if (! Schema::hasColumn('posts', 'votes_enabled') || ! Schema::hasTable('post_votes')) {
            return response()->json([
                'message' => 'Oylama sistemi şu anda hazır değil.',
            ], 503);
        }

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'message' => 'Oy vermek için giriş yapmalısınız.',
                'login_url' => route('login'),
            ], 401);
        }

        $visible = Post::query()
            ->published()
            ->whereKey($post->id)
            ->exists();

        abort_unless($visible, 404);

        if (! (bool) $post->votes_enabled) {
            return response()->json([
                'message' => 'Bu gönderide oylama kapalı.',
            ], 422);
        }

        $data = $request->validate([
            'value' => ['required', 'integer', Rule::in([-1, 1])],
        ]);

        $value = (int) $data['value'];
        $userVote = DB::transaction(function () use ($post, $user, $value): int {
            $existing = DB::table('post_votes')
                ->where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existing && (int) $existing->value === $value) {
                DB::table('post_votes')->where('id', $existing->id)->delete();
                return 0;
            }

            if ($existing) {
                DB::table('post_votes')
                    ->where('id', $existing->id)
                    ->update([
                        'value' => $value,
                        'updated_at' => now(),
                    ]);

                return $value;
            }

            DB::table('post_votes')->insert([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $value;
        });

        $row = DB::table('post_votes')
            ->where('post_id', $post->id)
            ->selectRaw('COALESCE(SUM(value), 0) as score, SUM(CASE WHEN value = 1 THEN 1 ELSE 0 END) as upvotes, SUM(CASE WHEN value = -1 THEN 1 ELSE 0 END) as downvotes')
            ->first();

        return response()->json([
            'enabled' => true,
            'score' => (int) ($row->score ?? 0),
            'upvotes' => (int) ($row->upvotes ?? 0),
            'downvotes' => (int) ($row->downvotes ?? 0),
            'user_vote' => $userVote,
        ]);
    }
}
