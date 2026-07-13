<div class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm" data-message-settings-modal aria-hidden="true">
    <div class="flex w-full max-w-xl flex-col overflow-hidden rounded-3xl bg-white/95 shadow-[0_24px_80px_-30px_rgba(15,23,42,0.6)] ring-1 ring-slate-200/70">
        <div class="flex items-center justify-between px-4 py-4 sm:px-6 bg-gradient-to-br from-white to-slate-50 border-b border-slate-200/60">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">{{ __('messages.settings.title') }}</p>
                <h3 class="text-xl font-bold text-slate-900">{{ __('messages.settings.subtitle') }}</h3>
            </div>
            <button type="button" data-message-settings-close class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">x</button>
        </div>

        <form method="post" action="{{ route('messages.settings.update') }}" class="space-y-4 bg-white px-4 py-5 sm:px-6">
            @csrf
            <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                <div>
                    <div class="text-sm font-semibold text-slate-900">{{ __('messages.settings.allow_messages') }}</div>
                    <div class="text-xs text-slate-500">{{ __('messages.settings.allow_messages_help') }}</div>
                </div>
                <x-ui.switch
                    name="allow_messages"
                    :checked="$preferences->allow_messages"
                />
            </div>

            <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                <div>
                    <div class="text-sm font-semibold text-slate-900">{{ __('messages.settings.following_only') }}</div>
                    <div class="text-xs text-slate-500">{{ __('messages.settings.following_only_help') }}</div>
                </div>
                <x-ui.switch
                    name="allow_following_only"
                    :checked="$preferences->allow_following_only"
                />
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" data-message-settings-close class="rounded-xl px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">
                    {{ __('messages.actions.cancel') }}
                </button>
                <button class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-100">
                    {{ __('messages.actions.save') }}
                </button>
            </div>
        </form>
    </div>
</div>



