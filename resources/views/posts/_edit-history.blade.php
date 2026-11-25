@if ($post->is_published && ! $post->is_spam && (! $post->is_adult || auth()->user()?->show_adult_content))
    <span class="dropdown post-edit-history" data-edit-history-url="{{ route('posts.edit-history', $post) }}">
        <button type="button" class="btn btn-secondary-ghost btn-circle btn-xs post-edit-history-trigger" data-dropdown-toggle aria-expanded="false" aria-label="Düzenleme geçmişini göster" title="Düzenleme geçmişi">
            <x-repost-icon name="clock" size="14" />
        </button>
        <ul class="dropdown-menu post-edit-history-menu" aria-label="Düzenleme geçmişi">
            <li class="dropdown-menu-title">Düzenleme geçmişi</li>
            <li class="post-edit-history-status small color-gray" data-edit-history-status aria-live="polite">Yükleniyor…</li>
            <li class="post-edit-history-entries" role="presentation"><ul data-edit-history-entries></ul></li>
            <li class="dropdown-menu-item dropdown-menu-item-sm" data-edit-history-more-row hidden>
                <button type="button" data-edit-history-more>Daha fazla göster</button>
            </li>
        </ul>
    </span>
@endif
