<x-app-layout>
    <div class="py-4 sm:py-6">
        <div class="site-main-shell">
            <section class="space-y-4">
                @if (session('status'))
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="rounded-[28px] border border-slate-200 bg-white p-4 sm:p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="mb-3">
                                <a href="{{ route('messages.index') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m15 18l-6-6l6-6"/>
                                    </svg>
                                    {{ __('messages.actions.back_to_messages') }}
                                </a>
                            </div>
                            <h1 class="text-xl font-semibold text-slate-900">{{ __('messages.contacts.title') }}</h1>
                            <p class="text-sm text-slate-500">{{ __('messages.contacts.description') }}</p>
                        </div>

                        @include('messages.partials.settings-dropdown', [
                            'preferences' => $preferences,
                        ])
                    </div>
                </div>

                @include('messages.partials.contacts-stack', [
                    'followingContacts' => $followingContacts,
                    'followerContacts' => $followerContacts,
                ])
            </section>
        </div>
    </div>

</x-app-layout>
