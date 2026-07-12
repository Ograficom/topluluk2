@extends('activity-log.layout')
@section('title', __('Activity Log') . ' - ' . ($user->name ?? $user->username))

@section('content')
<div class="bg-white rounded-alma shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center space-x-3">
        <a href="{{ route('activity-log.index') }}" class="text-sm text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <img src="{{ $user->getAvatar() }}" alt="" class="w-10 h-10 rounded-full border border-gray-200">
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ __('Activity Log') }}</h1>
            <p class="text-sm text-gray-500">{{ $user->name ?? $user->username }} (@ {{ $user->username }})</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-alma shadow-sm border border-gray-200 p-6 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="w-48">
            <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Action') }}</label>
            <select name="action" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition bg-white">
                <option value="">{{ __('All Actions') }}</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ \App\Models\ActivityLog::actionLabelStatic($action) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition">
            {{ __('Filter') }}
        </button>
        @if(request('action'))
            <a href="{{ url()->current() }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition">{{ __('Clear') }}</a>
        @endif
    </form>
</div>

<div class="bg-white rounded-alma shadow-sm border border-gray-200">
    <div class="divide-y divide-gray-100">
        @forelse($logs as $log)
            <div class="px-6 py-4 flex items-start space-x-3 hover:bg-gray-50 transition">
                <div class="flex-shrink-0 mt-0.5">
                    @php
                        $iconColors = [
                            'login' => 'text-green-400',
                            'logout' => 'text-red-400',
                            'story_created' => 'text-blue-400',
                            'story_published' => 'text-blue-500',
                            'comment_created' => 'text-purple-400',
                            'kyc_submitted' => 'text-orange-400',
                            'kyc_verified' => 'text-green-500',
                            'message_sent' => 'text-indigo-400',
                        ];
                        $color = $iconColors[$log->action] ?? 'text-gray-400';
                    @endphp
                    <svg class="w-5 h-5 {{ $color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                            {{ $log->actionLabel() }}
                        </span>
                        <span class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">{{ $log->description }}</p>
                </div>
            </div>
        @empty
            <div class="px-6 py-12 text-center text-gray-400">
                {{ __('No activity logs found for this user.') }}
            </div>
        @endforelse
    </div>
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $logs->appends(request()->query())->links() }}
    </div>
</div>
@endsection
