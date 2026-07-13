<div class="border-b border-slate-200 px-6 py-6 dark:border-slate-800">
    <div class="flex flex-col items-center text-center">
        <img class="h-24 w-24 rounded-[28px] object-cover shadow-[0_10px_30px_rgba(15,23,42,.06)]" src="{{ $otherUser->profile_photo_url }}" alt="{{ $otherUser->name }}">
        <h3 class="mt-4 text-xl font-extrabold text-slate-900 dark:text-slate-100">{{ $otherUser->name }}</h3>
        <p class="mt-1 text-sm font-semibold text-slate-400 dark:text-slate-500">{{ '@' . ($otherUser->username ?? __('messages.default_username')) }}</p>

        <div class="mt-4 flex flex-wrap justify-center gap-2">
            <span class="rounded-full bg-gray-200 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.12em] text-gray-700 dark:bg-slate-700 dark:text-slate-200">
                {{ $otherUser->roleLabel() }}
            </span>
            <span class="rounded-full {{ $messages->last()?->created_at && $messages->last()?->created_at->gt(now()->subDay()) ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }} px-3 py-1.5 text-xs font-bold uppercase tracking-[0.12em]">
                {{ $messages->last()?->created_at && $messages->last()?->created_at->gt(now()->subDay()) ? __('messages.conversation_info.active') : __('messages.conversation_info.inactive') }}
            </span>
        </div>
    </div>
</div>

<div class="message-scrollbar flex-1 overflow-y-auto px-6 py-6">
    <div class="space-y-6">
        <div class="grid grid-cols-3 gap-3">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-center dark:border-slate-800 dark:bg-slate-950">
                <p class="text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ $mediaMessages->count() }}</p>
                <p class="mt-1 text-xs font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">{{ __('messages.conversation_info.media') }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-center dark:border-slate-800 dark:bg-slate-950">
                <p class="text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ $fileMessages->count() }}</p>
                <p class="mt-1 text-xs font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">{{ __('messages.conversation_info.file') }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-center dark:border-slate-800 dark:bg-slate-950">
                <p class="text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ $linkCount }}</p>
                <p class="mt-1 text-xs font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500">{{ __('messages.conversation_info.link') }}</p>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950">
            <h4 class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">{{ __('messages.conversation_info.short_note') }}</h4>
            <p class="mt-3 text-sm leading-7 text-slate-700 dark:text-slate-300">
                {{ $otherUser->bio ?: __('messages.conversation_info.summary_fallback') }}
            </p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h4 class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">{{ __('messages.conversation_info.shared_files') }}</h4>
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500">{{ $fileMessages->count() }}</span>
            </div>

            <div class="space-y-3">
                @forelse ($fileMessages->take(3) as $fileMessage)
                    @php($fileUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($fileMessage->attachment_path))
                    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">{{ $fileMessage->attachment_name ?: __('messages.thread.file') }}</p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">
                                {{ strtoupper(\Illuminate\Support\Str::after((string) ($fileMessage->attachment_name ?? __('messages.thread.file')), '.')) ?: strtoupper(__('messages.thread.file')) }}
                                @if ($fileMessage->attachment_size)
                                    - {{ number_format($fileMessage->attachment_size / 1024, 0) }} KB
                                @endif
                            </p>
                        </div>
                        <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                            {{ __('messages.actions.open') }}
                        </a>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-6 text-center text-sm font-semibold text-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-500">
                        {{ __('messages.conversation_info.no_files') }}
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h4 class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">{{ __('messages.conversation_info.media') }}</h4>
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500">{{ $mediaMessages->count() }}</span>
            </div>

            @if ($mediaMessages->isNotEmpty())
                <div class="grid grid-cols-3 gap-3">
                    @foreach ($mediaMessages->take(6) as $mediaMessage)
                        @php($mediaUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($mediaMessage->attachment_path))
                        <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                            <img src="{{ $mediaUrl }}" alt="{{ __('messages.conversation_info.media') }}" class="h-24 w-full object-cover">
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-6 text-center text-sm font-semibold text-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-500">
                    {{ __('messages.conversation_info.no_media') }}
                </div>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('users.show', $otherUser) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                {{ __('messages.actions.view_profile') }}
            </a>

            <form method="POST" action="{{ route('messages.pin', $otherUser) }}">
                @csrf
                <button type="submit" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                    {{ $isPinned ? __('messages.actions.unpin') : __('messages.actions.pin') }}
                </button>
            </form>

            <a href="{{ route('users.report.form', $otherUser) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                {{ __('messages.actions.report') }}
            </a>

            <form method="POST" action="{{ route('users.block', $otherUser) }}">
                @csrf
                <button type="submit" class="w-full rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-600 transition hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300 dark:hover:bg-rose-950/70">
                    {{ __('messages.actions.block') }}
                </button>
            </form>

            <form method="POST" action="{{ route('messages.delete', $otherUser) }}" class="col-span-2">
                @csrf
                <button type="submit" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                    {{ __('messages.actions.delete_conversation') }}
                </button>
            </form>
        </div>
    </div>
</div>
