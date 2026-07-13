@include('messages.partials.shell-styles')

<x-app-layout>
    <div class="messages-page px-3 py-4 sm:px-4 sm:py-8">
        @php($themeMessages = \App\Models\ThemeSetting::render('messages'))

        <div class="site-main-shell">
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

            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-4 flex flex-col gap-3 sm:mb-6 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ __('messages.title') }}</h1>
                </div>

                <div class="messages-conversation-actions">
                    @include('messages.partials.settings-dropdown', [
                        'preferences' => $preferences,
                        'buttonClass' => 'inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-100',
                    ])
                </div>
            </div>

            <div class="messages-shell {{ count($threads) === 0 ? 'messages-shell--simple' : '' }}">
                @include('messages.partials.sidebar', [
                    'threads' => $threads,
                    'preferences' => $preferences,
                    'blockedFromMessages' => $blockedFromMessages,
                    'activeConversationId' => null,
                ])

                @if (count($threads) > 0)
                    <section class="messages-card messages-main-panel hidden lg:flex">
                        <div class="border-b border-gray-200 p-4 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gray-200 text-sm font-semibold text-gray-700 shrink-0">
                                --
                            </div>
                            <div class="min-w-0">
                                <h2 class="truncate text-base font-semibold text-gray-900 sm:text-lg">{{ __('messages.select_conversation') }}</h2>
                                <p class="text-sm text-gray-500">{{ __('messages.select_conversation_hint') }}</p>
                            </div>
                        </div>

                        <div class="flex-1 bg-gray-50 p-3 sm:p-4">
                            <div class="messages-empty-state flex h-full min-h-[360px] flex-col items-center justify-center px-6 py-10 text-center">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-200 text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/>
                                    </svg>
                                </div>

                                <h3 class="mt-4 text-lg font-semibold text-gray-900 sm:text-xl">{{ __('messages.ready_title') }}</h3>
                                <p class="mt-2 max-w-md text-sm leading-6 text-gray-500">
                                    {{ __('messages.ready_description') }}
                                </p>
                            </div>
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </div>

    @include('messages.partials.sidebar-scripts')
</x-app-layout>
