@extends('messages.layout')
@section('title', __('Inbox'))

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ __('Messages') }}</h1>
    <a href="{{ route('messages.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-alma hover:bg-primary-700 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('New Message') }}
    </a>
</div>

@if($conversations->isEmpty())
    <div class="bg-white rounded-alma shadow-sm border border-gray-200 p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <p class="text-gray-500 text-lg">{{ __('No conversations yet.') }}</p>
        <p class="text-gray-400 mt-2">{{ __('Start a new conversation to connect with other users.') }}</p>
    </div>
@else
    <div class="bg-white rounded-alma shadow-sm border border-gray-200 overflow-hidden">
        @foreach($conversations as $conversation)
            <a href="{{ route('messages.show', $conversation) }}" class="flex items-center px-6 py-4 hover:bg-gray-50 transition border-b border-gray-100 last:border-b-0 {{ $conversation->unread_count > 0 ? 'bg-blue-50' : '' }}">
                <div class="flex-shrink-0 mr-4">
                    <img src="{{ $conversation->other_user ? $conversation->other_user->getAvatar() : asset('images/avatar_default.png') }}" alt="" class="w-12 h-12 rounded-full object-cover border border-gray-200" onerror="this.src='https://api.dicebear.com/7.x/avataaars/svg?seed=default'">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start">
                        <h3 class="text-sm font-semibold text-gray-900 truncate">
                            {{ $conversation->other_user ? ($conversation->other_user->name ?? $conversation->other_user->username) : __('Unknown User') }}
                        </h3>
                        <span class="text-xs text-gray-400 flex-shrink-0 ml-2">
                            {{ $conversation->latestMessage?->created_at?->diffForHumans() ?? $conversation->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center mt-1">
                        <p class="text-sm text-gray-500 truncate">
                            @if($conversation->latestMessage)
                                @if($conversation->latestMessage->user_id === Auth::id())
                                    <span class="text-gray-400">{{ __('You:') }}</span>
                                @endif
                                {{ \Illuminate\Support\Str::limit($conversation->latestMessage->body, 80) }}
                            @else
                                <span class="text-gray-400 italic">{{ __('No messages') }}</span>
                            @endif
                        </p>
                        @if($conversation->unread_count > 0)
                            <span class="flex-shrink-0 ml-2 bg-primary-500 text-white text-xs font-bold rounded-full h-5 min-w-[20px] flex items-center justify-center px-1.5">
                                {{ $conversation->unread_count > 99 ? '99+' : $conversation->unread_count }}
                            </span>
                        @endif
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
