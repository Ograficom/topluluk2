<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap');

    .site-header,
    .site-header *,
    .site-menu-panel,
    .site-notifications-panel,
    .site-notifications-actions-menu {
        font-family: "Roboto", Arial, Helvetica, sans-serif !important;
    }

    .site-header,
    .site-header-shell,
    .site-header-actions,
    [data-notifications-root],
    .site-notifications-panel,
    .site-notifications-panel-head,
    [data-notifications-actions],
    [data-user-menu] {
        overflow: visible !important;
    }

    .site-header {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        z-index: 9990 !important;
    }

    body {
        padding-top: var(--site-header-height, 70px) !important;
    }

    .site-header-shell {
        align-items: center !important;
    }

    .site-header-actions {
        position: relative !important;
        align-items: center !important;
    }

    /*
    SADECE ARAMA KUTUSU KONUM DUZELTMESI
    Sag-sol konuma dokunmadan yukari-asagi ortalar.
    */
    .site-search-panel {
        align-items: center !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        align-self: center !important;
    }

    .site-search-dropdown-top {
        align-items: center !important;
    }

    label.site-search-field,
    .site-search-field {
        align-items: center !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    @media (min-width: 1024px) {
        .site-search-panel {
            display: flex !important;
        }

        .site-header-write-btn,
        a.site-header-write-btn {
            display: inline-flex !important;
        }
    }

    /*
    MOBILDE ARAMA GENISLIGI
    Sagdan soldan daha genis olur.
    */
    @media (max-width: 1023px) {
        .site-search-dropdown {
            position: fixed !important;
            left: 10px !important;
            right: 10px !important;
            top: 72px !important;
            width: auto !important;
            max-width: none !important;
            margin: 0 !important;
        }

        .site-search-dropdown-top {
            width: 100% !important;
        }

        label.site-search-field,
        .site-search-field {
            width: 100% !important;
            max-width: none !important;
            flex: 1 1 auto !important;
        }

        .site-header-write-btn,
        a.site-header-write-btn {
            display: none !important;
        }
    }

    /*
    HEADER ICON BUTONLARI
    Normalde seffaf.
    Sadece tiklaninca / focus / acikken gri.
    Gölge ve animasyon yok.
    */
    .site-icon-btn,
    .site-search-trigger,
    .site-search-close,
    .site-search-clear,
    .site-notifications-more,
    .mobile-sidebar-trigger,
    button[data-notifications-actions-btn],
    button[data-user-menu-btn],
    button[data-mobile-sidebar-toggle],
    a.site-icon-btn {
        background: transparent !important;
        background-color: transparent !important;
        background-image: none !important;
        box-shadow: none !important;
        outline: none !important;
        border-color: transparent !important;
        transition: none !important;
        transform: none !important;
        font-weight: 400 !important;
    }

    .site-icon-btn:hover,
    .site-search-trigger:hover,
    .site-search-close:hover,
    .site-search-clear:hover,
    .site-notifications-more:hover,
    .mobile-sidebar-trigger:hover,
    button[data-notifications-actions-btn]:hover,
    button[data-user-menu-btn]:hover,
    button[data-mobile-sidebar-toggle]:hover,
    a.site-icon-btn:hover {
        background: transparent !important;
        background-color: transparent !important;
        background-image: none !important;
        box-shadow: none !important;
        border-color: transparent !important;
        transition: none !important;
        transform: none !important;
    }

    .site-icon-btn:active,
    .site-icon-btn:focus,
    .site-icon-btn:focus-visible,
    .site-icon-btn[aria-expanded="true"],
    .site-search-trigger:active,
    .site-search-trigger:focus,
    .site-search-trigger:focus-visible,
    .site-search-trigger[aria-expanded="true"],
    .site-search-close:active,
    .site-search-close:focus,
    .site-search-close:focus-visible,
    .site-search-clear:active,
    .site-search-clear:focus,
    .site-search-clear:focus-visible,
    .site-notifications-more:active,
    .site-notifications-more:focus,
    .site-notifications-more:focus-visible,
    .site-notifications-more[aria-expanded="true"],
    .mobile-sidebar-trigger:active,
    .mobile-sidebar-trigger:focus,
    .mobile-sidebar-trigger:focus-visible,
    .mobile-sidebar-trigger[aria-expanded="true"],
    button[data-notifications-actions-btn]:active,
    button[data-notifications-actions-btn]:focus,
    button[data-notifications-actions-btn]:focus-visible,
    button[data-notifications-actions-btn][aria-expanded="true"],
    button[data-user-menu-btn]:active,
    button[data-user-menu-btn]:focus,
    button[data-user-menu-btn]:focus-visible,
    button[data-user-menu-btn][aria-expanded="true"],
    button[data-mobile-sidebar-toggle]:active,
    button[data-mobile-sidebar-toggle]:focus,
    button[data-mobile-sidebar-toggle]:focus-visible,
    button[data-mobile-sidebar-toggle][aria-expanded="true"],
    a.site-icon-btn:active,
    a.site-icon-btn:focus,
    a.site-icon-btn:focus-visible,
    a.site-icon-btn[aria-expanded="true"] {
        background: #f1f5f9 !important;
        background-color: #f1f5f9 !important;
        background-image: none !important;
        box-shadow: none !important;
        border-color: transparent !important;
        transition: none !important;
        transform: none !important;
    }

    .site-icon-btn *,
    .site-search-trigger *,
    .site-search-close *,
    .site-search-clear *,
    .site-notifications-more *,
    .mobile-sidebar-trigger *,
    button[data-notifications-actions-btn] *,
    button[data-user-menu-btn] *,
    button[data-mobile-sidebar-toggle] *,
    a.site-icon-btn *,
    iconify-icon {
        background: transparent !important;
        background-color: transparent !important;
        background-image: none !important;
        box-shadow: none !important;
        transition: none !important;
        transform: none !important;
    }

    .site-icon-btn::before,
    .site-icon-btn::after,
    .site-search-trigger::before,
    .site-search-trigger::after,
    .site-search-close::before,
    .site-search-close::after,
    .site-search-clear::before,
    .site-search-clear::after,
    .site-notifications-more::before,
    .site-notifications-more::after,
    .mobile-sidebar-trigger::before,
    .mobile-sidebar-trigger::after,
    button[data-notifications-actions-btn]::before,
    button[data-notifications-actions-btn]::after,
    button[data-user-menu-btn]::before,
    button[data-user-menu-btn]::after,
    button[data-mobile-sidebar-toggle]::before,
    button[data-mobile-sidebar-toggle]::after,
    a.site-icon-btn::before,
    a.site-icon-btn::after {
        content: none !important;
        display: none !important;
        background: transparent !important;
        background-color: transparent !important;
        box-shadow: none !important;
    }

    /*
    YAZ BUTONU
    Gölge yok, font kalınlaştırma yok.
    */
    .site-header-write-btn,
    a.site-header-write-btn {
        height: 42px !important;
        min-height: 42px !important;
        padding: 0 18px !important;
        border-radius: 9999px !important;
        background: #2563eb !important;
        background-color: #2563eb !important;
        border: 1px solid #2563eb !important;
        color: #ffffff !important;
        font-weight: 400 !important;
        box-shadow: none !important;
        align-items: center !important;
        justify-content: center !important;
        transition: none !important;
        transform: none !important;
    }

    .site-header-write-btn:hover,
    a.site-header-write-btn:hover {
        background: #1d4ed8 !important;
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
        box-shadow: none !important;
        transition: none !important;
        transform: none !important;
    }

    .site-header-write-btn:active,
    .site-header-write-btn:focus,
    .site-header-write-btn:focus-visible,
    a.site-header-write-btn:active,
    a.site-header-write-btn:focus,
    a.site-header-write-btn:focus-visible {
        background: #1e40af !important;
        background-color: #1e40af !important;
        border-color: #1e40af !important;
        color: #ffffff !important;
        outline: none !important;
        box-shadow: none !important;
        transition: none !important;
        transform: none !important;
    }

    .site-header-write-btn iconify-icon,
    .site-header-write-btn span {
        color: #ffffff !important;
        background: transparent !important;
        background-color: transparent !important;
        font-weight: 400 !important;
    }

    /*
    ARAMA ALANI
    */
    .site-search-panel,
    .site-search-dropdown,
    .site-search-dropdown-top,
    [data-search-shell],
    [data-search-dropdown] {
        background: transparent !important;
        background-color: transparent !important;
        box-shadow: none !important;
        transition: none !important;
    }

    .site-search-dropdown-top {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
    }

    label.site-search-field,
    .site-search-field {
        height: 44px !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        flex: 1 1 auto !important;
        min-width: 0 !important;
        padding: 0 14px !important;
        margin: 0 !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        border: 1px solid #d1d5db !important;
        border-radius: 9999px !important;
        box-shadow: none !important;
        outline: none !important;
        overflow: hidden !important;
        transition: none !important;
    }

    .site-search-field::before,
    .site-search-field::after,
    label.site-search-field::before,
    label.site-search-field::after {
        display: none !important;
        content: none !important;
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    .site-search-icon {
        width: 20px !important;
        height: 20px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 auto !important;
        background: transparent !important;
        background-color: transparent !important;
        color: #0f172a !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    label.site-search-field input,
    .site-search-field input,
    input[data-search-input] {
        height: 100% !important;
        flex: 1 1 auto !important;
        min-width: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 0 !important;
        outline: 0 !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        font-weight: 400 !important;
        transition: none !important;
    }

    label.site-search-field input:focus,
    .site-search-field input:focus,
    input[data-search-input]:focus {
        border: 0 !important;
        outline: 0 !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        box-shadow: none !important;
    }

    input[data-search-input]::placeholder,
    .site-search-field input::placeholder {
        color: #64748b !important;
        font-weight: 400 !important;
    }

    input[data-search-input]:-webkit-autofill,
    input[data-search-input]:-webkit-autofill:hover,
    input[data-search-input]:-webkit-autofill:focus {
        -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
        -webkit-text-fill-color: #0f172a !important;
    }

    .site-search-close {
        width: 42px !important;
        height: 42px !important;
        border-radius: 9999px !important;
        border: 0 !important;
        color: #475569 !important;
        flex: 0 0 auto !important;
    }

    .site-search-clear {
        width: 34px !important;
        height: 34px !important;
        border-radius: 9999px !important;
        border: 0 !important;
        color: #475569 !important;
        flex: 0 0 auto !important;
    }

    /*
    GELISMIS BILDIRIM PANELI
    */
    .site-notifications-panel {
        position: absolute !important;
        top: calc(100% + 14px) !important;
        right: 0 !important;
        z-index: 99980 !important;
        width: 390px !important;
        max-width: calc(100vw - 24px) !important;
        padding: 0 !important;
        border-radius: 24px !important;
        border: 1px solid rgba(226, 232, 240, 0.95) !important;
        background: #ffffff !important;
        box-shadow: none !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        overflow: visible !important;
        transition: none !important;
        transform: none !important;
    }

    .site-notifications-panel::before {
        content: "" !important;
        position: absolute !important;
        top: -7px !important;
        right: 22px !important;
        width: 14px !important;
        height: 14px !important;
        transform: rotate(45deg) !important;
        background: #ffffff !important;
        border-left: 1px solid rgba(226, 232, 240, 0.95) !important;
        border-top: 1px solid rgba(226, 232, 240, 0.95) !important;
        box-shadow: none !important;
    }

    .site-notifications-panel-head {
        position: relative !important;
        z-index: 2 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
        padding: 18px 18px 14px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        box-shadow: none !important;
    }

    .site-notifications-panel-title {
        margin: 0 !important;
        font-size: 17px !important;
        line-height: 1.2 !important;
        font-weight: 400 !important;
        color: #0f172a !important;
        letter-spacing: 0 !important;
    }

    .site-notifications-panel-subtitle {
        margin: 4px 0 0 !important;
        color: #64748b !important;
        font-size: 12px !important;
        font-weight: 400 !important;
        line-height: 1.4 !important;
    }

    .site-notifications-more,
    button[data-notifications-actions-btn] {
        width: 36px !important;
        height: 36px !important;
        border: 0 !important;
        border-radius: 12px !important;
        color: #475569 !important;
    }

    .site-notifications-actions-menu,
    div[data-notifications-actions-menu] {
        position: absolute !important;
        top: calc(100% + 8px) !important;
        right: 0 !important;
        left: auto !important;
        z-index: 99999 !important;
        width: max-content !important;
        min-width: 250px !important;
        max-width: 290px !important;
        padding: 8px !important;
        border-radius: 18px !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        background-color: #ffffff !important;
        box-shadow: none !important;
        transition: none !important;
        transform: none !important;
    }

    .site-notifications-menu-item,
    button[data-notifications-mark-all],
    button[data-notifications-delete-all] {
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        padding: 11px 12px !important;
        border: 0 !important;
        border-radius: 13px !important;
        background: transparent !important;
        color: #0f172a !important;
        font-size: 13px !important;
        font-weight: 400 !important;
        line-height: 1.35 !important;
        text-align: left !important;
        white-space: normal !important;
        box-shadow: none !important;
        transition: none !important;
        transform: none !important;
    }

    .site-notifications-menu-item:hover,
    button[data-notifications-mark-all]:hover,
    button[data-notifications-delete-all]:hover {
        background: #f8fafc !important;
        background-color: #f8fafc !important;
        box-shadow: none !important;
        transform: none !important;
    }

    .site-notifications-menu-item iconify-icon {
        flex: 0 0 auto !important;
        font-size: 16px !important;
        color: #2563eb !important;
    }

    .site-notifications-menu-item[data-notifications-delete-all] iconify-icon {
        color: #ef4444 !important;
    }

    .site-notifications-menu-item span {
        display: block !important;
        white-space: normal !important;
        word-break: normal !important;
        font-weight: 400 !important;
    }

    .site-notifications-list {
        max-height: 430px !important;
        overflow-y: auto !important;
        padding: 10px !important;
        box-shadow: none !important;
    }

    .site-notifications-empty {
        margin: 0 !important;
        padding: 26px 16px !important;
        text-align: center !important;
        color: #64748b !important;
        font-size: 14px !important;
        font-weight: 400 !important;
    }

    .site-notifications-list a,
    .site-notifications-list button {
        border-radius: 16px !important;
        box-shadow: none !important;
        transition: none !important;
        transform: none !important;
        font-weight: 400 !important;
    }

    .site-notifications-footer-link {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        margin: 0 !important;
        padding: 14px 16px !important;
        border-top: 1px solid #f1f5f9 !important;
        color: #2563eb !important;
        font-size: 13px !important;
        font-weight: 400 !important;
        text-decoration: none !important;
        box-shadow: none !important;
        transition: none !important;
        transform: none !important;
    }

    .site-notifications-footer-link:hover {
        background: #f8fafc !important;
        box-shadow: none !important;
        transform: none !important;
    }

    /*
    GELISMIS PROFIL MENU
    */
    .site-menu-panel {
        position: absolute !important;
        top: calc(100% + 14px) !important;
        right: 0 !important;
        z-index: 99970 !important;
        width: 288px !important;
        padding: 10px !important;
        border: 1px solid rgba(226, 232, 240, 0.95) !important;
        border-radius: 24px !important;
        background: #ffffff !important;
        box-shadow: none !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        transition: none !important;
        transform: none !important;
    }

    .site-menu-panel::before {
        content: "" !important;
        position: absolute !important;
        top: -7px !important;
        right: 22px !important;
        width: 14px !important;
        height: 14px !important;
        transform: rotate(45deg) !important;
        background: #ffffff !important;
        border-left: 1px solid rgba(226, 232, 240, 0.95) !important;
        border-top: 1px solid rgba(226, 232, 240, 0.95) !important;
        box-shadow: none !important;
    }

    .site-user-menu-card {
        position: relative !important;
        z-index: 2 !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        padding: 12px !important;
        margin-bottom: 8px !important;
        border-radius: 18px !important;
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
    }

    .site-user-menu-avatar {
        width: 46px !important;
        height: 46px !important;
        border-radius: 9999px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 auto !important;
        overflow: hidden !important;
        background: #dbeafe !important;
        color: #2563eb !important;
        font-size: 14px !important;
        font-weight: 400 !important;
        box-shadow: none !important;
    }

    .site-user-menu-avatar img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        border-radius: 9999px !important;
        box-shadow: none !important;
    }

    .site-user-menu-info {
        min-width: 0 !important;
        flex: 1 1 auto !important;
    }

    .site-user-menu-name {
        display: block !important;
        color: #0f172a !important;
        font-size: 14px !important;
        font-weight: 400 !important;
        line-height: 1.25 !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .site-user-menu-username {
        display: block !important;
        margin-top: 2px !important;
        color: #64748b !important;
        font-size: 12px !important;
        font-weight: 400 !important;
        line-height: 1.25 !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .site-user-menu-divider {
        height: 1px !important;
        margin: 8px 4px !important;
        background: #f1f5f9 !important;
        box-shadow: none !important;
    }

    .site-user-menu-link,
    .site-user-menu-button {
        position: relative !important;
        z-index: 2 !important;
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        min-height: 44px !important;
        padding: 10px 12px !important;
        border-radius: 16px !important;
        border: 0 !important;
        background: transparent !important;
        color: #0f172a !important;
        font-size: 14px !important;
        font-weight: 400 !important;
        line-height: 1.2 !important;
        text-align: left !important;
        text-decoration: none !important;
        transition: none !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .site-user-menu-link:hover,
    .site-user-menu-button:hover {
        background: #f8fafc !important;
        color: #2563eb !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .site-user-menu-icon {
        width: 32px !important;
        height: 32px !important;
        border-radius: 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 auto !important;
        background: #f1f5f9 !important;
        color: #334155 !important;
        box-shadow: none !important;
    }

    .site-user-menu-link:hover .site-user-menu-icon,
    .site-user-menu-button:hover .site-user-menu-icon {
        background: #dbeafe !important;
        color: #2563eb !important;
        box-shadow: none !important;
    }

    .site-user-menu-button.site-user-menu-button-danger {
        color: #dc2626 !important;
        font-weight: 400 !important;
    }

    .site-user-menu-button.site-user-menu-button-danger:hover {
        background: #fef2f2 !important;
        color: #dc2626 !important;
    }

    .site-user-menu-button.site-user-menu-button-danger .site-user-menu-icon {
        color: #dc2626 !important;
        background: #fef2f2 !important;
    }

    button[data-user-menu-btn] {
        background: transparent !important;
        background-color: transparent !important;
        padding: 0 !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    button[data-user-menu-btn] .site-avatar-fallback {
        background: #e0edff !important;
        color: #4f46e5 !important;
        font-weight: 400 !important;
        box-shadow: none !important;
    }

    /*
    DARK MODE
    */
    html.dark .site-header-write-btn,
    html.dark a.site-header-write-btn,
    .dark .site-header-write-btn,
    .dark a.site-header-write-btn {
        background: #2563eb !important;
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: none !important;
        font-weight: 400 !important;
    }

    html.dark .site-header-write-btn:hover,
    html.dark a.site-header-write-btn:hover,
    .dark .site-header-write-btn:hover,
    .dark a.site-header-write-btn:hover {
        background: #1d4ed8 !important;
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }

    html.dark label.site-search-field,
    html.dark .site-search-field,
    .dark label.site-search-field,
    .dark .site-search-field {
        background: #0f172a !important;
        background-color: #0f172a !important;
        border-color: #334155 !important;
        box-shadow: none !important;
    }

    html.dark label.site-search-field input,
    html.dark .site-search-field input,
    html.dark input[data-search-input],
    .dark label.site-search-field input,
    .dark .site-search-field input,
    .dark input[data-search-input] {
        background: #0f172a !important;
        background-color: #0f172a !important;
        color: #e2e8f0 !important;
        box-shadow: none !important;
    }

    html.dark input[data-search-input]::placeholder,
    html.dark .site-search-field input::placeholder,
    .dark input[data-search-input]::placeholder,
    .dark .site-search-field input::placeholder {
        color: #94a3b8 !important;
    }

    html.dark input[data-search-input]:-webkit-autofill,
    .dark input[data-search-input]:-webkit-autofill {
        -webkit-box-shadow: 0 0 0 1000px #0f172a inset !important;
        -webkit-text-fill-color: #e2e8f0 !important;
    }

    html.dark .site-search-icon,
    .dark .site-search-icon {
        color: #e2e8f0 !important;
    }

    html.dark .site-icon-btn,
    html.dark .site-search-trigger,
    html.dark .site-search-close,
    html.dark .site-search-clear,
    html.dark .site-notifications-more,
    html.dark .mobile-sidebar-trigger,
    html.dark button[data-notifications-actions-btn],
    html.dark button[data-user-menu-btn],
    html.dark button[data-mobile-sidebar-toggle],
    html.dark a.site-icon-btn,
    .dark .site-icon-btn,
    .dark .site-search-trigger,
    .dark .site-search-close,
    .dark .site-search-clear,
    .dark .site-notifications-more,
    .dark .mobile-sidebar-trigger,
    .dark button[data-notifications-actions-btn],
    .dark button[data-user-menu-btn],
    .dark button[data-mobile-sidebar-toggle],
    .dark a.site-icon-btn {
        background: transparent !important;
        background-color: transparent !important;
        background-image: none !important;
        box-shadow: none !important;
        border-color: transparent !important;
    }

    html.dark .site-icon-btn:hover,
    html.dark .site-search-trigger:hover,
    html.dark .site-search-close:hover,
    html.dark .site-search-clear:hover,
    html.dark .site-notifications-more:hover,
    html.dark .mobile-sidebar-trigger:hover,
    html.dark button[data-notifications-actions-btn]:hover,
    html.dark button[data-user-menu-btn]:hover,
    html.dark button[data-mobile-sidebar-toggle]:hover,
    html.dark a.site-icon-btn:hover,
    .dark .site-icon-btn:hover,
    .dark .site-search-trigger:hover,
    .dark .site-search-close:hover,
    .dark .site-search-clear:hover,
    .dark .site-notifications-more:hover,
    .dark .mobile-sidebar-trigger:hover,
    .dark button[data-notifications-actions-btn]:hover,
    .dark button[data-user-menu-btn]:hover,
    .dark button[data-mobile-sidebar-toggle]:hover,
    .dark a.site-icon-btn:hover {
        background: transparent !important;
        background-color: transparent !important;
        box-shadow: none !important;
    }

    html.dark .site-icon-btn:active,
    html.dark .site-icon-btn:focus,
    html.dark .site-icon-btn:focus-visible,
    html.dark .site-icon-btn[aria-expanded="true"],
    html.dark .site-search-trigger:active,
    html.dark .site-search-trigger:focus,
    html.dark .site-search-trigger:focus-visible,
    html.dark .site-search-trigger[aria-expanded="true"],
    html.dark .site-search-close:active,
    html.dark .site-search-close:focus,
    html.dark .site-search-close:focus-visible,
    html.dark .site-search-clear:active,
    html.dark .site-search-clear:focus,
    html.dark .site-search-clear:focus-visible,
    html.dark .site-notifications-more:active,
    html.dark .site-notifications-more:focus,
    html.dark .site-notifications-more:focus-visible,
    html.dark .site-notifications-more[aria-expanded="true"],
    html.dark .mobile-sidebar-trigger:active,
    html.dark .mobile-sidebar-trigger:focus,
    html.dark .mobile-sidebar-trigger:focus-visible,
    html.dark .mobile-sidebar-trigger[aria-expanded="true"],
    html.dark button[data-notifications-actions-btn]:active,
    html.dark button[data-notifications-actions-btn]:focus,
    html.dark button[data-notifications-actions-btn]:focus-visible,
    html.dark button[data-notifications-actions-btn][aria-expanded="true"],
    html.dark button[data-user-menu-btn]:active,
    html.dark button[data-user-menu-btn]:focus,
    html.dark button[data-user-menu-btn]:focus-visible,
    html.dark button[data-user-menu-btn][aria-expanded="true"],
    html.dark button[data-mobile-sidebar-toggle]:active,
    html.dark button[data-mobile-sidebar-toggle]:focus,
    html.dark button[data-mobile-sidebar-toggle]:focus-visible,
    html.dark button[data-mobile-sidebar-toggle][aria-expanded="true"],
    html.dark a.site-icon-btn:active,
    html.dark a.site-icon-btn:focus,
    html.dark a.site-icon-btn:focus-visible,
    html.dark a.site-icon-btn[aria-expanded="true"],
    .dark .site-icon-btn:active,
    .dark .site-icon-btn:focus,
    .dark .site-icon-btn:focus-visible,
    .dark .site-icon-btn[aria-expanded="true"],
    .dark .site-search-trigger:active,
    .dark .site-search-trigger:focus,
    .dark .site-search-trigger:focus-visible,
    .dark .site-search-trigger[aria-expanded="true"],
    .dark .site-search-close:active,
    .dark .site-search-close:focus,
    .dark .site-search-close:focus-visible,
    .dark .site-search-clear:active,
    .dark .site-search-clear:focus,
    .dark .site-search-clear:focus-visible,
    .dark .site-notifications-more:active,
    .dark .site-notifications-more:focus,
    .dark .site-notifications-more:focus-visible,
    .dark .site-notifications-more[aria-expanded="true"],
    .dark .mobile-sidebar-trigger:active,
    .dark .mobile-sidebar-trigger:focus,
    .dark .mobile-sidebar-trigger:focus-visible,
    .dark .mobile-sidebar-trigger[aria-expanded="true"],
    .dark button[data-notifications-actions-btn]:active,
    .dark button[data-notifications-actions-btn]:focus,
    .dark button[data-notifications-actions-btn]:focus-visible,
    .dark button[data-notifications-actions-btn][aria-expanded="true"],
    .dark button[data-user-menu-btn]:active,
    .dark button[data-user-menu-btn]:focus,
    .dark button[data-user-menu-btn]:focus-visible,
    .dark button[data-user-menu-btn][aria-expanded="true"],
    .dark button[data-mobile-sidebar-toggle]:active,
    .dark button[data-mobile-sidebar-toggle]:focus,
    .dark button[data-mobile-sidebar-toggle]:focus-visible,
    .dark button[data-mobile-sidebar-toggle][aria-expanded="true"],
    .dark a.site-icon-btn:active,
    .dark a.site-icon-btn:focus,
    .dark a.site-icon-btn:focus-visible,
    .dark a.site-icon-btn[aria-expanded="true"] {
        background: #1e293b !important;
        background-color: #1e293b !important;
        box-shadow: none !important;
    }

    html.dark .site-search-close,
    html.dark .site-search-clear,
    .dark .site-search-close,
    .dark .site-search-clear {
        color: #cbd5e1 !important;
    }

    html.dark .site-notifications-panel,
    .dark .site-notifications-panel,
    html.dark .site-menu-panel,
    .dark .site-menu-panel {
        background: #0f172a !important;
        border-color: #334155 !important;
        box-shadow: none !important;
    }

    html.dark .site-notifications-panel::before,
    .dark .site-notifications-panel::before,
    html.dark .site-menu-panel::before,
    .dark .site-menu-panel::before {
        background: #0f172a !important;
        border-color: #334155 !important;
        box-shadow: none !important;
    }

    html.dark .site-notifications-panel-head,
    .dark .site-notifications-panel-head,
    html.dark .site-notifications-footer-link,
    .dark .site-notifications-footer-link {
        border-color: #1e293b !important;
    }

    html.dark .site-notifications-panel-title,
    .dark .site-notifications-panel-title,
    html.dark .site-user-menu-name,
    .dark .site-user-menu-name {
        color: #f8fafc !important;
        font-weight: 400 !important;
    }

    html.dark .site-notifications-empty,
    .dark .site-notifications-empty,
    html.dark .site-user-menu-username,
    .dark .site-user-menu-username,
    html.dark .site-notifications-panel-subtitle,
    .dark .site-notifications-panel-subtitle {
        color: #94a3b8 !important;
        font-weight: 400 !important;
    }

    html.dark .site-notifications-actions-menu,
    html.dark div[data-notifications-actions-menu],
    .dark .site-notifications-actions-menu,
    .dark div[data-notifications-actions-menu] {
        background: #0f172a !important;
        background-color: #0f172a !important;
        border-color: #334155 !important;
        box-shadow: none !important;
    }

    html.dark .site-notifications-menu-item,
    html.dark button[data-notifications-mark-all],
    html.dark button[data-notifications-delete-all],
    .dark .site-notifications-menu-item,
    .dark button[data-notifications-mark-all],
    .dark button[data-notifications-delete-all] {
        color: #e2e8f0 !important;
        font-weight: 400 !important;
    }

    html.dark .site-notifications-menu-item:hover,
    html.dark button[data-notifications-mark-all]:hover,
    html.dark button[data-notifications-delete-all]:hover,
    .dark .site-notifications-menu-item:hover,
    .dark button[data-notifications-mark-all]:hover,
    .dark button[data-notifications-delete-all]:hover,
    html.dark .site-notifications-footer-link:hover,
    .dark .site-notifications-footer-link:hover {
        background: #1e293b !important;
        background-color: #1e293b !important;
    }

    html.dark button[data-user-menu-btn] .site-avatar-fallback,
    .dark button[data-user-menu-btn] .site-avatar-fallback {
        background: #1e3a8a !important;
        color: #dbeafe !important;
        font-weight: 400 !important;
    }

    html.dark .site-user-menu-card,
    .dark .site-user-menu-card {
        background: #111827 !important;
        border-color: #1e293b !important;
        box-shadow: none !important;
    }

    html.dark .site-user-menu-divider,
    .dark .site-user-menu-divider {
        background: #1e293b !important;
    }

    html.dark .site-user-menu-link,
    html.dark .site-user-menu-button,
    .dark .site-user-menu-link,
    .dark .site-user-menu-button {
        color: #e2e8f0 !important;
        font-weight: 400 !important;
    }

    html.dark .site-user-menu-link:hover,
    html.dark .site-user-menu-button:hover,
    .dark .site-user-menu-link:hover,
    .dark .site-user-menu-button:hover {
        background: #1e293b !important;
        color: #93c5fd !important;
        box-shadow: none !important;
        transform: none !important;
    }

    html.dark .site-user-menu-icon,
    .dark .site-user-menu-icon {
        background: #1e293b !important;
        color: #cbd5e1 !important;
        box-shadow: none !important;
    }

    html.dark .site-user-menu-link:hover .site-user-menu-icon,
    html.dark .site-user-menu-button:hover .site-user-menu-icon,
    .dark .site-user-menu-link:hover .site-user-menu-icon,
    .dark .site-user-menu-button:hover .site-user-menu-icon {
        background: #1e3a8a !important;
        color: #dbeafe !important;
    }

    html.dark .site-user-menu-button.site-user-menu-button-danger,
    .dark .site-user-menu-button.site-user-menu-button-danger {
        color: #fca5a5 !important;
        font-weight: 400 !important;
    }

    html.dark .site-user-menu-button.site-user-menu-button-danger:hover,
    .dark .site-user-menu-button.site-user-menu-button-danger:hover {
        background: rgba(127, 29, 29, 0.35) !important;
        color: #fecaca !important;
    }

    html.dark .site-user-menu-button.site-user-menu-button-danger .site-user-menu-icon,
    .dark .site-user-menu-button.site-user-menu-button-danger .site-user-menu-icon {
        background: rgba(127, 29, 29, 0.35) !important;
        color: #fecaca !important;
    }

    /*
    HEADER DARK MODE FINAL FIX
    Bu blok header, logo, ikonlar, arama kutusu, profil menusu,
    bildirim paneli ve mobil menuyu dark mode uyumlu hale getirir.
    */
    .site-header {
        background: rgba(255, 255, 255, 0.96) !important;
        background-color: rgba(255, 255, 255, 0.96) !important;
        border-bottom: 1px solid #e5e7eb !important;
        color: #0f172a !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        box-shadow: none !important;
    }

    .site-header-shell {
        background: transparent !important;
        color: inherit !important;
        box-shadow: none !important;
    }

    .site-header a,
    .site-header button,
    .site-header span,
    .site-header iconify-icon,
    .site-header svg {
        color: inherit !important;
    }

    .site-header-logo,
    .site-header-logo-wordmark {
        color: #0f172a !important;
        font-weight: 400 !important;
        text-decoration: none !important;
    }



    .site-header-logo-light-image {
        display: inline-flex !important;
    }

    .site-header-logo-dark-image {
        display: none !important;
        width: auto !important;
        height: 36px !important;
        max-height: 36px !important;
        object-fit: contain !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    html.dark .site-header-logo-light-image,
    .dark .site-header-logo-light-image {
        display: inline-flex !important;
    }

    html.dark .site-header-logo-dark-image,
    .dark .site-header-logo-dark-image {
        display: none !important;
    }

    .site-header-logo-image,
    .site-header-logo-image *,
    .site-header-logo svg,
    .site-header-logo svg * {
        color: #0f172a !important;
        fill: currentColor !important;
        stroke: currentColor !important;
    }

    .site-icon-btn,
    .site-search-trigger,
    .site-search-close,
    .site-search-clear,
    .site-notifications-more,
    .mobile-sidebar-trigger,
    button[data-notifications-actions-btn],
    button[data-user-menu-btn],
    button[data-mobile-sidebar-toggle],
    a.site-icon-btn {
        color: #334155 !important;
    }

    .site-icon-btn iconify-icon,
    .site-search-trigger iconify-icon,
    .site-search-close iconify-icon,
    .site-search-clear iconify-icon,
    .site-notifications-more iconify-icon,
    .mobile-sidebar-trigger svg,
    button[data-notifications-actions-btn] iconify-icon,
    button[data-user-menu-btn] iconify-icon,
    button[data-mobile-sidebar-toggle] svg,
    a.site-icon-btn iconify-icon {
        color: currentColor !important;
        fill: none !important;
        stroke: currentColor !important;
    }

    .site-status-dot {
        background: #2563eb !important;
        border-color: #ffffff !important;
        box-shadow: none !important;
    }

    .site-search-results-panel {
        margin-top: 10px !important;
        padding: 10px !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 20px !important;
        background: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
    }

    .site-search-empty {
        color: #64748b !important;
        font-weight: 400 !important;
    }

    .site-search-all {
        color: #2563eb !important;
        background: transparent !important;
        font-weight: 400 !important;
        text-decoration: none !important;
    }

    .site-search-all:hover {
        background: #f8fafc !important;
        color: #1d4ed8 !important;
    }

    html.dark .site-header,
    .dark .site-header {
        background: rgba(255, 255, 255, 0.96) !important;
        background-color: rgba(255, 255, 255, 0.96) !important;
        border-bottom-color: #e5e7eb !important;
        color: #0f172a !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        box-shadow: none !important;
    }

    html.dark .site-header-shell,
    .dark .site-header-shell {
        background: transparent !important;
        color: #0f172a !important;
        box-shadow: none !important;
    }

    html.dark .site-header-logo,
    html.dark .site-header-logo-wordmark,
    .dark .site-header-logo,
    .dark .site-header-logo-wordmark {
        color: #0f172a !important;
        font-weight: 400 !important;
    }

    html.dark .site-header-logo-image,
    html.dark .site-header-logo-image *,
    html.dark .site-header-logo svg,
    html.dark .site-header-logo svg *,
    .dark .site-header-logo-image,
    .dark .site-header-logo-image *,
    .dark .site-header-logo svg,
    .dark .site-header-logo svg * {
        color: #0f172a !important;
        fill: currentColor !important;
        stroke: currentColor !important;
    }

    .site-header .site-header-logo-wordmark {
        font-family: "Poppins", "Roboto", Arial, Helvetica, sans-serif !important;
        font-size: 1.38rem !important;
        font-weight: 600 !important;
        letter-spacing: 0 !important;
    }

    .site-header-logo-mark {
        position: relative !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 42px !important;
        height: 42px !important;
        flex: 0 0 42px !important;
    }

    .site-header-logo-mark .site-header-logo-image,
    .site-header-logo-mark .site-header-logo-dark-image {
        position: absolute !important;
        inset: 0 !important;
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
    }

    .site-header-logo-main-image {
        padding-top: 0 !important;
    }

    html.dark .site-header a,
    html.dark .site-header button,
    html.dark .site-header span,
    html.dark .site-header iconify-icon,
    html.dark .site-header svg,
    .dark .site-header a,
    .dark .site-header button,
    .dark .site-header span,
    .dark .site-header iconify-icon,
    .dark .site-header svg {
        color: inherit !important;
    }

    html.dark .site-header .site-header-logo-wordmark,
    .dark .site-header .site-header-logo-wordmark {
        font-family: "Poppins", "Roboto", Arial, Helvetica, sans-serif !important;
        font-size: 1.38rem !important;
        font-weight: 600 !important;
        letter-spacing: 0 !important;
    }

    html.dark .site-icon-btn,
    html.dark .site-search-trigger,
    html.dark .site-search-close,
    html.dark .site-search-clear,
    html.dark .site-notifications-more,
    html.dark .mobile-sidebar-trigger,
    html.dark button[data-notifications-actions-btn],
    html.dark button[data-user-menu-btn],
    html.dark button[data-mobile-sidebar-toggle],
    html.dark a.site-icon-btn,
    .dark .site-icon-btn,
    .dark .site-search-trigger,
    .dark .site-search-close,
    .dark .site-search-clear,
    .dark .site-notifications-more,
    .dark .mobile-sidebar-trigger,
    .dark button[data-notifications-actions-btn],
    .dark button[data-user-menu-btn],
    .dark button[data-mobile-sidebar-toggle],
    .dark a.site-icon-btn {
        color: #cbd5e1 !important;
        background: transparent !important;
        background-color: transparent !important;
        border-color: transparent !important;
        box-shadow: none !important;
    }

    html.dark .site-icon-btn:hover,
    html.dark .site-search-trigger:hover,
    html.dark .site-search-close:hover,
    html.dark .site-search-clear:hover,
    html.dark .site-notifications-more:hover,
    html.dark .mobile-sidebar-trigger:hover,
    html.dark button[data-notifications-actions-btn]:hover,
    html.dark button[data-user-menu-btn]:hover,
    html.dark button[data-mobile-sidebar-toggle]:hover,
    html.dark a.site-icon-btn:hover,
    .dark .site-icon-btn:hover,
    .dark .site-search-trigger:hover,
    .dark .site-search-close:hover,
    .dark .site-search-clear:hover,
    .dark .site-notifications-more:hover,
    .dark .mobile-sidebar-trigger:hover,
    .dark button[data-notifications-actions-btn]:hover,
    .dark button[data-user-menu-btn]:hover,
    .dark button[data-mobile-sidebar-toggle]:hover,
    .dark a.site-icon-btn:hover {
        color: #f8fafc !important;
        background: transparent !important;
        background-color: transparent !important;
        box-shadow: none !important;
    }

    html.dark .site-icon-btn:active,
    html.dark .site-icon-btn:focus,
    html.dark .site-icon-btn:focus-visible,
    html.dark .site-icon-btn[aria-expanded="true"],
    html.dark .site-search-trigger:active,
    html.dark .site-search-trigger:focus,
    html.dark .site-search-trigger:focus-visible,
    html.dark .site-search-trigger[aria-expanded="true"],
    html.dark .site-search-close:active,
    html.dark .site-search-close:focus,
    html.dark .site-search-close:focus-visible,
    html.dark .site-search-clear:active,
    html.dark .site-search-clear:focus,
    html.dark .site-search-clear:focus-visible,
    html.dark .site-notifications-more:active,
    html.dark .site-notifications-more:focus,
    html.dark .site-notifications-more:focus-visible,
    html.dark .site-notifications-more[aria-expanded="true"],
    html.dark .mobile-sidebar-trigger:active,
    html.dark .mobile-sidebar-trigger:focus,
    html.dark .mobile-sidebar-trigger:focus-visible,
    html.dark .mobile-sidebar-trigger[aria-expanded="true"],
    html.dark button[data-notifications-actions-btn]:active,
    html.dark button[data-notifications-actions-btn]:focus,
    html.dark button[data-notifications-actions-btn]:focus-visible,
    html.dark button[data-notifications-actions-btn][aria-expanded="true"],
    html.dark button[data-user-menu-btn]:active,
    html.dark button[data-user-menu-btn]:focus,
    html.dark button[data-user-menu-btn]:focus-visible,
    html.dark button[data-user-menu-btn][aria-expanded="true"],
    html.dark button[data-mobile-sidebar-toggle]:active,
    html.dark button[data-mobile-sidebar-toggle]:focus,
    html.dark button[data-mobile-sidebar-toggle]:focus-visible,
    html.dark button[data-mobile-sidebar-toggle][aria-expanded="true"],
    html.dark a.site-icon-btn:active,
    html.dark a.site-icon-btn:focus,
    html.dark a.site-icon-btn:focus-visible,
    html.dark a.site-icon-btn[aria-expanded="true"],
    .dark .site-icon-btn:active,
    .dark .site-icon-btn:focus,
    .dark .site-icon-btn:focus-visible,
    .dark .site-icon-btn[aria-expanded="true"],
    .dark .site-search-trigger:active,
    .dark .site-search-trigger:focus,
    .dark .site-search-trigger:focus-visible,
    .dark .site-search-trigger[aria-expanded="true"],
    .dark .site-search-close:active,
    .dark .site-search-close:focus,
    .dark .site-search-close:focus-visible,
    .dark .site-search-clear:active,
    .dark .site-search-clear:focus,
    .dark .site-search-clear:focus-visible,
    .dark .site-notifications-more:active,
    .dark .site-notifications-more:focus,
    .dark .site-notifications-more:focus-visible,
    .dark .site-notifications-more[aria-expanded="true"],
    .dark .mobile-sidebar-trigger:active,
    .dark .mobile-sidebar-trigger:focus,
    .dark .mobile-sidebar-trigger:focus-visible,
    .dark .mobile-sidebar-trigger[aria-expanded="true"],
    .dark button[data-notifications-actions-btn]:active,
    .dark button[data-notifications-actions-btn]:focus,
    .dark button[data-notifications-actions-btn]:focus-visible,
    .dark button[data-notifications-actions-btn][aria-expanded="true"],
    .dark button[data-user-menu-btn]:active,
    .dark button[data-user-menu-btn]:focus,
    .dark button[data-user-menu-btn]:focus-visible,
    .dark button[data-user-menu-btn][aria-expanded="true"],
    .dark button[data-mobile-sidebar-toggle]:active,
    .dark button[data-mobile-sidebar-toggle]:focus,
    .dark button[data-mobile-sidebar-toggle]:focus-visible,
    .dark button[data-mobile-sidebar-toggle][aria-expanded="true"],
    .dark a.site-icon-btn:active,
    .dark a.site-icon-btn:focus,
    .dark a.site-icon-btn:focus-visible,
    .dark a.site-icon-btn[aria-expanded="true"] {
        color: #ffffff !important;
        background: #1e293b !important;
        background-color: #1e293b !important;
        box-shadow: none !important;
    }

    html.dark .site-icon-btn iconify-icon,
    html.dark .site-search-trigger iconify-icon,
    html.dark .site-search-close iconify-icon,
    html.dark .site-search-clear iconify-icon,
    html.dark .site-notifications-more iconify-icon,
    html.dark .mobile-sidebar-trigger svg,
    html.dark button[data-notifications-actions-btn] iconify-icon,
    html.dark button[data-user-menu-btn] iconify-icon,
    html.dark button[data-mobile-sidebar-toggle] svg,
    html.dark a.site-icon-btn iconify-icon,
    .dark .site-icon-btn iconify-icon,
    .dark .site-search-trigger iconify-icon,
    .dark .site-search-close iconify-icon,
    .dark .site-search-clear iconify-icon,
    .dark .site-notifications-more iconify-icon,
    .dark .mobile-sidebar-trigger svg,
    .dark button[data-notifications-actions-btn] iconify-icon,
    .dark button[data-user-menu-btn] iconify-icon,
    .dark button[data-mobile-sidebar-toggle] svg,
    .dark a.site-icon-btn iconify-icon {
        color: currentColor !important;
        fill: none !important;
        stroke: currentColor !important;
    }

    html.dark .site-status-dot,
    .dark .site-status-dot {
        background: #60a5fa !important;
        border-color: #020617 !important;
        box-shadow: none !important;
    }

    html.dark label.site-search-field,
    html.dark .site-search-field,
    .dark label.site-search-field,
    .dark .site-search-field {
        background: #020617 !important;
        background-color: #020617 !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
        box-shadow: none !important;
    }

    html.dark label.site-search-field input,
    html.dark .site-search-field input,
    html.dark input[data-search-input],
    .dark label.site-search-field input,
    .dark .site-search-field input,
    .dark input[data-search-input] {
        background: #020617 !important;
        background-color: #020617 !important;
        color: #f8fafc !important;
        caret-color: #60a5fa !important;
        box-shadow: none !important;
    }

    html.dark .site-search-icon,
    html.dark .site-search-field iconify-icon,
    .dark .site-search-icon,
    .dark .site-search-field iconify-icon {
        color: #cbd5e1 !important;
    }

    html.dark input[data-search-input]::placeholder,
    html.dark .site-search-field input::placeholder,
    .dark input[data-search-input]::placeholder,
    .dark .site-search-field input::placeholder {
        color: #94a3b8 !important;
        opacity: 1 !important;
    }

    html.dark .site-search-results-panel,
    .dark .site-search-results-panel {
        background: #0f172a !important;
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
        box-shadow: none !important;
    }

    html.dark .site-search-empty,
    .dark .site-search-empty {
        color: #94a3b8 !important;
        font-weight: 400 !important;
    }

    html.dark .site-search-all,
    .dark .site-search-all {
        color: #93c5fd !important;
        background: transparent !important;
        font-weight: 400 !important;
    }

    html.dark .site-search-all:hover,
    .dark .site-search-all:hover {
        background: #1e293b !important;
        color: #bfdbfe !important;
    }

    html.dark .site-notifications-panel,
    html.dark .site-menu-panel,
    .dark .site-notifications-panel,
    .dark .site-menu-panel {
        background: #020617 !important;
        background-color: #020617 !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
        box-shadow: none !important;
    }

    html.dark .site-notifications-panel::before,
    html.dark .site-menu-panel::before,
    .dark .site-notifications-panel::before,
    .dark .site-menu-panel::before {
        background: #020617 !important;
        background-color: #020617 !important;
        border-color: #334155 !important;
        box-shadow: none !important;
    }

    html.dark .site-notifications-panel-title,
    html.dark .site-user-menu-name,
    .dark .site-notifications-panel-title,
    .dark .site-user-menu-name {
        color: #f8fafc !important;
        font-weight: 400 !important;
    }

    html.dark .site-notifications-panel-subtitle,
    html.dark .site-notifications-empty,
    html.dark .site-user-menu-username,
    .dark .site-notifications-panel-subtitle,
    .dark .site-notifications-empty,
    .dark .site-user-menu-username {
        color: #94a3b8 !important;
        font-weight: 400 !important;
    }

    html.dark .site-user-menu-card,
    .dark .site-user-menu-card {
        background: #0f172a !important;
        background-color: #0f172a !important;
        border-color: #1e293b !important;
        color: #f8fafc !important;
        box-shadow: none !important;
    }

    html.dark .site-user-menu-link,
    html.dark .site-user-menu-button,
    .dark .site-user-menu-link,
    .dark .site-user-menu-button {
        color: #e2e8f0 !important;
        background: transparent !important;
        font-weight: 400 !important;
    }

    html.dark .site-user-menu-link:hover,
    html.dark .site-user-menu-button:hover,
    .dark .site-user-menu-link:hover,
    .dark .site-user-menu-button:hover {
        background: #1e293b !important;
        background-color: #1e293b !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }

    html.dark .site-user-menu-icon,
    .dark .site-user-menu-icon {
        background: #1e293b !important;
        background-color: #1e293b !important;
        color: #cbd5e1 !important;
        box-shadow: none !important;
    }

    html.dark .site-user-menu-link:hover .site-user-menu-icon,
    html.dark .site-user-menu-button:hover .site-user-menu-icon,
    .dark .site-user-menu-link:hover .site-user-menu-icon,
    .dark .site-user-menu-button:hover .site-user-menu-icon {
        background: #1e3a8a !important;
        background-color: #1e3a8a !important;
        color: #dbeafe !important;
    }

    html.dark .site-user-menu-divider,
    .dark .site-user-menu-divider,
    html.dark .site-notifications-panel-head,
    .dark .site-notifications-panel-head,
    html.dark .site-notifications-footer-link,
    .dark .site-notifications-footer-link {
        border-color: #1e293b !important;
        background-color: #1e293b !important;
    }

    html.dark .site-notifications-actions-menu,
    html.dark div[data-notifications-actions-menu],
    .dark .site-notifications-actions-menu,
    .dark div[data-notifications-actions-menu] {
        background: #020617 !important;
        background-color: #020617 !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
        box-shadow: none !important;
    }

    html.dark .site-notifications-menu-item,
    html.dark button[data-notifications-mark-all],
    html.dark button[data-notifications-delete-all],
    .dark .site-notifications-menu-item,
    .dark button[data-notifications-mark-all],
    .dark button[data-notifications-delete-all] {
        color: #e2e8f0 !important;
        background: transparent !important;
        font-weight: 400 !important;
    }

    html.dark .site-notifications-menu-item:hover,
    html.dark button[data-notifications-mark-all]:hover,
    html.dark button[data-notifications-delete-all]:hover,
    .dark .site-notifications-menu-item:hover,
    .dark button[data-notifications-mark-all]:hover,
    .dark button[data-notifications-delete-all]:hover {
        background: #1e293b !important;
        background-color: #1e293b !important;
        color: #ffffff !important;
    }

    html.dark .site-notifications-footer-link,
    .dark .site-notifications-footer-link {
        color: #93c5fd !important;
        background: transparent !important;
        font-weight: 400 !important;
    }

    html.dark .site-notifications-footer-link:hover,
    .dark .site-notifications-footer-link:hover {
        background: #0f172a !important;
        color: #bfdbfe !important;
    }

    html.dark #mobile-sidebar-drawer aside,
    html.dark [data-mobile-sidebar-panel],
    .dark #mobile-sidebar-drawer aside,
    .dark [data-mobile-sidebar-panel] {
        background: #020617 !important;
        background-color: #020617 !important;
        color: #f8fafc !important;
        border-color: #1e293b !important;
        box-shadow: none !important;
    }

    html.dark [data-mobile-sidebar-backdrop],
    .dark [data-mobile-sidebar-backdrop] {
        background: rgba(2, 6, 23, 0.72) !important;
    }

    html.dark [data-mobile-sidebar-panel] .border-b,
    .dark [data-mobile-sidebar-panel] .border-b {
        border-color: #1e293b !important;
    }

    html.dark [data-mobile-sidebar-close],
    .dark [data-mobile-sidebar-close] {
        background: #0f172a !important;
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
        box-shadow: none !important;
    }

    html.dark [data-mobile-sidebar-close]:hover,
    .dark [data-mobile-sidebar-close]:hover {
        background: #1e293b !important;
        background-color: #1e293b !important;
        color: #ffffff !important;
    }

    body.alma-app {
        padding-top: 64px !important;
    }

    body.alma-app .site-header,
    .site-header {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        height: 64px !important;
        min-height: 64px !important;
        background: rgba(255, 255, 255, 0.82) !important;
        background-color: rgba(255, 255, 255, 0.82) !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        border: 0 !important;
        box-shadow: none !important;
        filter: none !important;
        z-index: 9990 !important;
    }

    body.alma-app .site-header-shell,
    .site-header-shell {
        width: 100% !important;
        max-width: 1272px !important;
        height: 64px !important;
        min-height: 64px !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        align-items: center !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    /* Marka kilidi: logo ile ad arasındaki görünmez boşluğu kaldır. */
    html body .site-header .site-header-logo {
        display: inline-flex !important;
        align-items: center !important;
        gap: 2px !important;
        column-gap: 2px !important;
        font-family: "Roboto", Arial, Helvetica, sans-serif !important;
    }

    html body .site-header .site-header-logo-mark {
        width: 32px !important;
        height: 36px !important;
        min-width: 32px !important;
        flex: 0 0 32px !important;
    }

    html body .site-header .site-header-logo-mark .site-header-logo-main-image {
        width: 32px !important;
        height: 32px !important;
        max-width: 32px !important;
        max-height: 32px !important;
        inset: 2px 0 !important;
    }

    html body .site-header .site-header-logo-wordmark,
    html.dark body .site-header .site-header-logo-wordmark {
        margin: 0 !important;
        font-family: "Roboto", Arial, Helvetica, sans-serif !important;
        font-size: 20px !important;
        line-height: 1 !important;
        font-weight: 600 !important;
        letter-spacing: -0.01em !important;
    }

    /* Visual-only enlargement: no width, height, gap, padding or position changes. */
    html body .site-header .site-header-logo-main-image {
        transform: scale(1.30) !important;
        transform-origin: center !important;
    }

    html body .site-header .site-header-logo-wordmark:not(#comments *):not(#app *) {
        transform: scale(1.30) !important;
        transform-origin: left center !important;
    }

    html body .site-header :is(
        button[data-user-menu-btn],
        a.site-icon-btn[aria-label]
    ) > iconify-icon {
        transform: scale(1.18) !important;
        transform-origin: center !important;
    }

</style>

<header class="site-header" data-site-header style="background: rgba(255,255,255,.98) !important; background-color: rgba(255,255,255,.98) !important; color: #0f172a !important; filter: none !important; border-bottom: 1px solid #e5e7eb !important;">
    <div class="site-header-shell">
        @php
            $currentUser = auth()->user();
            $avatarUrl = $currentUser?->profile_photo_url
                ?? $currentUser?->avatar
                ?? $currentUser?->photo
                ?? null;
            $initial = $currentUser
                ? strtoupper(mb_substr($currentUser->name ?? 'U', 0, 1))
                : 'U';
            $unreadNotifications = $currentUser
                ? (int) $currentUser->unreadNotifications()->count()
                : 0;
            $unreadMessages = $currentUser
                ? (int) \App\Models\Message::query()
                    ->where('recipient_id', $currentUser->id)
                    ->whereNull('read_at')
                    ->where('deleted_by_recipient', false)
                    ->count()
                : 0;
        @endphp

        <div class="flex items-center gap-1.5 sm:gap-3">
            <div class="relative" data-logo-menu>
                <button
                    type="button"
                    class="mobile-sidebar-trigger lg:hidden"
                    aria-label="Menu"
                    aria-expanded="false"
                    aria-controls="mobile-sidebar-drawer"
                    data-mobile-sidebar-toggle
                    style="background: transparent !important; background-color: transparent !important; box-shadow: none !important; border-color: transparent !important;"
                >
                    <svg viewBox="0 0 24 24" width="1.2em" height="1.2em" class="h-6 w-6" aria-hidden="true">
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5h12M4 12h16M4 19h8"></path>
                    </svg>
                </button>
            </div>

            <a class="site-header-logo" href="{{ route('home') }}" aria-label="{{ config('app.name', 'Ografi') }}">
                <span class="site-header-logo-mark" aria-hidden="true">
                    <x-application-logo class="site-header-logo-image site-header-logo-light-image site-header-logo-main-image" style="width: var(--site-header-logo-size) !important; height: var(--site-header-logo-size) !important; font-size: var(--site-header-logo-size) !important;" />
                    <img class="site-header-logo-dark-image site-header-logo-main-image" style="width: var(--site-header-logo-size) !important; height: var(--site-header-logo-size) !important; font-size: var(--site-header-logo-size) !important;" src="{{ asset('images/ografi-logo.png') }}?v=20260714a" alt="">
                </span>
                <span class="site-header-logo-wordmark">Ografi</span>
            </a>
        </div>

        <div class="site-header-actions">
            <form action="{{ route('search') }}" method="GET" class="site-search-panel hidden lg:flex" data-search-shell>
                <button
                    type="button"
                    class="site-search-trigger"
                    aria-label="Ara"
                    aria-controls="site-header-search-dropdown"
                    aria-expanded="false"
                    data-search-trigger
                    style="background: transparent !important; background-color: transparent !important; box-shadow: none !important; border-color: transparent !important;"
                >
                    <iconify-icon icon="lucide:search" style="width: var(--site-header-icon-size) !important; height: var(--site-header-icon-size) !important; font-size: var(--site-header-icon-size) !important;"></iconify-icon>
                </button>

                <div id="site-header-search-dropdown" class="site-search-dropdown hidden" data-search-dropdown>
                    <div class="site-search-dropdown-top">
                        <label class="site-search-field">
                            <iconify-icon icon="lucide:search" class="site-search-icon"></iconify-icon>

                            <input
                                type="search"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="Kullanici, kategori, tag, post ve sayfa ara..."
                                autocomplete="off"
                                data-search-input
                            />

                            <button
                                type="button"
                                class="site-search-clear hidden"
                                aria-label="Aramayi temizle"
                                data-search-clear
                                style="background: transparent !important; background-color: transparent !important; box-shadow: none !important; border-color: transparent !important;"
                            >
                                <iconify-icon icon="lucide:x"></iconify-icon>
                            </button>
                        </label>

                        <button
                            type="button"
                            class="site-search-close"
                            aria-label="Aramayi kapat"
                            data-search-close
                        >
                            <iconify-icon icon="lucide:x"></iconify-icon>
                        </button>
                    </div>

                    <div class="site-search-results-panel">
                        <div class="site-search-results" data-search-results>
                            <p class="site-search-empty">Kullanici, kategori, tag, post veya sayfa aramak icin yaz.</p>
                        </div>

                        <a href="{{ route('search') }}" class="site-search-all" data-search-view-all>
                            <iconify-icon icon="lucide:corner-down-left"></iconify-icon>
                            <span data-search-view-all-label>Tum sonuclari goster</span>
                        </a>
                    </div>
                </div>
            </form>

            <a
                href="{{ route('blog.create') }}"
                class="site-header-write-btn site-header-desktop-only inline-flex items-center gap-2 text-sm"
                style="
                    height: 42px !important;
                    min-height: 42px !important;
                    padding: 0 18px !important;
                    border-radius: 9999px !important;
                    background: #2563eb !important;
                    background-color: #2563eb !important;
                    border: 1px solid #2563eb !important;
                    color: #ffffff !important;
                    font-weight: 400 !important;
                    box-shadow: none !important;
                    align-items: center !important;
                    justify-content: center !important;
                    transition: none !important;
                    transform: none !important;
                "
            >
                <iconify-icon icon="lucide:square-pen" style="font-size: 16px; color: #ffffff !important; background: transparent !important;"></iconify-icon>
                <span style="color: #ffffff !important; font-weight: 400 !important;">{{ __('site.common.write') }}</span>
            </a>

            @if ($currentUser)
                <div
                    class="relative site-header-desktop-only"
                    data-notifications-root
                    data-notifications-endpoint="{{ route('notifications.dropdown') }}"
                    data-notifications-index-url="{{ route('notifications.index') }}"
                    data-notifications-mark-all-url="{{ route('notifications.mark-all') }}"
                    data-notifications-delete-all-url="{{ route('notifications.delete-all') }}"
                >
                    <button
                        type="button"
                        class="site-icon-btn site-icon-btn--status"
                        aria-label="Bildirimler"
                        aria-expanded="false"
                        data-notifications-btn
                        style="background: transparent !important; background-color: transparent !important; box-shadow: none !important; border-color: transparent !important;"
                    >
                        <iconify-icon icon="lucide:bell" style="width: var(--site-header-icon-size) !important; height: var(--site-header-icon-size) !important; font-size: var(--site-header-icon-size) !important;"></iconify-icon>
                        <span class="site-status-dot {{ $unreadNotifications > 0 ? '' : 'hidden' }}" aria-hidden="true" data-notifications-dot></span>
                    </button>

                    <div class="site-notifications-panel hidden" data-notifications-panel style="background: #ffffff !important; background-color: #ffffff !important; color: #0f172a !important; border-color: #e2e8f0 !important; filter: none !important;">
                        <div class="site-notifications-panel-head">
                            <div>
                                <h3 class="site-notifications-panel-title">Bildirimler</h3>
                                <p class="site-notifications-panel-subtitle">
                                    Yeni hareketleri buradan takip et.
                                </p>
                            </div>

                            <div class="relative" data-notifications-actions>
                                <button
                                    type="button"
                                    class="site-notifications-more"
                                    aria-label="Bildirim islemleri"
                                    aria-expanded="false"
                                    data-notifications-actions-btn
                                    style="background: transparent !important; background-color: transparent !important; box-shadow: none !important; border-color: transparent !important;"
                                >
                                    <iconify-icon icon="lucide:ellipsis"></iconify-icon>
                                </button>

                                <div class="site-notifications-actions-menu hidden" data-notifications-actions-menu>
                                    <button type="button" class="site-notifications-menu-item" data-notifications-mark-all>
                                        <iconify-icon icon="lucide:check-check"></iconify-icon>
                                        <span>Tumunu okundu isaretle</span>
                                    </button>

                                    <button type="button" class="site-notifications-menu-item" data-notifications-delete-all>
                                        <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                        <span>Tumunu sil</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="site-notifications-list" data-notifications-list>
                            <p class="site-notifications-empty">Bildirimler yukleniyor...</p>
                        </div>

                        <a href="{{ route('notifications.index') }}" class="site-notifications-footer-link">
                            <span>Tum bildirimleri gor</span>
                            <iconify-icon icon="lucide:arrow-right" style="font-size: 15px;"></iconify-icon>
                        </a>
                    </div>
                </div>

                <a
                    href="{{ route('messages.index') }}"
                    class="site-icon-btn site-icon-btn--status site-header-desktop-only"
                    aria-label="Mesajlar"
                    style="background: transparent !important; background-color: transparent !important; box-shadow: none !important; border-color: transparent !important;"
                >
                    <iconify-icon icon="lucide:message-circle-more" style="width: var(--site-header-icon-size) !important; height: var(--site-header-icon-size) !important; font-size: var(--site-header-icon-size) !important;"></iconify-icon>
                    @if ($unreadMessages > 0)
                        <span class="site-status-dot" aria-hidden="true"></span>
                    @endif
                </a>

                <div class="relative" data-user-menu>
                    <button
                        type="button"
                        class="site-icon-btn p-0 overflow-hidden"
                        aria-label="{{ __('site.header.account') }}"
                        data-user-menu-btn
                        aria-expanded="false"
                        style="background: transparent !important; background-color: transparent !important; box-shadow: none !important; border-color: transparent !important;"
                    >
                        @if ($avatarUrl)
                            <img class="h-full w-full rounded-full object-cover" src="{{ $avatarUrl }}" alt="{{ $currentUser->name }}">
                        @else
                            <span class="site-avatar-fallback">{{ $initial }}</span>
                        @endif
                    </button>

                    <div class="site-menu-panel hidden" data-user-menu-panel style="background: #ffffff !important; background-color: #ffffff !important; color: #0f172a !important; border-color: #e2e8f0 !important; filter: none !important;">
                        <div class="site-user-menu-card">
                            <span class="site-user-menu-avatar">
                                @if ($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="{{ $currentUser->name }}">
                                @else
                                    {{ $initial }}
                                @endif
                            </span>

                            <div class="site-user-menu-info">
                                <span class="site-user-menu-name">{{ $currentUser->name }}</span>
                                <span class="site-user-menu-username">
                                    {{ '@' . ($currentUser->username ?? $currentUser->name ?? 'user') }}
                                </span>
                            </div>
                        </div>

                        <a href="{{ route('users.show', ['user' => $currentUser->username]) }}" class="site-user-menu-link">
                            <span class="site-user-menu-icon">
                                <iconify-icon icon="lucide:user-round" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <span>{{ __('site.common.profile') }}</span>
                        </a>

                        <a href="{{ route('dashboard') }}" class="site-user-menu-link">
                            <span class="site-user-menu-icon">
                                <iconify-icon icon="lucide:layout-dashboard" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <span>{{ __('site.common.dashboard') }}</span>
                        </a>

                        <a href="{{ route('blog.bookmarks') }}" class="site-user-menu-link">
                            <span class="site-user-menu-icon">
                                <iconify-icon icon="lucide:bookmark" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <span>{{ __('site.common.bookmarks') }}</span>
                        </a>

                        <div class="site-user-menu-divider"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="site-user-menu-button site-user-menu-button-danger">
                                <span class="site-user-menu-icon">
                                    <iconify-icon icon="lucide:log-out" style="font-size: 16px;"></iconify-icon>
                                </span>
                                <span>{{ __('site.common.sign_out') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a
                    href="{{ route('login') }}"
                    class="site-icon-btn"
                    aria-label="{{ __('site.header.login') }}"
                    style="background: transparent !important; background-color: transparent !important; box-shadow: none !important; border-color: transparent !important;"
                >
                    <iconify-icon icon="lucide:circle-user-round" style="width: var(--site-header-icon-size) !important; height: var(--site-header-icon-size) !important; font-size: var(--site-header-icon-size) !important;"></iconify-icon>
                </a>
            @endif
        </div>
    </div>
</header>

<div
    id="mobile-sidebar-drawer"
    class="pointer-events-none fixed inset-0 z-[70] lg:hidden"
    data-mobile-sidebar-drawer
    aria-hidden="true"
    inert
>
    <div class="absolute inset-0 bg-slate-950/40 opacity-0 transition-opacity duration-200" data-mobile-sidebar-backdrop></div>

    <aside
        class="absolute inset-y-0 left-0 flex h-full w-[88vw] max-w-[320px] -translate-x-full flex-col bg-white transition-none dark:bg-slate-900"
        data-mobile-sidebar-panel
        style="box-shadow: none !important;"
    >
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4 dark:border-slate-800">
            <a class="site-header-logo min-w-0" href="{{ route('home') }}" aria-label="{{ config('app.name', 'Ografi') }}">
                <span class="site-header-logo-mark" aria-hidden="true">
                    <x-application-logo class="site-header-logo-image site-header-logo-light-image site-header-logo-main-image" />
                    <img class="site-header-logo-dark-image site-header-logo-main-image" src="{{ asset('images/ografi-logo.png') }}?v=20260714a" alt="">
                </span>
                <span class="site-header-logo-wordmark">Ografi</span>
            </a>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    aria-label="Menüyü kapat"
                    data-mobile-sidebar-close
                    style="box-shadow: none !important; transition: none !important;"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-4 py-5">
            @include('partials.left', ['mobileSidebar' => true])
        </div>
    </aside>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const themeButtons = document.querySelectorAll('[data-theme-toggle]');
                const themeIcons = document.querySelectorAll('[data-theme-icon]');

                const syncThemeButtons = () => {
                    const isDark = document.documentElement.classList.contains('dark');

                    themeButtons.forEach((button) => {
                        button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                        button.setAttribute('aria-label', isDark ? 'Light mode' : 'Dark mode');
                        button.setAttribute('title', isDark ? 'Light mode' : 'Dark mode');
                    });

                    themeIcons.forEach((icon) => {
                        icon.setAttribute('icon', isDark ? 'lucide:sun' : 'lucide:moon');
                    });
                };

                themeButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const isDark = document.documentElement.classList.contains('dark');
                        window.setPreferredTheme?.(isDark ? 'light' : 'dark');
                    });
                });

                window.addEventListener('themechange', syncThemeButtons);
                syncThemeButtons();

                const mobileSidebarDrawer = document.querySelector('[data-mobile-sidebar-drawer]');
                const mobileSidebarPanel = mobileSidebarDrawer?.querySelector('[data-mobile-sidebar-panel]');
                const mobileSidebarBackdrop = mobileSidebarDrawer?.querySelector('[data-mobile-sidebar-backdrop]');
                const mobileSidebarOpeners = document.querySelectorAll('[data-mobile-sidebar-toggle]');
                const mobileSidebarClosers = mobileSidebarDrawer?.querySelectorAll('[data-mobile-sidebar-close]');
                const mobileSidebarLinks = mobileSidebarDrawer?.querySelectorAll('a');

                if (!mobileSidebarDrawer || !mobileSidebarPanel || mobileSidebarOpeners.length === 0) {
                    return;
                }

                const setExpanded = (value) => {
                    mobileSidebarOpeners.forEach((button) => {
                        button.setAttribute('aria-expanded', value ? 'true' : 'false');
                    });
                };

                const openMobileSidebar = () => {
                    mobileSidebarDrawer.classList.remove('pointer-events-none');
                    mobileSidebarDrawer.setAttribute('aria-hidden', 'false');
                    mobileSidebarDrawer.removeAttribute('inert');

                    requestAnimationFrame(() => {
                        mobileSidebarBackdrop?.classList.remove('opacity-0');
                        mobileSidebarPanel.classList.remove('-translate-x-full');
                    });

                    document.body.classList.add('overflow-hidden');
                    setExpanded(true);
                };

                const closeMobileSidebar = () => {
                    mobileSidebarBackdrop?.classList.add('opacity-0');
                    mobileSidebarPanel.classList.add('-translate-x-full');
                    mobileSidebarDrawer.setAttribute('aria-hidden', 'true');
                    mobileSidebarDrawer.setAttribute('inert', '');
                    document.body.classList.remove('overflow-hidden');
                    setExpanded(false);

                    window.setTimeout(() => {
                        if (mobileSidebarDrawer.getAttribute('aria-hidden') === 'true') {
                            mobileSidebarDrawer.classList.add('pointer-events-none');
                        }
                    }, 200);
                };

                mobileSidebarOpeners.forEach((button) => {
                    button.addEventListener('click', (event) => {
                        event.preventDefault();

                        const isClosed = mobileSidebarDrawer.classList.contains('pointer-events-none');

                        if (isClosed) {
                            openMobileSidebar();
                            return;
                        }

                        closeMobileSidebar();
                    });
                });

                mobileSidebarClosers?.forEach((button) => {
                    button.addEventListener('click', closeMobileSidebar);
                });

                mobileSidebarBackdrop?.addEventListener('click', closeMobileSidebar);

                mobileSidebarLinks?.forEach((link) => {
                    link.addEventListener('click', closeMobileSidebar);
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key !== 'Escape') {
                        return;
                    }

                    closeMobileSidebar();
                });

                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 1024) {
                        closeMobileSidebar();
                    }
                });
            });
        </script>
    @endpush
@endonce

<style>
/* Header ikonlarinin tek boyutlandirma noktasi. Yalnizca bu degeri degistirin. */
html body .site-header {
    --site-header-icon-size: 24px;
    --site-header-logo-size: 34px;
}

@media (max-width: 640px) {
    html body .site-header {
        --site-header-logo-size: 38px;
    }
}

html body .site-header :is(
    .site-search-trigger,
    button[data-notifications-btn],
    :is(a, button).site-icon-btn
) {
    position: relative !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    align-self: center !important;
    line-height: 0 !important;
    vertical-align: middle !important;
}

html body .site-header :is(
    .site-search-trigger,
    button[data-notifications-btn],
    :is(a, button).site-icon-btn[aria-label]
) > iconify-icon {
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    margin: 0 !important;
    transform: translate(-50%, -50%) !important;
}

html body .site-header .site-header-logo-mark,
html body .site-header .site-header-logo-mark .site-header-logo-main-image,
html body .site-header .mobile-sidebar-trigger > svg,
html body .site-header :is(.site-search-trigger, button[data-notifications-btn], :is(a, button).site-icon-btn[aria-label]) > iconify-icon,
html body .site-header button[data-user-menu-btn] > :is(img, .site-avatar-fallback) {
    display: block !important;
    flex: 0 0 var(--site-header-icon-size) !important;
    align-self: center !important;
    width: var(--site-header-icon-size) !important;
    height: var(--site-header-icon-size) !important;
    min-width: var(--site-header-icon-size) !important;
    max-width: var(--site-header-icon-size) !important;
    min-height: var(--site-header-icon-size) !important;
    max-height: var(--site-header-icon-size) !important;
    font-size: var(--site-header-icon-size) !important;
    line-height: var(--site-header-icon-size) !important;
    vertical-align: middle !important;
}

html body .site-header .site-header-logo-mark {
    flex: 0 0 var(--site-header-icon-size) !important;
}

html body .site-header .site-header-logo-mark .site-header-logo-main-image,
html body .site-header button[data-user-menu-btn] > :is(img, .site-avatar-fallback) {
    transform: none !important;
}

html body .site-search-dropdown-top {
    gap: 10px !important;
    align-items: center !important;
}

html body .site-search-dropdown-top label.site-search-field {
    height: 40px !important;
    min-height: 40px !important;
    padding: 0 14px !important;
    border: 1px solid #dfe3e8 !important;
    border-radius: 9999px !important;
    background: #fff !important;
    box-shadow: none !important;
}

html body .site-search-dropdown-top .site-search-icon {
    width: 18px !important;
    height: 18px !important;
    min-width: 18px !important;
    font-size: 18px !important;
    color: #525866 !important;
}

html body .site-search-dropdown-top input[data-search-input] {
    height: 38px !important;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
    font-size: 14px !important;
    line-height: 38px !important;
    outline: 0 !important;
    box-shadow: none !important;
}

html body .site-search-dropdown-top input[data-search-input]:not(#comments *):not(#app *),
html body .site-search-dropdown-top input[data-search-input]:not(#comments *):not(#app *):is(:hover, :focus, :focus-visible, :active),
html body .site-search-dropdown-top input[data-search-input]:not(#comments *):not(#app *):-webkit-autofill {
    background: #ffffff !important;
    background-color: #ffffff !important;
    -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
    box-shadow: 0 0 0 1000px #ffffff inset !important;
}

html body .site-search-dropdown-top input[data-search-input]::placeholder {
    color: #6b7280 !important;
    opacity: 1 !important;
}

html body .site-search-dropdown-top .site-search-clear {
    width: 28px !important;
    height: 28px !important;
    min-width: 28px !important;
    min-height: 28px !important;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
    color: #666 !important;
}

html body .site-search-dropdown-top .site-search-clear :is(iconify-icon, svg) {
    width: 17px !important;
    height: 17px !important;
    font-size: 17px !important;
}

html body .site-search-dropdown-top .site-search-close {
    width: 40px !important;
    height: 40px !important;
    min-width: 40px !important;
    min-height: 40px !important;
    flex: 0 0 40px !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 9999px !important;
    background: #f3f6fc !important;
    color: #2563eb !important;
    box-shadow: none !important;
}

html body .site-search-dropdown-top .site-search-close:is(:hover, :focus, :focus-visible, :active) {
    border: 0 !important;
    background: #f3f6fc !important;
    background-color: #f3f6fc !important;
    color: #2563eb !important;
    box-shadow: none !important;
}

html body .site-search-dropdown-top .site-search-close :is(iconify-icon, svg) {
    width: 20px !important;
    height: 20px !important;
    font-size: 20px !important;
    line-height: 20px !important;
}

</style>
