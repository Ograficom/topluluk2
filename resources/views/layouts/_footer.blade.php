<footer class="footer">
    <nav class="footer__menu" aria-label="Alt gezinme">
        <a href="mailto:{{ config('mail.from.address') }}" class="footer__menu-link">İletişim</a>
        <a href="{{ route('register') }}#signup-rules" class="footer__menu-link">Kurallar</a>
        <a href="{{ route('stats.index') }}" class="footer__menu-link">İstatistikler</a>
        <button class="btn btn-secondary btn-xs btn-rounded" type="button" data-modal-btn="footer-more" aria-label="Yan menüyü aç" aria-haspopup="dialog">
            <x-repost-icon name="more" size="18" class="color-gray" />
        </button>
    </nav>

    <small class="color-gray">{{ $brandName }} &copy;&nbsp;{{ $footerYear }}</small>

    <nav class="footer__social" aria-label="Sosyal medya">
        @foreach (['instagram' => 'Instagram', 'telegram' => 'Telegram', 'threads' => 'Threads'] as $network => $label)
            @if (filled($footerSocialLinks[$network]))
                <a class="btn btn-sm btn-secondary btn-square" href="{{ $footerSocialLinks[$network] }}" aria-label="{{ $label }}" target="_blank" rel="noopener noreferrer"><x-repost-icon :name="$network" size="16" class="color-gray" /></a>
            @else
                <span class="btn btn-sm btn-secondary btn-square" aria-label="{{ $label }}"><x-repost-icon :name="$network" size="16" class="color-gray" /></span>
            @endif
        @endforeach
    </nav>
</footer>

<div class="modal footer-drawer" data-modal="footer-more" aria-label="Diğer bağlantılar">
    <div class="modal-content footer-drawer__content">
        <div class="modal-header footer-drawer__header">
            <h2 class="modal-title">Diğer bağlantılar</h2>
            <button class="btn btn-secondary-ghost btn-circle btn-sm modal-close" type="button" data-modal-close aria-label="Menüyü kapat">
                <x-repost-icon name="close" size="18" />
            </button>
        </div>
        <div class="modal-body footer-drawer__body">
            <a class="footer-drawer__link" href="{{ route('posts.index') }}">
                <x-repost-icon name="feed" size="18" />
                <span>Son yazılar</span>
                <x-repost-icon name="chevron-right" size="16" class="color-gray" />
            </a>
            <a class="footer-drawer__link" href="mailto:{{ config('mail.from.address') }}">
                <x-repost-icon name="mail" size="18" />
                <span>İletişim</span>
                <x-repost-icon name="chevron-right" size="16" class="color-gray" />
            </a>
            <a class="footer-drawer__link" href="{{ route('register') }}#signup-rules">
                <x-repost-icon name="shield-check" size="18" />
                <span>Kurallar</span>
                <x-repost-icon name="chevron-right" size="16" class="color-gray" />
            </a>
            <a class="footer-drawer__link" href="{{ route('stats.index') }}">
                <x-repost-icon name="chart-no-axes-column-increasing" size="18" />
                <span>İstatistikler</span>
                <x-repost-icon name="chevron-right" size="16" class="color-gray" />
            </a>
        </div>
    </div>
</div>

<style>
.footer-drawer {
    --modal-viewport-offset: 0px;
    --modal-viewport-padding-x: 0px;
    --modal-content-radius: 22px 0 0 22px;
    --modal-content-width: min(420px, 82vw);
    justify-content: flex-end;
    align-items: stretch;
    padding: 0;
}
.footer-drawer > .footer-drawer__content {
    width: var(--modal-content-width);
    height: 100dvh;
    max-height: 100dvh;
    margin-left: auto;
    border-radius: var(--modal-content-radius);
    transform: translate3d(100%, 0, 0);
    transform-origin: right center;
    transition: transform 260ms cubic-bezier(.22,.8,.25,1), opacity 260ms ease;
}
.footer-drawer.is-open > .footer-drawer__content {
    transform: translate3d(0, 0, 0);
}
.footer-drawer__header {
    min-height: 72px;
    padding: 20px 64px 20px 22px !important;
}
.footer-drawer__body {
    display: grid;
    align-content: start;
    gap: 8px;
    padding: 16px !important;
}
.footer-drawer__link {
    display: flex;
    min-height: 54px;
    align-items: center;
    gap: 14px;
    padding: 0 14px;
    border-radius: 12px;
    color: var(--text-color);
    text-decoration: none;
    transition: background-color 150ms ease, color 150ms ease;
}
.footer-drawer__link:hover,
.footer-drawer__link:focus-visible {
    color: var(--color-primary);
    background: var(--content-item-hover-bg);
}
.footer-drawer__link span {
    flex: 1;
    font-weight: 600;
}
@media (prefers-reduced-motion: reduce) {
    .footer-drawer > .footer-drawer__content,
    .footer-drawer__link {
        transition: none;
    }
}
</style>
