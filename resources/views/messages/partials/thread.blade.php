@foreach($messages as $message)
    @php($mine = (int) $message->sender_id === (int) auth()->id())
    <article class="repost-message-bubble {{ $mine ? 'is-mine' : 'is-theirs' }}" data-message-id="{{ $message->id }}">
        @if(filled($message->body))<p>{{ $message->body }}</p>@endif
        @if($message->attachment_path)
            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($message->attachment_path) }}" target="_blank" rel="noopener noreferrer">{{ $message->attachment_name ?: 'Ekli dosyayı görüntüle' }}</a>
        @endif
        <time>{{ $message->created_at?->format('d.m.Y H:i') }}</time>
    </article>
@endforeach
