@foreach($messages as $message)
<article class="pm-message {{ $message->sender_id === auth()->id() ? 'pm-message--outgoing' : 'pm-message--incoming' }}" data-message-id="{{ $message->id }}"><p>{{ $message->body }}</p>@if($message->attachment_path)<a href="{{ \Illuminate\Support\Facades\Storage::url($message->attachment_path) }}">{{ $message->attachment_name ?? 'Dosya' }}</a>@endif<time>{{ $message->created_at?->format('d.m.Y H:i') }}</time></article>
@endforeach
