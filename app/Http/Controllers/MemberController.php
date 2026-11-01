<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();
        $members = User::query()
            ->withCount(['posts' => fn (Builder $query) => $query->published()])
            ->when($viewer, fn (Builder $query) => $query
                ->whereNotIn('id', $viewer->blockedUsers()->select('users.id'))
                ->whereNotIn('id', $viewer->blockedByUsers()->select('users.id')))
            ->orderByDesc('posts_count')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('members.index', compact('members'));
    }
}
