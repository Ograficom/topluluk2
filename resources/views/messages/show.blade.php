@extends('messages.layout')
@section('title', $otherUser ? ($otherUser->name ?? $otherUser->username) : __('Conversation'))

@section('content')
<div class="mb-4">
    <a href="{{ route('messages.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('Back to Inbox') }}
    </a>
</div>

<div class="bg-white rounded-alma shadow-sm border border-gray-200 flex flex-col" style="min-height: 65vh;">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <img src="{{ $otherUser ? $otherUser->getAvatar() : asset('images/avatar_default.png') }}" alt="" class="w-10 h-10 rounded-full object-cover border border-gray-200" onerror="this.src='https://api.dicebear.com/7.x/avataaars/svg?seed=default'">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">
                    {{ $otherUser ? ($otherUser->name ?? $otherUser->username) : __('Unknown User') }}
                </h2>
                <p class="text-xs text-gray-400">@ {{ $otherUser?->username }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('messages.destroy', $conversation) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this conversation?') }}')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm text-red-500 hover:text-red-700 transition">{{ __('Delete') }}</button>
        </form>
    </div>

    <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4" id="messages-container">
        @foreach($messages->reverse() as $message)
            <div class="flex {{ $message->user_id === Auth::id() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[70%] {{ $message->user_id === Auth::id() ? 'order-1' : 'order-2' }}">
                    @if($message->user_id !== Auth::id())
                        <div class="flex items-center space-x-2 mb-1">
                            <img src="{{ $message->user->getAvatar() }}" alt="" class="w-5 h-5 rounded-full" onerror="this.src='https://api.dicebear.com/7.x/avataaars/svg?seed=default'">
                            <span class="text-xs font-medium text-gray-500">{{ $message->user->name ?? $message->user->username }}</span>
                        </div>
                    @endif
                    <div class="rounded-2xl px-4 py-2.5 text-sm {{ $message->user_id === Auth::id() ? 'bg-primary-500 text-white rounded-br-md' : 'bg-gray-100 text-gray-800 rounded-bl-md' }}">
                        {{ $message->body }}
                    </div>
                    <div class="mt-1 {{ $message->user_id === Auth::id() ? 'text-right' : 'text-left' }}">
                        <span class="text-xs text-gray-400">{{ $message->created_at->format('H:i') }}</span>
                        @if($message->user_id === Auth::id() && $message->read_at)
                            <span class="text-xs text-gray-300 ml-1">✓✓</span>
                        @elseif($message->user_id === Auth::id())
                            <span class="text-xs text-gray-300 ml-1">✓</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        @if($messages->isEmpty())
            <div class="flex items-center justify-center h-64 text-gray-400">
                <p>{{ __('No messages yet. Send the first message!') }}</p>
            </div>
        @endif
    </div>

    <div class="px-6 py-4 border-t border-gray-200">
        <form method="POST" action="{{ route('messages.reply', $conversation) }}" class="flex space-x-3">
            @csrf
            <textarea name="body" rows="2" placeholder="{{ __('Type your message...') }}" class="flex-1 resize-none border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition" required></textarea>
            <button type="submit" class="flex-shrink-0 inline-flex items-center px-5 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-xl hover:bg-primary-700 transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                {{ __('Send') }}
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
@endpush
