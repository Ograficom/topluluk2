@php
    $hasViteAssets = file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json'));
    $appCssVersion = file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : null;
    $appJsVersion = file_exists(public_path('js/app.js')) ? filemtime(public_path('js/app.js')) : null;
@endphp

@if($hasViteAssets)
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    <link rel="stylesheet" href="{{ asset('css/app.css') }}{{ $appCssVersion ? '?v=' . $appCssVersion : '' }}">
    <script src="{{ asset('js/app.js') }}{{ $appJsVersion ? '?v=' . $appJsVersion : '' }}" defer></script>
@endif

<style>
    body.alma-app :where(
        [aria-label*="Diger islemler"],
        [aria-label*="Diğer işlemler"],
        [aria-label*="More"],
        [aria-label*="more"],
        [data-post-card-menu-trigger],
        [data-comments-sort-toggle],
        [data-user-menu-btn],
        [data-logo-menu-btn],
        [data-theme-toggle],
        [data-profile-actions-open],
        [data-sort-toggle],
        [data-create-actions-menu] summary,
        [data-category-menu] summary,
        [data-sort-menu] summary,
        [data-message-settings-menu] summary,
        summary.profile-summary-toggle,
        .menu-btn,
        .menu-button,
        .site-notifications-more,
        .alma-post-card__menu-trigger,
        .post-show-profile__menu-trigger,
        .show-comments-sort__toggle,
        .profile-reference-menu-summary,
        .profile-reference-actions-trigger
    ) {
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
        transform: none !important;
        transition: none !important;
    }

    body.alma-app :where(
        [aria-label*="Diger islemler"],
        [aria-label*="Diğer işlemler"],
        [aria-label*="More"],
        [aria-label*="more"],
        [data-post-card-menu-trigger],
        [data-comments-sort-toggle],
        [data-user-menu-btn],
        [data-logo-menu-btn],
        [data-theme-toggle],
        [data-profile-actions-open],
        [data-sort-toggle],
        [data-create-actions-menu] summary,
        [data-category-menu] summary,
        [data-sort-menu] summary,
        [data-message-settings-menu] summary,
        summary.profile-summary-toggle,
        .menu-btn,
        .menu-button,
        .site-notifications-more,
        .alma-post-card__menu-trigger,
        .post-show-profile__menu-trigger,
        .show-comments-sort__toggle,
        .profile-reference-menu-summary,
        .profile-reference-actions-trigger
    ):where(:hover, :focus-visible, [aria-expanded="true"], [open] > summary) {
        background: #f4f4f5 !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
        transform: none !important;
    }

    body.alma-app :where(
        [data-post-card-menu],
        [data-post-card-reaction-menu],
        [data-user-menu-panel],
        [data-logo-menu-panel],
        [data-notifications-actions-menu],
        [data-comments-sort-menu],
        [data-profile-menu-panel],
        [data-sort-list],
        [data-create-actions-menu] > div,
        [data-category-menu] > div,
        [data-sort-menu] > div,
        [data-message-settings-menu] > div,
        .site-menu-panel,
        .alma-post-card__menu-panel,
        .post-show-profile__menu-panel,
        .show-comments-sort__menu,
        .profile-reference-sort-panel,
        .profile-reference-actions-dropdown,
        .message-settings-menu__panel,
        .post-card__menu,
        .post-card__reaction-menu
    ) {
        border: 0 !important;
        border-radius: 8px !important;
        background: #ffffff !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
    }

    body.alma-app :where(
        [data-post-card-menu] a,
        [data-post-card-menu] button,
        [data-post-card-reaction-menu] a,
        [data-post-card-reaction-menu] button,
        [data-user-menu-panel] a,
        [data-user-menu-panel] button,
        [data-logo-menu-panel] a,
        [data-logo-menu-panel] button,
        [data-notifications-actions-menu] a,
        [data-notifications-actions-menu] button,
        [data-comments-sort-menu] button,
        [data-profile-menu-panel] a,
        [data-profile-menu-panel] button,
        [data-sort-list] button,
        [data-create-actions-menu] > div a,
        [data-create-actions-menu] > div button,
        [data-category-menu] > div a,
        [data-category-menu] > div button,
        [data-sort-menu] > div a,
        [data-sort-menu] > div button,
        [data-message-settings-menu] > div a,
        [data-message-settings-menu] > div button,
        .site-menu-panel a,
        .site-menu-panel button,
        .alma-post-card__menu-item,
        .post-show-profile__menu-item,
        .post-show-profile__menu-button,
        .show-comments-sort__option,
        .profile-reference-sort-option,
        .profile-reference-actions-dropdown a,
        .profile-reference-actions-dropdown button,
        .message-settings-menu__panel a,
        .message-settings-menu__panel button,
        .post-card__menu-item,
        .post-card__reaction-option
    ) {
        border: 0 !important;
        border-radius: 6px !important;
        background: transparent !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
        transform: none !important;
        transition: none !important;
    }

    body.alma-app :where(
        [data-post-card-menu] a,
        [data-post-card-menu] button,
        [data-post-card-reaction-menu] a,
        [data-post-card-reaction-menu] button,
        [data-user-menu-panel] a,
        [data-user-menu-panel] button,
        [data-logo-menu-panel] a,
        [data-logo-menu-panel] button,
        [data-notifications-actions-menu] a,
        [data-notifications-actions-menu] button,
        [data-comments-sort-menu] button,
        [data-profile-menu-panel] a,
        [data-profile-menu-panel] button,
        [data-sort-list] button,
        [data-create-actions-menu] > div a,
        [data-create-actions-menu] > div button,
        [data-category-menu] > div a,
        [data-category-menu] > div button,
        [data-sort-menu] > div a,
        [data-sort-menu] > div button,
        [data-message-settings-menu] > div a,
        [data-message-settings-menu] > div button,
        .site-menu-panel a,
        .site-menu-panel button,
        .alma-post-card__menu-item,
        .post-show-profile__menu-item,
        .post-show-profile__menu-button,
        .show-comments-sort__option,
        .profile-reference-sort-option,
        .profile-reference-actions-dropdown a,
        .profile-reference-actions-dropdown button,
        .message-settings-menu__panel a,
        .message-settings-menu__panel button,
        .post-card__menu-item,
        .post-card__reaction-option
    ):where(:hover, :focus-visible, .is-active) {
        background: #f4f4f5 !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
        transform: none !important;
    }

    body.alma-app :where(
        .site-icon-btn,
        .profile-reference-icon-btn,
        .alma-post-card__metric-button,
        .alma-post-card__reaction-picker,
        .alma-post-card__reaction-pill,
        .alma-post-card__reaction-more,
        .post-reaction-bookmark-btn,
        .rx-summary-pill,
        [data-share-btn],
        [data-share-close],
        [data-reaction-overflow-toggle],
        [data-post-card-shell] .action-btn,
        [data-post-card-shell] .action-stat,
        [data-post-card-shell] .action-chip,
        [data-post-card-shell] .reaction-add,
        [data-post-card-shell] .smiley-btn,
        [data-post-card-shell] .post-card__action-button,
        [data-post-card-shell] .post-card__action-link,
        #comments .show-comment-card__vote-cluster,
        #comments .show-comment-card__thread-toggle,
        #comments .show-comment-card__reply,
        #comments .show-comment-card__action-icon,
        #comments .show-comment-form__tool,
        #comments .show-comment-form__send
    ) {
        background: transparent !important;
        color: #111111 !important;
        box-shadow: none !important;
        transform: none !important;
        transition: none !important;
    }

    body.alma-app :where(
        .site-icon-btn,
        .profile-reference-icon-btn,
        .alma-post-card__metric-button,
        .alma-post-card__reaction-picker,
        .alma-post-card__reaction-pill,
        .alma-post-card__reaction-more,
        .post-reaction-bookmark-btn,
        .rx-summary-pill,
        [data-share-btn],
        [data-share-close],
        [data-reaction-overflow-toggle],
        [data-post-card-shell] .action-btn,
        [data-post-card-shell] .action-stat,
        [data-post-card-shell] .action-chip,
        [data-post-card-shell] .reaction-add,
        [data-post-card-shell] .smiley-btn,
        [data-post-card-shell] .post-card__action-button,
        [data-post-card-shell] .post-card__action-link,
        #comments .show-comment-card__vote-cluster,
        #comments .show-comment-card__thread-toggle,
        #comments .show-comment-card__reply,
        #comments .show-comment-card__action-icon,
        #comments .show-comment-form__tool,
        #comments .show-comment-form__send
    ):where(:hover, :focus-visible, :focus-within) {
        background: #f4f4f5 !important;
        color: #111111 !important;
        box-shadow: none !important;
        outline: none !important;
        transform: none !important;
    }
</style>
