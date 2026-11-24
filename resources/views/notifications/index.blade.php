@extends('layouts.app')

@section('title', 'Bildirimler')
@section('robots', 'noindex,follow')

@push('head')
    <style>
        /* Repost messenger.html: pm card, pm-list, pm-item and dropdown styling. */
        .notification-inbox{overflow:visible}
        .notification-inbox .pm__header{z-index:40}
        .notification-inbox .pm-list{overflow:visible}
        .notification-inbox .notification-item{
            position:relative;display:flex;align-items:center;flex-wrap:nowrap;
            min-width:0;gap:12px;margin:0;padding:10px 6px 10px 10px;
            border:0;border-radius:12px;background:transparent;
            transition:background-color var(--pm-transition);
        }
        .notification-inbox .notification-item.is-unread,
        .notification-inbox .notification-item.is-unread:hover,
        .notification-inbox .notification-item:hover,
        .notification-inbox .notification-item:focus-within{background:var(--pm-secondary-light)}
        .notification-inbox .notification-item:has(details[open]){z-index:31;flex-wrap:nowrap}
        .notification-inbox .notification-item__main{
            position:relative;display:flex;align-items:center;flex:1 1 auto;
            min-width:0;gap:12px;padding:0 32px 0 0;min-height:48px;
            color:var(--text-color);text-decoration:none!important;
        }
        .notification-inbox .notification-item__icon{
            display:grid;place-items:center;width:48px;height:48px;flex:0 0 48px;
            border-radius:50%;background:var(--pm-secondary);color:var(--pm-primary);
        }
        .notification-inbox .notification-item__icon svg{width:22px;height:22px}
        .notification-inbox .notification-item__content{display:flex;min-width:0;flex:1 1 auto;gap:3px}
        .notification-inbox .notification-item__content strong{
            color:var(--title-color);font-family:var(--font-secondary);font-size:.9rem;
            font-weight:700;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
        }
        .notification-inbox .notification-item__content>span{
            display:block;color:var(--pm-muted);font-size:.8rem;line-height:1.35;
            white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
        }
        .notification-inbox .notification-item__content time{
            margin:0;color:var(--pm-muted);font-size:.7rem;line-height:1.35;white-space:nowrap;
        }
        .notification-inbox .notification-item__unread{
            position:absolute;right:36px;top:8px;margin:0;width:7px;height:7px;
            border-radius:50%;background:var(--pm-primary);
        }
        .notification-inbox .notification-item__main:has(.notification-item__unread){padding-right:48px}
        .notification-inbox .notification-item__actions,
        .notification-inbox .notification-item:has(details[open]) .notification-item__actions{
            position:absolute;z-index:30;top:6px;right:6px;display:block;flex:none;margin:0;
        }
        .notification-inbox .notification-item__actions summary{
            display:flex;align-items:center;justify-content:center;width:34px;height:34px;
            padding:0;border:0;border-radius:10px;color:var(--pm-muted);background:transparent;
            cursor:pointer;list-style:none;
        }
        .notification-inbox .notification-item__actions summary::-webkit-details-marker{display:none}
        .notification-inbox .notification-item__actions summary:hover,
        .notification-inbox .notification-item__actions[open] summary{color:var(--text-color);background:var(--pm-secondary)}
        .notification-inbox .notification-item__actions summary:focus-visible{outline:2px solid var(--pm-primary);outline-offset:2px}
        .notification-inbox .notification-item__actions-menu{
            position:absolute!important;top:calc(100% + 5px);bottom:auto;right:0;z-index:32;
            width:220px;max-width:calc(100vw - 56px);padding:5px;margin:0;
            border:1px solid var(--dropdown-menu-border-color,var(--border-color));
            border-radius:var(--dropdown-menu-radius,14px);
            background:var(--dropdown-menu-bg,var(--card-bg));
            box-shadow:var(--dropdown-menu-shadow,0 8px 24px rgb(15 23 42 / 10%));
        }
        .notification-inbox .notification-item:nth-last-child(-n+2):nth-child(n+3) .notification-item__actions-menu{top:auto;bottom:calc(100% + 5px)}
        .notification-inbox .notification-item__actions-menu form{margin:0}
        .notification-inbox .notification-item__actions-menu button{
            display:block;width:100%;min-height:35px;padding:0 10px;border:0;border-radius:11px;
            background:transparent;color:var(--text-color);font:inherit;font-size:.82rem;
            text-align:left;cursor:pointer;
        }
        .notification-inbox .notification-item__actions-menu button:hover,
        .notification-inbox .notification-item__actions-menu button:focus-visible{background:var(--pm-secondary-light)}
        .notification-inbox .notification-item__actions-menu button.is-danger{color:var(--color-red)}
        .notification-inbox .notification-empty{
            display:flex;align-items:center;flex-direction:column;gap:8px;padding:50px 15px;
            color:var(--pm-muted);font-size:.8rem;text-align:center;
        }
        .notification-inbox .notification-empty__icon{background:var(--pm-secondary);color:var(--pm-muted)}
        .notification-inbox .pm__dropdown{min-width:220px}
        .notification-inbox .pm__dropdown form{margin:0}
        @media(max-width:600px){
            .notification-inbox .notification-item{gap:9px;padding:9px 4px 9px 6px}
            .notification-inbox .notification-item__icon{width:40px;height:40px;flex-basis:40px}
            .notification-inbox .notification-item__main{gap:9px;min-height:42px}
            .notification-inbox .notification-item__content>span{white-space:normal;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
        }
    </style>
@endpush

@section('content')
    <article class="pm card card-body notification-inbox">
        <header class="pm__header">
            <h1 class="h3">Bildirimler</h1>
            <div class="dropdown dropdown-md">
                <button type="button" class="btn btn-secondary btn-xs btn-rounded" data-dropdown-toggle
                        aria-expanded="false" aria-haspopup="menu" aria-controls="notificationInboxMenu" aria-label="Bildirim işlemleri" title="Bildirim işlemleri">
                    <svg class="color-gray" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.7"/><circle cx="12" cy="12" r="1.7"/><circle cx="19" cy="12" r="1.7"/></svg>
                </button>
                <ul class="pm__dropdown dropdown-menu dropdown-menu-end" id="notificationInboxMenu">
                    <li class="dropdown-menu-item dropdown-menu-item-sm">
                        <button type="submit" form="notificationInboxReadAll">Tümünü okundu işaretle</button>
                    </li>
                    <li class="dropdown-menu-item dropdown-menu-item-sm is-danger">
                        <button type="submit" form="notificationInboxDeleteAll">Tümünü sil</button>
                    </li>
                </ul>
            </div>
        </header>
        <form id="notificationInboxReadAll" method="POST" action="{{ route('notifications.read-all') }}" hidden>
            @csrf
        </form>
        <form id="notificationInboxDeleteAll" method="POST" action="{{ route('notifications.destroy-all') }}" onsubmit="return confirm('Tüm bildirimlerini silmek istiyor musun?')" hidden>
            @csrf
            @method('DELETE')
        </form>
        <div class="pm__list-wrap">
            <div class="pm-list">
                @include('layouts._notification-items', ['headerNotifications' => $notifications])
            </div>
        </div>
        @include('layouts._pagination', ['paginator' => $notifications])
    </article>
    <script>
        (() => {
            const inbox = document.querySelector('.notification-inbox');
            if (!inbox) return;
            const closeOthers = (keep) => {
                inbox.querySelectorAll('details.notification-item__actions[open]').forEach(details => {
                    if (details !== keep) details.open = false;
                });
            };
            inbox.querySelectorAll('details.notification-item__actions').forEach(details => {
                details.addEventListener('toggle', () => {
                    if (details.open) closeOthers(details);
                });
            });
            document.addEventListener('click', event => {
                if (!event.target.closest('.notification-inbox details.notification-item__actions')) closeOthers(null);
            });
            inbox.addEventListener('keydown', event => {
                if (event.key !== 'Escape') return;
                const open = inbox.querySelector('details.notification-item__actions[open]');
                if (open) {
                    open.open = false;
                    open.querySelector('summary')?.focus();
                }
            });
        })();
    </script>
@endsection
