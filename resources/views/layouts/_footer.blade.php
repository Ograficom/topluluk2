<footer class="footer">
    <nav class="footer__menu" aria-label="Alt gezinme">
        <a href="{{ route('contacts') }}" class="footer__menu-link">İletişim</a>
        <a href="{{ route('pages.show', 'rules') }}" class="footer__menu-link">Kurallar</a>
        <a href="{{ route('stats.index') }}" class="footer__menu-link">İstatistikler</a>

        <div class="dropdown dropup dropdown-md">
            <button class="btn btn-secondary btn-xs btn-rounded" type="button" data-dropdown-toggle aria-expanded="false" aria-controls="footer-more-menu" aria-label="Diğer bağlantıları aç">
                <x-repost-icon name="more" size="18" class="color-gray" />
            </button>
            <ul class="dropdown-menu dropdown-menu-end" id="footer-more-menu">
                <li class="dropdown-menu-item dropdown-menu-item-md">
                    <a href="{{ route('posts.latest') }}">Son yazılar</a>
                </li>
                <li class="dropdown-menu-item dropdown-menu-item-md">
                    <a href="{{ route('comments.latest') }}">Son yorumlar</a>
                </li>
                <li class="dropdown-menu-item dropdown-menu-item-md">
                    <a href="{{ route('tags.index') }}">Etiketler</a>
                </li>
                <li class="dropdown-menu-item dropdown-menu-item-md">
                    <a href="{{ route('pages.show', 'about') }}">Hakkımızda</a>
                </li>
            </ul>
        </div>
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
