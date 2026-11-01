@extends('layouts.repost')

@section('title', 'Bildirimler — OgraFi')
@section('content')
    <div data-ografi-layout-container="notifications">
    <div data-ografi-layout-section="notifications_header" class="repost-feed-heading">
        <div><span class="repost-eyebrow">GÜNCEL</span><h1>Bildirimler</h1><p>{{ number_format((int) $unreadCount) }} okunmamış bildirim</p></div>
        <form method="POST" action="{{ route('notifications.mark-all') }}">@csrf<button class="btn btn-secondary btn-rounded" type="submit">Tümünü okundu işaretle</button></form>
    </div>
    <div class="card repost-notification-list" data-ografi-layout-section="notifications_list">
        @forelse($notifications as $notification)
            <div class="repost-notification-row {{ $notification['is_unread'] ? 'is-unread' : '' }}">
                <span class="avatar">
                    @if($notification['actor_avatar'])<img src="{{ $notification['actor_avatar'] }}" alt="" width="42" height="42">@else<span class="repost-avatar-fallback">{{ mb_substr($notification['actor_name'], 0, 1) }}</span>@endif
                </span>
                <a href="{{ $notification['read_url'] }}" class="repost-notification-copy">
                    <strong>{{ $notification['title'] }}</strong>
                    <span>{{ $notification['preview'] }}</span>
                    <time class="meta">{{ $notification['time_human'] }}</time>
                </a>
                <form method="POST" action="{{ $notification['delete_url'] }}">@csrf<button class="btn btn-secondary btn-sm" type="submit">Sil</button></form>
            </div>
        @empty
            <div class="repost-empty-state">Henüz bildirimin yok.</div>
        @endforelse
    </div>
    <div class="repost-pagination">{{ $notifications->links() }}</div>
    </div>
@endsection
