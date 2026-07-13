@php($contactCollection = collect($contacts))

<section class="rounded-[18px] border border-slate-200 bg-white p-4">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            @if (!empty($icon ?? null))
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                    {!! $icon !!}
                </span>
            @endif
            <div>
                <h2 class="text-sm font-medium text-slate-900">{{ $title }}</h2>
                <p class="text-xs text-slate-500">{{ __('messages.contacts.count', ['count' => $contactCollection->count()]) }}</p>
            </div>
        </div>
    </div>

    @if ($contactCollection->isEmpty())
        <p class="text-sm text-slate-500">{{ $emptyText }}</p>
    @else
        <div class="divide-y divide-slate-100">
            @foreach ($contactCollection as $contact)
                @php($person = $contact['user'])
                @php($lastMessage = $contact['last_message'])
                @php($lastSnippet = $lastMessage?->body ?: ($lastMessage?->attachment_path ? __('messages.contacts.file_share') : __('messages.contacts.no_messages')))
                @php($targetUrl = $contact['thread_exists'] ? $contact['thread_url'] : route('users.show', $person))

                <a href="{{ $targetUrl }}" class="flex items-center gap-3 py-3">
                    <img
                        class="h-11 w-11 rounded-full object-cover"
                        src="{{ $person->profile_photo_url }}"
                        alt="{{ $person->name }}"
                    >

                    <span class="min-w-0 flex-1">
                        <span class="flex items-center justify-between gap-3">
                            <span class="truncate text-sm font-medium text-slate-900">{{ $person->name }}</span>

                            @if ($contact['unread'] > 0)
                                <span class="inline-flex min-w-[1.5rem] items-center justify-center rounded-full bg-slate-200 px-2 py-0.5 text-[0.7rem] font-medium text-slate-700">
                                    {{ $contact['unread'] }}
                                </span>
                            @endif
                        </span>

                        <span class="mt-1 block truncate text-xs text-slate-500">
                            {{ $person->username ? '@' . $person->username . ' · ' : '' }}{{ \Illuminate\Support\Str::limit($lastSnippet, 72) }}
                        </span>
                    </span>
                </a>
            @endforeach
        </div>
    @endif
</section>
