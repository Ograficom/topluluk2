@forelse ($messages as $message)
    @php($isMine = $message->sender_id === auth()->id())
    @php($attachmentUrl = $message->attachment_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($message->attachment_path) : null)
    @php($attachmentMime = $message->attachment_mime ?? '')
    @php($gifPattern = '/\\[gif:(https?:\\/\\/(?:media\\d*\\.giphy\\.com|i\\.giphy\\.com)\\/[^\\]\\s]+)\\]/i')
    @php($gifUrl = preg_match($gifPattern, (string) $message->body, $gifMatch) ? $gifMatch[1] : null)
    @php($cleanBody = $gifUrl ? trim(preg_replace($gifPattern, '', (string) $message->body)) : $message->body)

    <div class="message-fade flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
        <div class="flex max-w-full flex-col {{ $isMine ? 'items-end' : 'items-start' }}">
            <div class="messages-bubble {{ $isMine ? 'messages-bubble--mine' : 'messages-bubble--other' }}">
                @if ($attachmentUrl)
                    @if (str_starts_with($attachmentMime, 'image/'))
                        <img src="{{ $attachmentUrl }}" alt="{{ $message->attachment_name ?? __('messages.thread.image_alt') }}" class="mb-3 max-h-80 w-full rounded-xl object-cover">
                    @elseif (str_starts_with($attachmentMime, 'video/'))
                        <div class="mb-3 overflow-hidden rounded-xl">
                            @include('components.custom-video-player', [
                                'video' => [
                                    'url' => $attachmentUrl,
                                    'type' => $attachmentMime,
                                    'subtitles' => [],
                                ],
                            ])
                        </div>
                    @elseif (str_starts_with($attachmentMime, 'audio/'))
                        <div class="mb-3 rounded-xl {{ $isMine ? 'bg-white/10' : 'bg-gray-50' }} p-3">
                            <audio src="{{ $attachmentUrl }}" controls class="w-full"></audio>
                        </div>
                    @else
                        <a
                            href="{{ $attachmentUrl }}"
                            target="_blank"
                            rel="noopener"
                            class="mb-3 flex items-center gap-3 rounded-xl {{ $isMine ? 'bg-white/10 text-white' : 'border border-gray-200 bg-gray-50 text-gray-700' }} px-4 py-3"
                        >
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full {{ $isMine ? 'bg-white/10' : 'bg-white' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <path d="M14 2v6h6"/>
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1 truncate text-sm font-medium">{{ $message->attachment_name ?? __('messages.thread.file') }}</span>
                            <span class="text-xs font-medium opacity-80">{{ __('messages.actions.open') }}</span>
                        </a>
                    @endif
                @endif

                @if ($gifUrl)
                    <img src="{{ $gifUrl }}" alt="{{ __('messages.thread.gif_alt') }}" class="mb-3 max-h-72 w-full rounded-xl object-cover">
                @endif

                @if (filled($cleanBody))
                    <p class="whitespace-pre-line text-sm leading-6">{{ $cleanBody }}</p>
                @endif
            </div>

            <div class="mt-1 px-1 text-[11px] text-gray-400">
                {{ $message->created_at?->format('H:i') }}
                @if ($isMine)
                    <span class="ml-2">{{ $message->read_at ? __('messages.thread.seen') : __('messages.thread.sent') }}</span>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="messages-empty-state flex min-h-[280px] flex-col items-center justify-center px-6 py-10 text-center">
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-200 text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/>
            </svg>
        </div>
        <p class="mt-4 text-base font-medium text-gray-700">{{ __('messages.thread.empty_title') }}</p>
        <p class="mt-1 max-w-md text-sm text-gray-500">{{ __('messages.thread.empty_description') }}</p>
    </div>
@endforelse
