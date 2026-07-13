<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('messages.settings.title') }}
            </h2>
            <a href="{{ route('messages.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                {{ __('messages.actions.back_to_messages') }}
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-[45rem] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-6">
                    @if (session('status'))
                        <div class="rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="post" action="{{ route('messages.settings.update') }}" class="space-y-4">
                        @csrf

                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div>
                                <div class="text-sm font-semibold text-gray-800">{{ __('messages.settings.allow_messages') }}</div>
                                <div class="text-xs text-gray-500">{{ __('messages.settings.allow_messages_page_help') }}</div>
                            </div>
                            <x-ui.switch
                                name="allow_messages"
                                :checked="$preferences->allow_messages"
                            />
                        </div>

                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div>
                                <div class="text-sm font-semibold text-gray-800">{{ __('messages.settings.following_only') }}</div>
                                <div class="text-xs text-gray-500">{{ __('messages.settings.following_only_page_help') }}</div>
                            </div>
                            <x-ui.switch
                                name="allow_following_only"
                                :checked="$preferences->allow_following_only"
                            />
                        </div>

                        <div class="flex items-center justify-end">
                            <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                {{ __('messages.actions.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

