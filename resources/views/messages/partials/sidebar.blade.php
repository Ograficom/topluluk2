@php
    $activeConversationId = $activeConversationId ?? null;
    $sidebarClasses = $sidebarClasses ?? '';
    $sidebarId = $sidebarId ?? null;
    $mobileDrawer = $mobileDrawer ?? false;
@endphp

<aside
    @if ($sidebarId)
        id="{{ $sidebarId }}"
    @endif
    class="messages-card messages-sidebar-panel {{ $mobileDrawer ? 'messages-mobile-drawer' : '' }} {{ $sidebarClasses }}"
    data-message-sidebar
>
    <div class="border-b border-gray-200 dark:border-slate-700 p-4">
        @if ($mobileDrawer)
            <div class="mb-3 flex justify-end">
                <button
                    type="button"
                    data-message-sidebar-close
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 transition hover:bg-slate-100 dark:hover:bg-slate-700 lg:hidden"
                    aria-label="{{ __('messages.actions.close') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <path d="M18 6 6 18"/>
                        <path d="m6 6 12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        <div>
            <input
                type="text"
                placeholder="{{ __('messages.sidebar.search_placeholder') }}"
                data-message-search
                class="w-full rounded-xl border border-gray-300 dark:border-slate-700 dark:bg-slate-800 px-4 py-2.5 text-sm text-gray-700 dark:text-slate-200 outline-none transition focus:ring-2 focus:ring-gray-300 dark:focus:ring-slate-600"
            />
        </div>
    </div>

    <div class="message-scrollbar flex-1 overflow-y-auto">
        @if ($blockedFromMessages)
            <div class="border-b border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 px-4 py-3 text-sm text-amber-800 dark:text-amber-300">
                {{ __('messages.sidebar.blocked_notice') }}
            </div>
        @endif

        @if (count($threads) === 0)
            <div class="p-4">
                <div class="messages-empty-state px-5 py-8 text-center">
                    <p class="text-sm font-medium text-gray-600 dark:text-slate-400">{{ __('messages.sidebar.empty') }}</p>
                </div>

                @if (isset($followingContacts))
                    @php
                        $suggested = collect($followingContacts)
                            ->filter(fn ($contact) => $contact['can_message'] && !$contact['thread_exists'])
                            ->take(6);
                    @endphp

                    @if ($suggested->isNotEmpty())
                        <div class="mt-5">
                            <p class="mb-2 px-1 text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-slate-500">
                                {{ __('messages.sidebar.suggestions_title') }}
                            </p>

                            <div class="space-y-1">
                                @foreach ($suggested as $contact)
                                    @php
                                        $person = $contact['user'];
                                    @endphp
                                    <a href="{{ $contact['thread_url'] }}" class="flex items-center gap-3 rounded-xl p-2 transition hover:bg-gray-50 dark:hover:bg-slate-800">
                                        <img class="h-9 w-9 shrink-0 rounded-full object-cover" src="{{ $person->profile_photo_url }}" alt="{{ $person->name }}">
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-medium text-gray-900 dark:text-slate-100">{{ $person->name }}</span>
                                            @if ($person->username)
                                                <span class="block truncate text-xs text-gray-500 dark:text-slate-400">{{ '@' . $person->username }}</span>
                                            @endif
                                        </span>
                                        <iconify-icon icon="lucide:message-circle-plus" style="font-size: 16px; color: #9ca3af; flex-shrink: 0;"></iconify-icon>
                                    </a>
                                @endforeach
                            </div>

                            <a href="{{ route('messages.contacts') }}" class="mt-2 flex items-center justify-center gap-1.5 rounded-xl p-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 transition hover:bg-gray-50 dark:hover:bg-slate-800">
                                {{ __('messages.sidebar.suggestions_see_all') }}
                                <iconify-icon icon="lucide:arrow-right" style="font-size: 14px;"></iconify-icon>
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        @else
            <div class="divide-y divide-gray-100 dark:divide-slate-800">
                @foreach ($threads as $thread)
                    @php
                        $other = $thread['user'];
                        $snippet = trim((string) ($thread['last_message']->body ?: ($thread['last_message']->attachment_path ? __('messages.sidebar.file_shared') : '')));
                        $isActive = $activeConversationId === $other->id;
                        $otherName = trim((string) ($other->name ?? __('messages.default_name')));
                        $parts = preg_split('/\s+/u', $otherName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                        $initials = collect($parts)
                            ->take(2)
                            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8'))
                            ->implode('');
                        $initials = $initials !== '' ? $initials : __('messages.default_initial');
                        $lastCreatedAt = $thread['last_message']->created_at ?? null;
                        $timeLabel = $lastCreatedAt
                            ? ($lastCreatedAt->isToday()
                                ? $lastCreatedAt->format('H:i')
                                : ($lastCreatedAt->isYesterday() ? __('messages.status.yesterday') : $lastCreatedAt->translatedFormat('d M')))
                            : '';
                    @endphp

                    <a
                        href="{{ route('messages.show', $other) }}"
                        class="messages-thread-item {{ $isActive ? 'is-active' : '' }} block w-full p-4"
                        data-thread-item
                        data-thread-name="{{ mb_strtolower((string) $otherName) }}"
                        data-thread-username="{{ mb_strtolower((string) ($other->username ?? '')) }}"
                        data-thread-snippet="{{ mb_strtolower((string) $snippet) }}"
                        data-thread-unread="{{ $thread['unread'] > 0 ? '1' : '0' }}"
                        data-thread-pinned="{{ $thread['pinned'] ? '1' : '0' }}"
                    >
                        <div class="flex items-start gap-3">
                            @if (filled($other->profile_photo_url ?? null))
                                <img
                                    class="h-11 w-11 shrink-0 rounded-full object-cover"
                                    src="{{ $other->profile_photo_url }}"
                                    alt="{{ $otherName }}"
                                >
                            @else
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gray-200 dark:bg-slate-700 text-sm font-semibold text-gray-700 dark:text-slate-200">
                                    {{ $initials }}
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0 flex items-center gap-2">
                                        <h3 class="truncate font-semibold text-gray-900 dark:text-slate-100">{{ $otherName }}</h3>
                                        @if ($thread['unread'] > 0)
                                            <span class="inline-flex min-w-[20px] items-center justify-center rounded-full bg-gray-300 dark:bg-slate-600 px-1.5 py-0.5 text-[11px] font-semibold text-gray-700 dark:text-slate-100">
                                                {{ $thread['unread'] }}
                                            </span>
                                        @endif
                                    </div>

                                    <span class="shrink-0 text-xs text-gray-400 dark:text-slate-500">{{ $timeLabel }}</span>
                                </div>

                                <p class="mt-1 truncate text-sm text-gray-500 dark:text-slate-400">
                                    {{ \Illuminate\Support\Str::limit($snippet !== '' ? $snippet : __('messages.sidebar.file_shared'), 76) }}
                                </p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="hidden p-4" data-thread-empty>
                <div class="messages-empty-state px-5 py-8 text-center">
                    <p class="text-sm font-medium text-gray-600 dark:text-slate-400">{{ __('messages.sidebar.empty_search') }}</p>
                </div>
            </div>
        @endif
    </div>
</aside>
