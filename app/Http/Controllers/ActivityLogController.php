<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->action, fn ($q) => $q->where('action', $request->action))
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->search, function ($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate(50);

        $actions = ActivityLog::distinct()->pluck('action');

        return view('activity-log.index', compact('logs', 'actions'));
    }

    public function user(Request $request, User $user)
    {
        $logs = ActivityLog::where('user_id', $user->id)
            ->when($request->action, fn ($q) => $q->where('action', $request->action))
            ->latest()
            ->paginate(50);

        $actions = ActivityLog::where('user_id', $user->id)->distinct()->pluck('action');

        return view('activity-log.user', compact('logs', 'actions', 'user'));
    }

    public function myLogs(Request $request)
    {
        $logs = ActivityLog::where('user_id', Auth::id())
            ->when($request->action, fn ($q) => $q->where('action', $request->action))
            ->latest()
            ->paginate(30);

        $actions = ActivityLog::where('user_id', Auth::id())->distinct()->pluck('action');

        return view('activity-log.my', compact('logs', 'actions'));
    }
}
