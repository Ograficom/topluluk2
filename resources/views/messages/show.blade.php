@include('messages.partials.shell-styles')

<x-app-layout>
    @section('hide_mobile_bottom_nav', '1')

    @php
        $activeThread = collect($threads)->first(fn (array $thread) => ($thread['user']->id ?? null) === $otherUser->id);
        $isPinnedThread = (bool) ($activeThread['pinned'] ?? false);
        $lastMessageAt = $messages->last()?->created_at;
        $isRecentlyActive = $lastMessageAt && $lastMessageAt->gt(now()->subMinutes(15));
        $parts = preg_split('/\s+/u', trim((string) ($otherUser->name ?? __('messages.default_name'))), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $otherInitials = collect($parts)
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1, 'UTF-8'), 'UTF-8'))
            ->implode('');
        $otherInitials = $otherInitials !== '' ? $otherInitials : __('messages.default_initial');
        $otherDisplayName = trim((string) ($otherUser->name ?? '')) !== ''
            ? trim((string) $otherUser->name)
            : ('@' . ($otherUser->username ?? __('messages.default_username')));
        $otherUsernameLabel = filled($otherUser->username ?? null) ? '@' . $otherUser->username : null;
        $conversationStatus = $isRecentlyActive
            ? __('messages.status.online')
            : ($lastMessageAt
                ? __('messages.status.last_activity', ['time' => $lastMessageAt->diffForHumans()])
                : __('messages.status.empty'));
    @endphp

    <div class="messages-page px-3 py-4 sm:px-4 sm:py-8">
        @php($themeMessages = \App\Models\ThemeSetting::render('messages'))

        <div class="site-main-shell messages-show-layout">
            <button
                type="button"
                id="openMessageSidebarBtn"
                class="messages-mobile-thread-button lg:hidden"
                aria-label="{{ __('messages.title') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
                    <path d="M8 9h8"/>
                    <path d="M8 13h5"/>
                </svg>
            </button>

            @if ($themeMessages !== '')
                <div class="mb-4">
                    {!! $themeMessages !!}
                </div>
            @endif

            @if (session('status'))
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="messages-card messages-header-card flex items-center gap-3 p-3 sm:p-4">
                @if (filled($otherUser->profile_photo_url ?? null))
                    <img class="h-11 w-11 shrink-0 rounded-full object-cover sm:h-12 sm:w-12" src="{{ $otherUser->profile_photo_url }}" alt="{{ $otherDisplayName }}">
                @else
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gray-200 text-sm font-semibold text-gray-700 sm:h-12 sm:w-12">
                        {{ $otherInitials }}
                    </div>
                @endif

                <div class="min-w-0 flex-1">
                    <h2 class="truncate text-sm font-semibold leading-tight text-gray-900 sm:text-[15px]">{{ $otherDisplayName }}</h2>
                    <p class="truncate text-xs text-gray-500">
                        @if($otherUsernameLabel)
                            {{ $otherUsernameLabel }}
                            <span class="mx-1 text-gray-300">&bull;</span>
                        @endif
                        {{ $conversationStatus }}
                    </p>
                </div>

                <div class="messages-conversation-actions">
                    <form method="POST" action="{{ route('messages.pin', $otherUser) }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                        >
                            {{ $isPinnedThread ? __('messages.actions.unpin') : __('messages.actions.pin') }}
                        </button>
                    </form>

                    @include('messages.partials.settings-dropdown', [
                        'preferences' => $preferences,
                        'buttonClass' => 'inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-100',
                    ])
                </div>
            </div>

            <div id="messageSidebarOverlay" class="messages-sidebar-overlay"></div>

            <div class="messages-shell messages-shell--show">
                @include('messages.partials.sidebar', [
                    'threads' => $threads,
                    'preferences' => $preferences,
                    'blockedFromMessages' => $blockedFromMessages,
                    'activeConversationId' => $otherUser->id,
                    'sidebarId' => 'messageSidebarPanel',
                    'mobileDrawer' => true,
                    'sidebarClasses' => '',
                ])

                <section class="messages-card messages-main-panel relative">
                    <div id="messagesContainer" class="message-scrollbar messages-thread-scroller flex-1 overflow-y-auto bg-gray-50 p-3 sm:p-4">
                        <div class="mx-auto flex w-full max-w-3xl flex-col gap-4" data-thread data-last-id="{{ $messages->last()?->id ?? 0 }}">
                            @include('messages.partials.thread', ['messages' => $messages])
                        </div>
                    </div>

                    <div class="messages-composer-dock p-3 sm:p-4">
                        @if (! $canMessage && !empty($blockedFromMessages))
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                {{ __('messages.permissions.blocked_self') }}
                            </div>
                        @elseif (! $canMessage)
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                {{ __('messages.permissions.following_only') }}
                            </div>
                        @else
                            <form method="POST" action="{{ route('messages.store', $otherUser) }}" enctype="multipart/form-data" id="messageForm">
                                @csrf

                                @if ($errors->any())
                                    <div class="mb-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                        {{ $errors->first() }}
                                    </div>
                                @endif

                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <input
                                        id="messageInput"
                                        type="text"
                                        name="body"
                                        value="{{ old('body') }}"
                                        placeholder="{{ __('messages.composer.placeholder') }}"
                                        class="w-full flex-1 rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-700 outline-none transition focus:ring-2 focus:ring-gray-300"
                                    />

                                    <div class="flex items-center justify-end gap-2.5 sm:gap-3">

                                        <label
                                            for="messageAttachmentInput"
                                            class="flex h-11 w-11 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white transition hover:bg-slate-100"
                                            aria-label="{{ __('messages.composer.upload_aria') }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.44 11.05l-8.49 8.49a5 5 0 01-7.07-7.07l9.19-9.2a3.5 3.5 0 114.95 4.96l-9.2 9.19a2 2 0 01-2.82-2.83l8.48-8.48"></path>
                                            </svg>
                                        </label>
                                        <input id="messageAttachmentInput" type="file" name="attachment" accept="image/*,video/*,audio/*" class="hidden">

                                        <button
                                            type="submit"
                                            class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-100"
                                            aria-label="{{ __('messages.actions.send') }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M22 2 11 13"/>
                                                <path d="m22 2-7 20-4-9-9-4Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="hidden text-xs text-rose-600" data-attachment-error></div>
                                </div>
                            </form>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </div>

    @include('messages.partials.sidebar-scripts')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('messageSidebarPanel');
            const overlay = document.getElementById('messageSidebarOverlay');
            const openSidebarButton = document.getElementById('openMessageSidebarBtn');
            const closeSidebarButton = sidebar?.querySelector('[data-message-sidebar-close]');
            const attachmentInput = document.getElementById('messageAttachmentInput');
            const attachmentError = document.querySelector('[data-attachment-error]');
            const messagesContainer = document.getElementById('messagesContainer');
            const thread = document.querySelector('[data-thread]');

            const syncBodyLock = () => {
                const isLocked = window.innerWidth < 1024 && sidebar?.classList.contains('is-open');
                document.documentElement.classList.toggle('overflow-hidden', Boolean(isLocked));
                document.body.classList.toggle('overflow-hidden', Boolean(isLocked));
            };

            const openSidebar = () => {
                if (!sidebar || window.innerWidth >= 1024) return;
                sidebar.classList.add('is-open');
                overlay?.classList.add('is-visible');
                syncBodyLock();
            };

            const closeSidebar = () => {
                if (!sidebar) return;
                sidebar.classList.remove('is-open');
                overlay?.classList.remove('is-visible');
                syncBodyLock();
            };

            openSidebarButton?.addEventListener('click', openSidebar);
            closeSidebarButton?.addEventListener('click', closeSidebar);
            overlay?.addEventListener('click', closeSidebar);

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    closeSidebar();
                }
            });

            attachmentInput?.addEventListener('change', () => {
                if (!attachmentError) return;

                attachmentError.classList.add('hidden');
                attachmentError.textContent = '';

                const file = attachmentInput.files?.[0];
                if (!file) return;

                if (file.type.startsWith('video/') || file.type.startsWith('audio/')) {
                    const media = document.createElement(file.type.startsWith('video/') ? 'video' : 'audio');
                    media.preload = 'metadata';
                    media.src = URL.createObjectURL(file);
                    media.onloadedmetadata = () => {
                        URL.revokeObjectURL(media.src);
                        if (media.duration > 60) {
                            attachmentError.textContent = @json(__('messages.composer.attachment_duration_error'));
                            attachmentError.classList.remove('hidden');
                            attachmentInput.value = '';
                        }
                    };
                }
            });

            const scrollToBottom = () => {
                if (!messagesContainer) return;
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            };

            scrollToBottom();

            const poll = async () => {
                if (!thread) return;

                try {
                    const response = await fetch('{{ route('messages.show', $otherUser) }}?partial=1', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });

                    if (!response.ok) return;

                    const data = await response.json();
                    const currentLastId = Number(thread.dataset.lastId || 0);
                    const nextLastId = Number(data.last_id || 0);

                    if (nextLastId !== currentLastId) {
                        thread.innerHTML = data.html;
                        thread.dataset.lastId = String(nextLastId || 0);
                        scrollToBottom();
                    }
                } catch (error) {
                    // polling errors can be ignored
                }
            };

            setInterval(poll, 4000);
        });
    </script>
</x-app-layout>
