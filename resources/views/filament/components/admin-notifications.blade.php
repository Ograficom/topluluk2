@php
    $adminNotifications = auth()->user()?->notifications()->latest()->limit(8)->get() ?? collect();
    $adminUnreadNotifications = auth()->user()?->unreadNotifications()->count() ?? 0;
@endphp

<div
    class="ogra-admin-notifications"
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
>
    <button
        type="button"
        class="ogra-admin-notifications__trigger"
        @click="open = !open"
        :aria-expanded="open.toString()"
        aria-label="Bildirimler{{ $adminUnreadNotifications > 0 ? ', '.$adminUnreadNotifications.' okunmamış' : '' }}"
    >
        <x-filament::icon icon="heroicon-o-bell" class="ogra-admin-notifications__bell" />
        @if ($adminUnreadNotifications > 0)
            <span class="ogra-admin-notifications__count">{{ $adminUnreadNotifications > 99 ? '99+' : $adminUnreadNotifications }}</span>
            <span class="ogra-admin-notifications__dot"></span>
        @endif
    </button>

    <div class="ogra-admin-notifications__panel" x-cloak x-show="open" x-transition.origin.top.right>
        <div class="ogra-admin-notifications__header">
            <div>
                <strong>Bildirimler</strong>
                <span>{{ $adminUnreadNotifications }} okunmamış bildirim</span>
            </div>
            @if ($adminUnreadNotifications > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit">Tümünü okundu işaretle</button>
                </form>
            @endif
        </div>

        <div class="ogra-admin-notifications__list">
            @forelse ($adminNotifications as $notification)
                <a
                    href="{{ route('notifications.open', $notification->id) }}"
                    class="ogra-admin-notifications__item{{ $notification->read_at ? '' : ' is-unread' }}"
                >
                    <span class="ogra-admin-notifications__item-icon">
                        <x-filament::icon icon="heroicon-o-bell-alert" />
                    </span>
                    <span class="ogra-admin-notifications__item-content">
                        <strong>{{ data_get($notification->data, 'title', 'Yeni bildirim') }}</strong>
                        <span>{{ data_get($notification->data, 'message', 'Hesabınızda yeni bir hareket var.') }}</span>
                        <time datetime="{{ $notification->created_at?->toIso8601String() }}">{{ $notification->created_at?->diffForHumans() }}</time>
                    </span>
                    @if (! $notification->read_at)
                        <span class="ogra-admin-notifications__unread-dot"></span>
                    @endif
                </a>
            @empty
                <div class="ogra-admin-notifications__empty">
                    <x-filament::icon icon="heroicon-o-bell-slash" />
                    <strong>Henüz bildirim yok</strong>
                    <span>Yeni şikayet ve destek talepleri burada görünecek.</span>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .ogra-admin-notifications{position:relative;display:flex;align-items:center}
    .ogra-admin-notifications [x-cloak]{display:none!important}
    .ogra-admin-notifications__trigger{position:relative;display:grid;place-items:center;width:2.5rem;height:2.5rem;border:0;border-radius:.5rem;background:transparent;color:var(--gray-500);cursor:pointer}
    .ogra-admin-notifications__trigger:hover{background:var(--gray-50);color:var(--gray-700)}
    .dark .ogra-admin-notifications__trigger:hover{background:var(--gray-800);color:var(--gray-200)}
    .ogra-admin-notifications__bell{width:1.35rem;height:1.35rem}
    .ogra-admin-notifications__count{position:absolute;top:0;right:-.1rem;display:grid;place-items:center;min-width:1.05rem;height:1.05rem;padding:0 .2rem;border:2px solid var(--fi-color-primary-50,#fff);border-radius:999px;background:#2563eb;color:#fff;font-size:.62rem;font-weight:800;line-height:1}
    .ogra-admin-notifications__dot{position:absolute;top:.1rem;right:.1rem;width:.48rem;height:.48rem;border:2px solid var(--fi-color-primary-50,#fff);border-radius:50%;background:#ef4444}
    .ogra-admin-notifications__panel{position:absolute;z-index:100;top:calc(100% + .65rem);right:0;width:min(22rem,calc(100vw - 2rem));overflow:hidden;border:1px solid var(--gray-200);border-radius:.85rem;background:var(--fi-main-50,#fff);box-shadow:0 12px 32px rgba(15,23,42,.14);color:var(--gray-950)}
    .dark .ogra-admin-notifications__panel{border-color:var(--gray-700);background:var(--gray-900);color:var(--gray-100)}
    .ogra-admin-notifications__header{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.9rem 1rem;border-bottom:1px solid var(--gray-200)}
    .dark .ogra-admin-notifications__header{border-color:var(--gray-700)}
    .ogra-admin-notifications__header>div{display:flex;flex-direction:column;gap:.15rem}
    .ogra-admin-notifications__header strong{font-size:.9rem;font-weight:700}
    .ogra-admin-notifications__header span{font-size:.7rem;color:var(--gray-500)}
    .ogra-admin-notifications__header form button{border:0;background:none;color:var(--primary-600);font-size:.68rem;font-weight:600;white-space:nowrap;cursor:pointer}
    .ogra-admin-notifications__list{max-height:24rem;overflow-y:auto}
    .ogra-admin-notifications__item{display:flex;align-items:flex-start;gap:.7rem;padding:.8rem .9rem;border-bottom:1px solid var(--gray-100);text-decoration:none!important}
    .dark .ogra-admin-notifications__item{border-color:var(--gray-800)}
    .ogra-admin-notifications__item:hover,.ogra-admin-notifications__item.is-unread{background:var(--gray-50)}
    .dark .ogra-admin-notifications__item:hover,.dark .ogra-admin-notifications__item.is-unread{background:var(--gray-800)}
    .ogra-admin-notifications__item-icon{display:grid;flex:none;place-items:center;width:2.1rem;height:2.1rem;border-radius:.6rem;background:var(--primary-50);color:var(--primary-600)}
    .ogra-admin-notifications__item-icon svg{width:1.1rem;height:1.1rem}
    .ogra-admin-notifications__item-content{display:flex;min-width:0;flex:1;flex-direction:column;gap:.2rem}
    .ogra-admin-notifications__item-content strong{font-size:.75rem;font-weight:700;color:var(--gray-950)}
    .dark .ogra-admin-notifications__item-content strong{color:var(--gray-100)}
    .ogra-admin-notifications__item-content>span{font-size:.7rem;line-height:1.4;color:var(--gray-500);overflow-wrap:anywhere}
    .ogra-admin-notifications__item-content time{font-size:.64rem;color:var(--gray-400)}
    .ogra-admin-notifications__unread-dot{flex:none;width:.45rem;height:.45rem;margin-top:.35rem;border-radius:50%;background:#2563eb}
    .ogra-admin-notifications__empty{display:flex;align-items:center;flex-direction:column;gap:.45rem;padding:2rem 1rem;text-align:center}
    .ogra-admin-notifications__empty>svg{width:1.8rem;height:1.8rem;margin-bottom:.2rem;color:var(--gray-400)}
    .ogra-admin-notifications__empty strong{font-size:.8rem}
    .ogra-admin-notifications__empty span{font-size:.68rem;color:var(--gray-500)}
</style>
