<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostReport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostReportController extends Controller
{
    public function create(Request $request, Post $post)
    {
        $reporter = $request->user();
        if (!$reporter) {
            abort(403);
        }

        if ((int) $post->author_id === (int) $reporter->id) {
            abort(403);
        }

        return view('blog.posts.report', [
            'post' => $post,
            'topics' => PostReport::TOPICS,
        ]);
    }

    public function store(Request $request, Post $post)
    {
        $reporter = $request->user();
        if (!$reporter) {
            abort(403);
        }

        if ((int) $post->author_id === (int) $reporter->id) {
            abort(403);
        }

        $data = $request->validate([
            'topic' => ['required', Rule::in(array_keys(PostReport::TOPICS))],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        PostReport::create([
            'reporter_id' => $reporter->id,
            'post_id' => $post->id,
            'topic' => $data['topic'],
            'description' => $data['message'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('blog.post', $post)
            ->with('status', 'Gonderi sikayetin alindi.');
    }
}
