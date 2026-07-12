@extends('activity-log.layout')
@section('title', __('Activity Logs'))

@section('content')
<div class="bg-white rounded-alma shadow-sm border border-gray-200 p-6 mb-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ __('Activity Logs') }}</h1>
    <p class="text-gray-500 text-sm mt-1">{{ __('Monitor all user activities across the platform.') }}</p>
</div>

<div class="bg-white rounded-alma shadow-sm border border-gray-200 p-6 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Search') }}</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search in descriptions...') }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition">
        </div>
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
        @if(request()->anyFilled(['search', 'action']))
            <a href="{{ route('activity-log.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition">{{ __('Clear') }}</a>
        @endif
    </form>
</div>

<div class="bg-white rounded-alma shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('User') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Action') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Description') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('IP Address') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                @if($log->user)
                                    <img src="{{ $log->user->getAvatar() }}" alt="" class="w-7 h-7 rounded-full border border-gray-200" onerror="this.src='https://api.dicebear.com/7.x/avataaars/svg?seed=default'">
                                    <a href="{{ route('user.show', $log->user->username) }}" class="text-sm font-medium text-primary-600 hover:text-primary-700">
                                        {{ $log->user->name ?? $log->user->username }}
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400 italic">{{ __('System / Deleted User') }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $log->actionLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-600 max-w-xs truncate">{{ $log->description }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400">
                            {{ $log->created_at->format('Y-m-d H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            {{ __('No activity logs found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $logs->appends(request()->query())->links() }}
    </div>
</div>
@endsection
