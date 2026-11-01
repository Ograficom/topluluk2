<footer class="footer">
    <nav class="footer__menu" aria-label="Alt gezinme">
        <a href="mailto:{{ config('mail.from.address') }}" class="footer__menu-link">İletişim</a>
        <a href="{{ route('register') }}#signup-rules" class="footer__menu-link">Kurallar</a>
        <a href="{{ route('stats.index') }}" class="footer__menu-link">İstatistikler</a>
        <div class="dropdown dropup dropdown-md">
            <button class="btn btn-secondary btn-xs btn-rounded" type="button" data-dropdown-toggle aria-expanded="false" aria-controls="footer-more-menu" aria-label="Diğer bağlantıları aç">
                <x-repost-icon name="more" size="18" class="color-gray" />
            </button>
            <ul class="dropdown-menu dropdown-menu-end" id="footer-more-menu">
                <li class="dropdown-menu-item dropdown-menu-item-md"><a href="{{ route('posts.index') }}">Son yazılar</a></li>
            </ul>
        </div>
    </nav>
    <small class="color-gray">Repost &copy;&nbsp;<span data-date="Y"></span></small>
    <nav class="footer__social" aria-label="Sosyal medya">
        <span class="btn btn-sm btn-secondary btn-square" aria-label="Instagram"><x-repost-icon name="instagram" size="16" class="color-gray" /></span>
        <span class="btn btn-sm btn-secondary btn-square" aria-label="Telegram"><x-repost-icon name="telegram" size="16" class="color-gray" /></span>
        <span class="btn btn-sm btn-secondary btn-square" aria-label="Threads"><x-repost-icon name="threads" size="16" class="color-gray" /></span>
    </nav>
</footer>
