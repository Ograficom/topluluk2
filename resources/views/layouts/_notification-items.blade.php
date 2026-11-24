@forelse ($headerNotifications as $notification)
    @php
        $notificationTitle = data_get($notification->data, 'title', 'Yeni bildirim');
        $notificationBody = data_get($notification->data, 'message', data_get($notification->data, 'body', 'Hesabında yeni bir hareket var.'));
        $notificationCategory = data_get($notification->data, 'category', 'announcements');
        $notificationIconPaths = [
            'replies' => ['M21 15a4 4 0 0 1-4 4H7l-4 2V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z', 'M8 10h8', 'M8 14h5'],
            'likes' => ['M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8z'],
            'posts' => ['M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z', 'M14 2v6h6', 'M8 13h8', 'M8 17h8'],
            'announcements' => ['M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9', 'M10 21h4', 'M12 2v2'],
            'followers' => ['M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2', 'M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8', 'M19 8v6', 'M16 11h6'],
            'messages' => ['M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z'],
            'mentions' => ['M10 13a5 5 0 0 0 7.1 0l3-3a5 5 0 0 0-7.1-7.1l-1.7 1.7', 'M14 11a5 5 0 0 0-7.1 0l-3 3a5 5 0 0 0 7.1 7.1l1.7-1.7'],
            'security' => ['M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z', 'm9 12 2 2 4-4'],
        ];
        $notificationIcon = $notificationIconPaths[$notificationCategory] ?? $notificationIconPaths['announcements'];
    @endphp
    <div class="notification-item{{ $notification->read_at ? '' : ' is-unread' }}">
        <a href="{{ route('notifications.open', $notification->id) }}" class="notification-item__main">
            <span class="notification-item__icon">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    @foreach ($notificationIcon as $iconPath)<path d="{{ $iconPath }}"/>@endforeach
                </svg>
            </span>
            <span class="notification-item__content">
                <strong>{{ $notificationTitle }}</strong>
                <span>{{ $notificationBody }}</span>
                <time datetime="{{ $notification->created_at?->toIso8601String() }}">{{ $notification->created_at?->diffForHumans() }}</time>
            </span>
            @if (! $notification->read_at)<span class="notification-item__unread" aria-label="Okunmadı"></span>@endif
        </a>
        <details class="notification-item__actions">
            <summary aria-label="Bildirim seçenekleri">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.7"/><circle cx="12" cy="12" r="1.7"/><circle cx="19" cy="12" r="1.7"/></svg>
            </summary>
            <div class="notification-item__actions-menu">
                <form method="POST" action="{{ route($notification->read_at ? 'notifications.mark-unread' : 'notifications.mark-read', $notification->id) }}">
                    @csrf
                    <button type="submit">{{ $notification->read_at ? 'Okunmamış olarak işaretle' : 'Okundu olarak işaretle' }}</button>
                </form>
                <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" onsubmit="return confirm('Bu bildirimi silmek istediğine emin misin?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="is-danger">Silmek</button>
                </form>
            </div>
        </details>
    </div>
@empty
    <div class="notification-empty">
        <span class="notification-empty__icon"><svg viewBox="0 0 24 24" width="25" height="25" fill="none" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        <strong>Henüz bildirimin yok</strong>
        <span>Yeni etkileşimler burada görünecek.</span>
    </div>
@endforelse
