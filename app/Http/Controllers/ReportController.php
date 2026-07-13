<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function create(Request $request, User $user)
    {
        $reporter = $request->user();
        if (!$reporter) {
            abort(403);
        }

        $topics = [
            'Taciz',
            'Zorbalik',
            'Dolandirici',
            'Kimlik taklidi',
            'Casus veya supheli',
            'Satici',
            'Istenmeyen',
            'Olasi aktivite',
        ];

        return view('users.report', [
            'user' => $user,
            'topics' => $topics,
        ]);
    }

    public function store(Request $request, User $user)
    {
        $reporter = $request->user();
        if (!$reporter) {
            abort(403);
        }

        $topics = [
            'Taciz',
            'Zorbalik',
            'Dolandirici',
            'Kimlik taklidi',
            'Casus veya supheli',
            'Satici',
            'Istenmeyen',
            'Olasi aktivite',
        ];

        $data = $request->validate([
            'topic' => ['required', Rule::in($topics)],
            'message' => ['nullable', 'string', 'max:2000'],
            'show_username' => ['boolean'],
            'terms' => ['accepted'],
        ]);

        Report::create([
            'reporter_id' => $reporter->id,
            'reported_user_id' => $user->id,
            'topic' => $data['topic'],
            'description' => $data['message'] ?? null,
            'show_username' => $data['show_username'] ?? false,
            'status' => 'pending',
        ]);

        return back()->with('status', 'Sikayetin alindi. Tesekkurler.');
    }
}
