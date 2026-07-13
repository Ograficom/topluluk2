<div class="fixed inset-0 z-[9998] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm" data-message-contacts-modal aria-hidden="true">
    <div class="flex max-h-[88vh] w-full max-w-4xl flex-col overflow-hidden rounded-[30px] border border-slate-200 bg-white shadow-[0_30px_100px_-40px_rgba(15,23,42,0.6)]">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-4 sm:px-6">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">{{ __('messages.contacts.title') }}</p>
                <h3 class="truncate text-lg font-semibold text-slate-900">{{ __('messages.contacts.heading') }}</h3>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('messages.contacts') }}"
                    class="inline-flex h-10 items-center justify-center rounded-full border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                >
                    {{ __('messages.actions.open_separately') }}
                </a>
                <button
                    type="button"
                    data-message-contacts-close
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                    aria-label="{{ __('messages.actions.close') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <path d="M18 6L6 18"/>
                        <path d="M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="overflow-y-auto px-4 py-4 sm:px-6 sm:py-6">
            @include('messages.partials.contacts-stack', [
                'followingContacts' => $followingContacts,
                'followerContacts' => $followerContacts,
            ])
        </div>
    </div>
</div>
