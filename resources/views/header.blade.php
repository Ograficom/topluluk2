<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap');

    .site-header,
    .site-header *,
    .site-menu-panel,
    .site-notifications-panel,
    .site-notifications-actions-menu {
        font-family: "Inter", Arial, Helvetica, sans-serif !important;
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
        box-shadow: 0 12px 32px -8px rgba(15, 23, 42, 0.16), 0 2px 8px rgba(15, 23, 42, 0.06) !important;
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
        box-shadow: -2px -2px 4px -2px rgba(15, 23, 42, 0.06) !important;
    }

    .site-user-menu-card {
        position: relative !important;
        z-index: 2 !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        padding: 14px 12px !important;
        margin-bottom: 8px !important;
        border-radius: 18px !important;
        background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 65%) !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
        text-decoration: none !important;
        cursor: pointer !important;
        transition: none !important;
    }

    .site-user-menu-card:hover {
        background: linear-gradient(135deg, #dbeafe 0%, #f1f5f9 65%) !important;
        border-color: #bfdbfe !important;
    }

    .site-user-menu-avatar {
        width: 48px !important;
        height: 48px !important;
        border-radius: 9999px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 auto !important;
        overflow: hidden !important;
        background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%) !important;
        color: #ffffff !important;
        font-size: 15px !important;
        font-weight: 600 !important;
        letter-spacing: 0.01em !important;
        border: 2px solid #ffffff !important;
        outline: 1px solid #dbeafe !important;
        box-sizing: border-box !important;
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
        font-size: 14.5px !important;
        font-weight: 600 !important;
        line-height: 1.25 !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .site-user-menu-meta {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        margin-top: 2px !important;
        min-width: 0 !important;
    }

    .site-user-menu-username {
        display: block !important;
        color: #64748b !important;
        font-size: 12px !important;
        font-weight: 400 !important;
        line-height: 1.25 !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
        min-width: 0 !important;
    }

    .site-user-menu-points {
        display: inline-flex !important;
        align-items: center !important;
        gap: 3px !important;
        flex: 0 0 auto !important;
        padding: 1px 7px !important;
        border-radius: 999px !important;
        background: #fef3c7 !important;
        color: #b45309 !important;
        font-size: 11.5px !important;
        font-weight: 700 !important;
        line-height: 1.6 !important;
    }

    .site-user-menu-link--switch {
        cursor: pointer !important;
    }

    .site-user-menu-switch {
        margin-left: auto !important;
        flex: 0 0 auto !important;
        position: relative !important;
        width: 34px !important;
        height: 20px !important;
        border-radius: 999px !important;
        background: #d1d5db !important;
        transition: none !important;
    }

    .site-user-menu-switch__knob {
        position: absolute !important;
        top: 2px !important;
        left: 2px !important;
        width: 16px !important;
        height: 16px !important;
        border-radius: 999px !important;
        background: #ffffff !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.25) !important;
        transition: none !important;
    }

    .site-user-menu-link--switch[aria-pressed="true"] .site-user-menu-switch {
        background: #2563eb !important;
    }

    .site-user-menu-link--switch[aria-pressed="true"] .site-user-menu-switch__knob {
        left: 16px !important;
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
        background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%) !important;
        background-image: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%) !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        letter-spacing: 0.01em !important;
        box-shadow: none !important;
    }

    button[data-user-menu-btn] {
        border-radius: 9999px !important;
    }

    button[data-user-menu-btn] > :is(img, .site-avatar-fallback) {
        box-sizing: border-box !important;
        box-shadow: inset 0 0 0 1.5px rgba(255, 255, 255, 0.9) !important;
    }

    button[data-user-menu-btn][aria-expanded="true"] > :is(img, .site-avatar-fallback) {
        box-shadow: inset 0 0 0 1.5px #bfdbfe !important;
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

    html.dark .site-menu-panel,
    .dark .site-menu-panel {
        box-shadow: 0 12px 32px -8px rgba(0, 0, 0, 0.45), 0 2px 8px rgba(0, 0, 0, 0.25) !important;
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
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
        background-image: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
        color: #ffffff !important;
        font-weight: 600 !important;
    }

    html.dark button[data-user-menu-btn] > :is(img, .site-avatar-fallback),
    .dark button[data-user-menu-btn] > :is(img, .site-avatar-fallback) {
        box-shadow: inset 0 0 0 1.5px rgba(15, 23, 42, 0.85) !important;
    }

    html.dark .site-user-menu-card,
    .dark .site-user-menu-card {
        background: linear-gradient(135deg, #1e293b 0%, #111827 65%) !important;
        border-color: #1e293b !important;
        box-shadow: none !important;
    }

    html.dark .site-user-menu-avatar,
    .dark .site-user-menu-avatar {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
        color: #ffffff !important;
        border-color: #111827 !important;
        outline-color: #1e3a8a !important;
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
        margin-top: 8px !important;
        padding: 8px !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
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

    {{--
        ONEMLI: Header'in karanlik moddeki rengi TEK bir yerden yonetiliyor:
        partials/system-appearance.blade.php icindeki "html.dark .site-header"
        kurali. Burada AYNI secici ile tekrar tanimlamayin - iki farkli dosyada
        ayni seciciyle cakisan kurallar, hangi sablonun (home-like, blog/show
        vb.) once/sonra render edildigine gore header'in sayfadan sayfaya
        farkli (bazen acik, bazen koyu) gorunmesine yol aciyordu.
    --}}
    html.dark .site-header-logo-wordmark,
    .dark .site-header-logo-wordmark {
        font-weight: 400 !important;
    }

    .site-header .site-header-logo-wordmark {
        font-family: "Poppins", "Inter", Arial, Helvetica, sans-serif !important;
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
        font-family: "Poppins", "Inter", Arial, Helvetica, sans-serif !important;
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

    html.dark .site-menu-panel,
    .dark .site-menu-panel {
        box-shadow: 0 12px 32px -8px rgba(0, 0, 0, 0.5), 0 2px 8px rgba(0, 0, 0, 0.3) !important;
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
        color: #0f172a !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        border: 0 !important;
        border-bottom: 1px solid #e5e7eb !important;
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
        font-family: "Inter", Arial, Helvetica, sans-serif !important;
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
        font-family: "Inter", Arial, Helvetica, sans-serif !important;
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

<header class="site-header" data-site-header>
    <div class="site-header-shell">
        @php
            $currentUser = auth()->user();
            $avatarUrl = $currentUser?->profile_photo_url
                ?? $currentUser?->avatar
                ?? $currentUser?->photo
                ?? null;
            // Gercek fotograf yoksa Jetstream, ui-avatars.com'dan solgun/marka disi
            // bir yer tutucu uretiyor; onun yerine kendi gradyanli ilk harf
            // rozetimizi gostermek icin bu jenerik yer tutucuyu yok sayiyoruz.
            if ($avatarUrl && str_contains($avatarUrl, 'ui-avatars.com')) {
                $avatarUrl = null;
            }
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
                    aria-label="{{ __('site.header.menu') }}"
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
                    aria-label="{{ __('site.common.search') }}"
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
                                placeholder="{{ __('site.header.search_full_placeholder') }}"
                                autocomplete="off"
                                data-search-input
                            />

                            <button
                                type="button"
                                class="site-search-clear hidden"
                                aria-label="{{ __('site.header.clear_search') }}"
                                data-search-clear
                                style="background: transparent !important; background-color: transparent !important; box-shadow: none !important; border-color: transparent !important;"
                            >
                                <iconify-icon icon="lucide:x"></iconify-icon>
                            </button>
                        </label>

                        <button
                            type="button"
                            class="site-search-close"
                            aria-label="{{ __('site.header.close_search') }}"
                            data-search-close
                        >
                            <iconify-icon icon="lucide:x"></iconify-icon>
                        </button>
                    </div>

                    <div class="site-search-results-panel">
                        <div class="site-search-results" data-search-results>
                            <p class="site-search-empty">{{ __('site.header.search_empty_hint') }}</p>
                        </div>

                        <a href="{{ route('search') }}" class="site-search-all" data-search-view-all>
                            <iconify-icon icon="lucide:corner-down-left"></iconify-icon>
                            <span data-search-view-all-label>{{ __('site.header.view_all_results') }}</span>
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
                    class="relative"
                    data-notifications-root
                    data-notifications-endpoint="{{ route('notifications.dropdown') }}"
                    data-notifications-index-url="{{ route('notifications.index') }}"
                    data-notifications-mark-all-url="{{ route('notifications.mark-all') }}"
                    data-notifications-delete-all-url="{{ route('notifications.delete-all') }}"
                >
                    <button
                        type="button"
                        class="site-icon-btn site-icon-btn--status"
                        aria-label="{{ __('site.header.notifications') }}"
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
                                <h3 class="site-notifications-panel-title">{{ __('site.header.notifications') }}</h3>
                                <p class="site-notifications-panel-subtitle">
                                    {{ __('site.header.notifications_subtitle') }}
                                </p>
                            </div>

                            <div class="relative" data-notifications-actions>
                                <button
                                    type="button"
                                    class="site-notifications-more"
                                    aria-label="{{ __('site.header.notification_actions') }}"
                                    aria-expanded="false"
                                    data-notifications-actions-btn
                                    style="background: transparent !important; background-color: transparent !important; box-shadow: none !important; border-color: transparent !important;"
                                >
                                    <iconify-icon icon="lucide:ellipsis"></iconify-icon>
                                </button>

                                <div class="site-notifications-actions-menu hidden" data-notifications-actions-menu>
                                    <button type="button" class="site-notifications-menu-item" data-notifications-mark-all>
                                        <iconify-icon icon="lucide:check-check"></iconify-icon>
                                        <span>{{ __('site.header.mark_all_read') }}</span>
                                    </button>

                                    <button type="button" class="site-notifications-menu-item" data-notifications-delete-all>
                                        <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                        <span>{{ __('site.header.delete_all') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="site-notifications-list" data-notifications-list>
                            <p class="site-notifications-empty">{{ __('site.header.notifications_loading') }}</p>
                        </div>

                        <a href="{{ route('notifications.index') }}" class="site-notifications-footer-link">
                            <span>{{ __('site.header.view_all_notifications') }}</span>
                            <iconify-icon icon="lucide:arrow-right" style="font-size: 15px;"></iconify-icon>
                        </a>
                    </div>
                </div>

                {{--
                    Mobilde alt gezinme cubugunda zaten kendi Mesajlar sekmesi var,
                    bu yuzden ust bardaki bu ikon orada gereksiz tekrar oluyor - mobilde
                    gizlenip yerini soldaki bildirim zilinin almasi icin ozel bir sinif
                    kullaniliyor (site-header-desktop-only hicbir CSS'e baglanmadigi
                    icin tek basina bunu saglamiyordu).
                --}}
                <style>
                    @media (max-width: 639.98px) {
                        .site-header-messages-link {
                            display: none !important;
                        }
                    }
                </style>
                <a
                    href="{{ route('messages.index') }}"
                    class="site-icon-btn site-icon-btn--status site-header-desktop-only site-header-messages-link"
                    aria-label="{{ __('site.header.messages') }}"
                    style="background: transparent !important; background-color: transparent !important; box-shadow: none !important; border-color: transparent !important;"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width: var(--site-header-icon-size) !important; height: var(--site-header-icon-size) !important;">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.25 12a9.23 9.23 0 0 1-2.705 6.54A9.25 9.25 0 0 1 12 21.25a9.2 9.2 0 0 1-3.795-.81l-3.867.572a1.195 1.195 0 0 1-1.361-1.43l.537-3.923A8.9 8.9 0 0 1 2.75 12a9.23 9.23 0 0 1 2.705-6.54A9.25 9.25 0 0 1 12 2.75a9.26 9.26 0 0 1 6.545 2.71A9.24 9.24 0 0 1 21.25 12" />
                    </svg>
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
                        <a href="{{ route('users.show', ['user' => $currentUser->username]) }}" class="site-user-menu-card">
                            <span class="site-user-menu-avatar">
                                @if ($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="{{ $currentUser->name }}">
                                @else
                                    {{ $initial }}
                                @endif
                            </span>

                            <div class="site-user-menu-info">
                                <span class="site-user-menu-name">{{ $currentUser->name }}</span>
                                <span class="site-user-menu-meta">
                                    <span class="site-user-menu-username">
                                        {{ '@' . ($currentUser->username ?? $currentUser->name ?? 'user') }}
                                    </span>
                                    @if ((int) ($currentUser->badge_points ?? 0) > 0)
                                        <span class="site-user-menu-points">
                                            <iconify-icon icon="lucide:award" style="font-size: 14px;"></iconify-icon>
                                            {{ number_format((int) $currentUser->badge_points) }}
                                        </span>
                                    @endif
                                </span>
                            </div>
                        </a>

                        <button
                            type="button"
                            class="site-user-menu-link site-user-menu-link--switch"
                            data-user-menu-theme-toggle
                            data-label-dark="{{ __('site.common.dark_mode') }}"
                            data-label-light="{{ __('site.common.light_mode') }}"
                            aria-pressed="false"
                        >
                            <span class="site-user-menu-icon">
                                <iconify-icon icon="lucide:moon" data-user-menu-theme-icon style="font-size: 16px;"></iconify-icon>
                            </span>
                            <span>{{ __('site.common.dark_mode') }}</span>
                            <span class="site-user-menu-switch" data-user-menu-theme-switch aria-hidden="true">
                                <span class="site-user-menu-switch__knob"></span>
                            </span>
                        </button>

                        <a
                            href="{{ route('locale.switch', ['locale' => ($currentLocale ?? 'tr') === 'en' ? 'tr' : 'en']) }}"
                            class="site-user-menu-link"
                        >
                            <span class="site-user-menu-icon">
                                <iconify-icon icon="lucide:languages" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <span>{{ __('site.common.language') }}: {{ ($currentLocale ?? 'tr') === 'en' ? 'English' : 'Turkce' }}</span>
                        </a>

                        <div class="site-user-menu-divider"></div>

                        <a href="{{ route('dashboard') }}" class="site-user-menu-link">
                            <span class="site-user-menu-icon">
                                <iconify-icon icon="lucide:layout-dashboard" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <span>{{ __('site.common.dashboard') }}</span>
                        </a>

                        <a href="{{ route('blog.categories') }}" class="site-user-menu-link">
                            <span class="site-user-menu-icon">
                                <iconify-icon icon="lucide:users" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <span>{{ __('site.common.my_communities') }}</span>
                        </a>

                        <a href="{{ route('blog.drafts') }}" class="site-user-menu-link">
                            <span class="site-user-menu-icon">
                                <iconify-icon icon="lucide:file-edit" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <span>{{ __('site.common.drafts') }}</span>
                        </a>

                        <a href="{{ route('blog.bookmarks') }}" class="site-user-menu-link">
                            <span class="site-user-menu-icon">
                                <iconify-icon icon="lucide:bookmark" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <span>{{ __('site.common.bookmarks') }}</span>
                        </a>

                        <a href="{{ route('dashboard.profile') }}" class="site-user-menu-link">
                            <span class="site-user-menu-icon">
                                <iconify-icon icon="lucide:settings" style="font-size: 16px;"></iconify-icon>
                            </span>
                            <span>{{ __('site.common.settings') }}</span>
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
                    aria-label="{{ __('site.header.close_menu') }}"
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
                const themeButtons = document.querySelectorAll('[data-theme-toggle], [data-user-menu-theme-toggle]');
                const themeIcons = document.querySelectorAll('[data-theme-icon], [data-user-menu-theme-icon]');

                const syncThemeButtons = () => {
                    const isDark = document.documentElement.classList.contains('dark');

                    themeButtons.forEach((button) => {
                        const label = isDark
                            ? (button.dataset.labelLight || 'Light mode')
                            : (button.dataset.labelDark || 'Dark mode');
                        button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                        button.setAttribute('aria-label', label);
                        button.setAttribute('title', label);
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

            (() => {
                // Arama/Etiketler/Kullanicilar sayfalarinda mobilde: asagi
                // kaydirinca basligi gizle, yukari kaydirinca geri getir -
                // Twitter/iOS Safari tarzi "hide on scroll" davranisi.
                // Kaydirma miktariyla 1:1 takip ediyor (Response + Direct
                // manipulation ilkeleri) - esik degeri yerine surekli geri
                // bildirim, ani "gorunur/gizli" ziplamasi yerine akici bir
                // his veriyor. Sayfanin kendi kimlik kutusu (page-title-
                // identity/og-search-identity) CSS ile position:fixed - "top"
                // degeri her karede baslikla AYNI miktarda guncelleniyor
                // (headerHeight - hiddenAmount) - yoksa baslik geri gelirken
                // ikisi ayni yerde ust uste biner, kimlik kutusu baslikta
                // kalirken (yuksek z-index) tamamen kaybolurdu. Kutu normal
                // akistan ciktigi icin hemen altindaki [data-identity-spacer]
                // onun yuksekligi kadar yer tutup icerigin ziplamasini onler.
                const header = document.querySelector('[data-site-header]');
                if (!header) return;

                const targetRouteClasses = ['route-search', 'route-tags', 'route-users'];
                if (!targetRouteClasses.some((cls) => document.body.classList.contains(cls))) return;

                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

                const identityBox = document.querySelector('.og-search-identity, .page-title-identity');
                const identitySpacer = document.querySelector('[data-identity-spacer]');
                const searchPanel = document.querySelector('[data-tags-search-panel], [data-users-search-panel]');
                const contentList = document.querySelector('[data-search-results-container], [data-tags-list], [data-users-list]');
                // Kimlik kutusunun VE arama panelinin kendi __edge-blur
                // cocuklari - ayni class isimlerini paylastiklari icin tek
                // sorguda ikisi de yakalaniyor, panel acilinca (skill #12:
                // "Never stack a light translucent surface on another" -
                // yalnizca dar/dikey seritler, komple ekrani degil) o da
                // ayni hizla-guclenen blur'u alir.
                const edgeBlurEls = Array.from(document.querySelectorAll('.page-title-identity__edge-blur, .og-search-identity__edge-blur'));
                const mobileQuery = window.matchMedia('(max-width: 640px)');
                const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
                const isSearchOpen = () => searchPanel?.classList.contains('is-open');

                // Baslik ile kimlik kutusu artik bitişik degil - aralarinda
                // kucuk bir bosluk birakiliyor (GAP), bu bosluk da (kutunun
                // __edge-blur-top cocugu ile) hafifce bulaniklastiriliyor -
                // kutunun ALTINDAKI blur efektinin simetrisi.
                const GAP = 10;

                // Motion blur ayarlari: kaydirma hizi (bir onceki scroll
                // olayindan bu yana kat edilen px) MAX_BLUR_SPEED'e ulasinca
                // tam MAX_BLUR'a ulasir; kaydirma durunca CLEAR_DELAY ms
                // sonra 0'a doner (CSS transition ile yumusakca). Kenar
                // bulanikligi (edgeBlurEls - basligin ustundeki/kutunun
                // altindaki seritler) CSS'teki sabit 20px tabanindan, hizli
                // kaydirirken +EDGE_BLUR_BOOST kadar daha da guclenip ayni
                // ritimle geri doner - butun gecis bolgesi (baslik -> bosluk
                // -> kutu -> icerik) TEK bir "hizla birlikte bulanan malzeme"
                // gibi tepki verir, "kalan bosluklari motion blur ile
                // doldur" talebinin karsiligi.
                const MAX_BLUR = 5;
                const MAX_BLUR_SPEED = 70;
                const EDGE_BLUR_BASE = 20;
                const EDGE_BLUR_BOOST = 12;
                const CLEAR_DELAY = 120;

                let hiddenAmount = 0;
                let headerHeight = header.offsetHeight;
                let lastScrollY = window.scrollY;
                let lastDelta = 0;
                let ticking = false;
                let blurClearTimer = null;

                const syncSpacerHeight = () => {
                    if (identityBox && identitySpacer) {
                        identitySpacer.style.height = `${identityBox.offsetHeight + GAP}px`;
                    }
                };

                const resetHeader = () => {
                    hiddenAmount = 0;
                    header.style.transform = '';
                    if (identityBox) identityBox.style.top = '';
                    if (searchPanel) searchPanel.style.top = '';
                    if (contentList) contentList.style.filter = '';
                    edgeBlurEls.forEach((el) => {
                        el.style.backdropFilter = '';
                        el.style.webkitBackdropFilter = '';
                    });
                };

                const applyTransform = () => {
                    header.style.transform = hiddenAmount > 0 ? `translateY(-${hiddenAmount}px)` : '';
                    document.body.style.setProperty('--route-header-height', `${headerHeight - hiddenAmount}px`);

                    if (identityBox) {
                        const identityTop = headerHeight - hiddenAmount + GAP;
                        identityBox.style.top = `${identityTop}px`;
                        if (searchPanel) {
                            searchPanel.style.top = `${identityTop + identityBox.offsetHeight}px`;
                        }
                    }

                    const speedRatio = clamp(Math.abs(lastDelta) / MAX_BLUR_SPEED, 0, 1);

                    // Arama paneli acikken (yaziyor/okuyor) liste genelini
                    // bulaniklastirmiyoruz - "arama kutusunu cevreleyen
                    // dikdortgen ... komple ekrani blur salmasin" talebi:
                    // blur SADECE panelin kendi dar kenar seritlerinde kalir.
                    if (contentList && !isSearchOpen()) {
                        const blurAmount = speedRatio * MAX_BLUR;
                        contentList.style.filter = blurAmount > 0.15 ? `blur(${blurAmount.toFixed(2)}px)` : '';
                    } else if (contentList && isSearchOpen()) {
                        contentList.style.filter = '';
                    }

                    if (edgeBlurEls.length) {
                        const edgeBlurPx = EDGE_BLUR_BASE + speedRatio * EDGE_BLUR_BOOST;
                        const edgeFilter = `blur(${edgeBlurPx.toFixed(1)}px) saturate(180%)`;
                        edgeBlurEls.forEach((el) => {
                            el.style.backdropFilter = edgeFilter;
                            el.style.webkitBackdropFilter = edgeFilter;
                        });
                    }

                    clearTimeout(blurClearTimer);
                    blurClearTimer = setTimeout(() => {
                        if (contentList) contentList.style.filter = '';
                        edgeBlurEls.forEach((el) => {
                            el.style.backdropFilter = '';
                            el.style.webkitBackdropFilter = '';
                        });
                    }, CLEAR_DELAY);

                    ticking = false;
                };

                const onScroll = () => {
                    if (!mobileQuery.matches) {
                        if (hiddenAmount !== 0) resetHeader();
                        lastScrollY = window.scrollY;
                        return;
                    }

                    const currentY = window.scrollY;
                    const delta = currentY - lastScrollY;
                    lastScrollY = currentY;
                    lastDelta = delta;

                    hiddenAmount = currentY <= 0 ? 0 : clamp(hiddenAmount + delta, 0, headerHeight);

                    if (!ticking) {
                        ticking = true;
                        window.requestAnimationFrame(applyTransform);
                    }
                };

                window.addEventListener('resize', () => {
                    headerHeight = header.offsetHeight;
                    syncSpacerHeight();
                });

                mobileQuery.addEventListener('change', (event) => {
                    if (!event.matches) resetHeader();
                });

                window.addEventListener('scroll', onScroll, { passive: true });

                // Ilk render'da kimlik kutusu (fixed) hemen dogru "top" (=
                // headerHeight, cunku henuz kaydirma yok) degerini alsin,
                // yoksa bir sonraki scroll olayina kadar top:0'da kalip
                // basligin ustune biner.
                syncSpacerHeight();
                document.body.style.setProperty('--route-header-height', `${headerHeight}px`);
                applyTransform();
            })();
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
        --site-header-logo-size: 40px;
    }

    html body .site-header .site-header-logo-mark,
    html body .site-header .site-header-logo-mark .site-header-logo-main-image {
        flex: 0 0 var(--site-header-logo-size) !important;
        width: var(--site-header-logo-size) !important;
        height: var(--site-header-logo-size) !important;
        min-width: var(--site-header-logo-size) !important;
        max-width: var(--site-header-logo-size) !important;
        min-height: var(--site-header-logo-size) !important;
        max-height: var(--site-header-logo-size) !important;
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

/* Logo PNG'sindeki seffaf kenarlari telafi et; masaustu ve mobilde gercek logoyu buyut. */
html body .site-header .site-header-logo-mark {
    flex: 0 0 var(--site-header-logo-size) !important;
    width: var(--site-header-logo-size) !important;
    height: var(--site-header-logo-size) !important;
    min-width: var(--site-header-logo-size) !important;
    max-width: var(--site-header-logo-size) !important;
    min-height: var(--site-header-logo-size) !important;
    max-height: var(--site-header-logo-size) !important;
    overflow: visible !important;
}

html body .site-header .site-header-logo-mark .site-header-logo-main-image {
    width: var(--site-header-logo-size) !important;
    height: var(--site-header-logo-size) !important;
    min-width: var(--site-header-logo-size) !important;
    max-width: var(--site-header-logo-size) !important;
    min-height: var(--site-header-logo-size) !important;
    max-height: var(--site-header-logo-size) !important;
    transform: scale(1.35) !important;
    transform-origin: center !important;
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

{{--
    ONEMLI - TEK GECERLI KURAL: Dosyanin baska yerlerinde (ve layouts/app.blade.php,
    templates/home-like.blade.php gibi diger dosyalarda) header'i "her zaman acik
    renk" yapan birden fazla eski/cakisan kural birikmisti - bu da header'in
    sayfadan sayfaya bazen koyu bazen acik gorunmesine yol aciyordu. Bu blok, bu
    dosyadaki EN SON <style> etiketinin en sonunda oldugu ve yuksek ozguelluge
    sahip oldugu icin, karanlik moddaki header rengi icin GERCEKTEN kazanan
    kural budur. Baska yerde ".site-header" icin karanlik mod kurali eklemeyin.
--}}
html.dark body.alma-app .site-header[data-site-header],
html.dark body .site-header[data-site-header] {
    background: rgba(15, 23, 42, 0.92) !important;
    background-color: rgba(15, 23, 42, 0.92) !important;
    background-image: none !important;
    border-bottom-color: rgba(148, 163, 184, 0.16) !important;
    color: #e5e7eb !important;
    box-shadow: none !important;
}

html.dark body.alma-app .site-header[data-site-header] .site-header-logo-wordmark,
html.dark body .site-header[data-site-header] .site-header-logo-wordmark {
    color: #e5e7eb !important;
}

/* ============================
   DROPDOWN MENU - EKRAN GORUNTUSUNDEKI GIBI (RAFINE)
   Ikon arkasinda kutu/daire yok, duz sade liste.
   Ic duzen: tum icerik ayni sol hizada (20px), hover
   zemini ise kenarlara tasarak tam genislik hissi verir.
   ============================ */
.site-menu-panel {
    width: 240px !important;
    padding: 14px 16px 10px !important;
    border-radius: 16px !important;
    box-shadow: 0 16px 40px -10px rgba(15, 23, 42, 0.16), 0 2px 8px rgba(15, 23, 42, 0.06) !important;
    border-color: #eef1f5 !important;
}

.site-user-menu-card {
    padding: 0 0 12px 0 !important;
    margin: 0 0 6px 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    border: 0 !important;
    border-bottom: 1px solid #f1f5f9 !important;
    gap: 11px !important;
}

.site-user-menu-card:hover {
    background: transparent !important;
    border-color: transparent !important;
    border-bottom-color: #f1f5f9 !important;
}

.site-user-menu-card:hover .site-user-menu-name {
    color: #2563eb !important;
}

.site-user-menu-avatar {
    width: 38px !important;
    height: 38px !important;
    font-size: 13px !important;
    border-width: 0 !important;
    outline: 0 !important;
}

.site-user-menu-name {
    font-size: 14px !important;
    font-weight: 600 !important;
    letter-spacing: -0.01em !important;
    transition: color 150ms ease !important;
}

.site-user-menu-meta {
    margin-top: 2px !important;
}

.site-user-menu-username {
    font-size: 12.5px !important;
    color: #64748b !important;
}

.site-user-menu-points {
    background: transparent !important;
    border: 0 !important;
    padding: 0 !important;
    color: #2563eb !important;
    font-weight: 700 !important;
    font-size: 12.5px !important;
    gap: 3px !important;
}

.site-user-menu-points iconify-icon {
    color: #2563eb !important;
    font-size: 13px !important;
}

.site-user-menu-divider {
    display: block !important;
    height: 1px !important;
    margin: 6px -6px !important;
    background: #f1f5f9 !important;
}

/* Ilk divider (Karanlik Mod'un altindaki) gorunur kalir,
   liste boylece "tema / sayfalar / cikis" olarak uc bolume ayrilir. */

.site-user-menu-link,
.site-user-menu-button {
    gap: 12px !important;
    min-height: 36px !important;
    padding: 7px 10px !important;
    margin: 0 -6px !important;
    width: calc(100% + 12px) !important;
    border-radius: 10px !important;
    font-size: 13.5px !important;
    font-weight: 400 !important;
    color: #1e293b !important;
    transition: background-color 150ms ease, color 150ms ease !important;
}

.site-user-menu-icon {
    width: 18px !important;
    height: 18px !important;
    background: transparent !important;
    border-radius: 0 !important;
    color: #475569 !important;
    box-shadow: none !important;
    flex: 0 0 18px !important;
}

.site-user-menu-icon iconify-icon {
    font-size: 17px !important;
}

.site-user-menu-link:hover,
.site-user-menu-button:hover {
    background: #f8fafc !important;
    color: #0f172a !important;
}

.site-user-menu-link:hover .site-user-menu-icon,
.site-user-menu-button:hover .site-user-menu-icon {
    background: transparent !important;
    color: #2563eb !important;
}

.site-user-menu-switch {
    background: #e2e8f0 !important;
    transition: background-color 150ms ease !important;
}

.site-user-menu-switch__knob {
    transition: left 150ms ease !important;
}

.site-user-menu-button.site-user-menu-button-danger {
    color: #1e293b !important;
}

.site-user-menu-button.site-user-menu-button-danger .site-user-menu-icon {
    background: transparent !important;
    color: #475569 !important;
}

.site-user-menu-button.site-user-menu-button-danger:hover {
    background: #fef2f2 !important;
    color: #dc2626 !important;
}

.site-user-menu-button.site-user-menu-button-danger:hover .site-user-menu-icon {
    background: transparent !important;
    color: #dc2626 !important;
}

/* ---- Dark mode ---- */
html.dark .site-menu-panel,
.dark .site-menu-panel {
    border-color: #1e293b !important;
    box-shadow: 0 16px 40px -10px rgba(0, 0, 0, 0.55), 0 2px 8px rgba(0, 0, 0, 0.3) !important;
}

html.dark .site-user-menu-card,
.dark .site-user-menu-card {
    background: transparent !important;
    border-bottom-color: #1e293b !important;
}

html.dark .site-user-menu-card:hover,
.dark .site-user-menu-card:hover {
    background: transparent !important;
    border-bottom-color: #1e293b !important;
}

html.dark .site-user-menu-card:hover .site-user-menu-name,
.dark .site-user-menu-card:hover .site-user-menu-name {
    color: #93c5fd !important;
}

html.dark .site-user-menu-divider,
.dark .site-user-menu-divider {
    display: block !important;
    background: #1e293b !important;
    background-color: #1e293b !important;
    border: 0 !important;
}

html.dark .site-user-menu-link,
html.dark .site-user-menu-button,
.dark .site-user-menu-link,
.dark .site-user-menu-button {
    color: #cbd5e1 !important;
}

html.dark .site-user-menu-icon,
.dark .site-user-menu-icon {
    background: transparent !important;
    color: #94a3b8 !important;
}

html.dark .site-user-menu-link:hover,
html.dark .site-user-menu-button:hover,
.dark .site-user-menu-link:hover,
.dark .site-user-menu-button:hover {
    background: #17233a !important;
    color: #f8fafc !important;
}

html.dark .site-user-menu-link:hover .site-user-menu-icon,
html.dark .site-user-menu-button:hover .site-user-menu-icon,
.dark .site-user-menu-link:hover .site-user-menu-icon,
.dark .site-user-menu-button:hover .site-user-menu-icon {
    background: transparent !important;
    color: #93c5fd !important;
}

html.dark .site-user-menu-switch,
.dark .site-user-menu-switch {
    background: #334155 !important;
}

html.dark .site-user-menu-button.site-user-menu-button-danger,
.dark .site-user-menu-button.site-user-menu-button-danger {
    color: #cbd5e1 !important;
}

html.dark .site-user-menu-button.site-user-menu-button-danger .site-user-menu-icon,
.dark .site-user-menu-button.site-user-menu-button-danger .site-user-menu-icon {
    background: transparent !important;
    color: #94a3b8 !important;
}

html.dark .site-user-menu-button.site-user-menu-button-danger:hover,
.dark .site-user-menu-button.site-user-menu-button-danger:hover {
    background: rgba(127, 29, 29, 0.22) !important;
    color: #fecaca !important;
}

html.dark .site-user-menu-button.site-user-menu-button-danger:hover .site-user-menu-icon,
.dark .site-user-menu-button.site-user-menu-button-danger:hover .site-user-menu-icon {
    background: transparent !important;
    color: #fecaca !important;
}

/* ============================
   PROFIL AVATARI - AYARLANABILIR BOYUT
   Avatar boyutunu SADECE asagidaki --site-header-avatar-size
   degerini degistirerek buyutup kucultebilirsin.
   Diger ikonlardan bagimsizdir.
   ============================ */
html body .site-header {
    --site-header-avatar-size: 36px; /* <-- BURADAN AYARLA (or: 32px, 40px, 42px) */
}

html body .site-header .site-header-actions button[data-user-menu-btn].site-icon-btn,
html body .site-header [data-user-menu] button[data-user-menu-btn] {
    width: var(--site-header-avatar-size) !important;
    height: var(--site-header-avatar-size) !important;
    min-width: var(--site-header-avatar-size) !important;
    min-height: var(--site-header-avatar-size) !important;
    max-width: var(--site-header-avatar-size) !important;
    max-height: var(--site-header-avatar-size) !important;
    flex: 0 0 var(--site-header-avatar-size) !important;
    border-radius: 9999px !important;
}

html body .site-header .site-header-actions button[data-user-menu-btn].site-icon-btn > img,
html body .site-header .site-header-actions button[data-user-menu-btn].site-icon-btn > .site-avatar-fallback,
html body .site-header [data-user-menu] button[data-user-menu-btn] > img,
html body .site-header [data-user-menu] button[data-user-menu-btn] > .site-avatar-fallback {
    width: var(--site-header-avatar-size) !important;
    height: var(--site-header-avatar-size) !important;
    min-width: var(--site-header-avatar-size) !important;
    min-height: var(--site-header-avatar-size) !important;
    max-width: var(--site-header-avatar-size) !important;
    max-height: var(--site-header-avatar-size) !important;
    font-size: calc(var(--site-header-avatar-size) * 0.42) !important;
    line-height: var(--site-header-avatar-size) !important;
    border-radius: 9999px !important;
    object-fit: cover !important;
    transform: none !important;
}

/* Mobilde istersen farkli boyut kullan (istege bagli) */
@media (max-width: 640px) {
    html body .site-header {
        --site-header-avatar-size: 34px;
    }
}

/* ============================
   BILDIRIM ZILI - MOBIL GORUNURLUK
   Mesaj ikonunun solunda, ayni satirda gorunur.
   ============================ */
[data-notifications-root] {
    display: flex !important;
    align-items: center !important;
}

/* ============================
   BILDIRIM PANELI - IC DUZEN RAFINESI (KOMPAKT)
   Alma referansindaki gibi: dar panel, sade baslik,
   alt baslik yok, profil menusuyle ayni koseler/golge.
   ============================ */
.site-notifications-panel {
    width: 320px !important;
    border-radius: 16px !important;
    border-color: #eef1f5 !important;
    box-shadow: 0 16px 40px -10px rgba(15, 23, 42, 0.16), 0 2px 8px rgba(15, 23, 42, 0.06) !important;
}

.site-notifications-panel-head {
    padding: 14px 16px 12px !important;
}

.site-notifications-panel-title {
    font-size: 15px !important;
    font-weight: 600 !important;
    letter-spacing: -0.01em !important;
}

.site-notifications-panel-subtitle {
    display: none !important;
}

.site-notifications-list {
    max-height: 360px !important;
    padding: 6px 10px !important;
}

.site-notifications-empty {
    padding: 34px 16px !important;
    font-size: 13.5px !important;
}

.site-notifications-more,
button[data-notifications-actions-btn] {
    width: 32px !important;
    height: 32px !important;
}

.site-notifications-actions-menu,
div[data-notifications-actions-menu] {
    border-radius: 14px !important;
    padding: 6px !important;
    min-width: 210px !important;
    box-shadow: 0 12px 30px -8px rgba(15, 23, 42, 0.18) !important;
}

.site-notifications-menu-item,
button[data-notifications-mark-all],
button[data-notifications-delete-all] {
    border-radius: 10px !important;
    padding: 9px 11px !important;
    font-size: 13px !important;
}

.site-notifications-footer-link {
    padding: 11px 16px !important;
    font-size: 13px !important;
    font-weight: 500 !important;
}

html.dark .site-notifications-panel,
.dark .site-notifications-panel {
    box-shadow: 0 16px 40px -10px rgba(0, 0, 0, 0.55), 0 2px 8px rgba(0, 0, 0, 0.3) !important;
}

/* ============================
   BILDIRIM PANELI - MOBIL OPTIMIZASYON
   Kucuk ekranlarda buton altina degil, arama kutusu
   gibi ust bara sabitlenmis, kenar bosluklu tam genislik
   panel olarak acilir. Dokunma hedefleri buyutulur.
   ============================ */
@media (max-width: 640px) {
    .site-notifications-panel {
        position: fixed !important;
        top: 70px !important;
        left: 10px !important;
        right: 10px !important;
        width: auto !important;
        max-width: none !important;
        max-height: calc(100vh - 96px) !important;
        border-radius: 20px !important;
    }

    .site-notifications-panel:not(.hidden) {
        display: flex !important;
        flex-direction: column !important;
    }

    .site-notifications-panel::before {
        display: none !important;
    }

    .site-notifications-panel-head {
        padding: 16px 16px 12px !important;
        flex: 0 0 auto !important;
    }

    .site-notifications-panel-title {
        font-size: 16px !important;
    }

    .site-notifications-list {
        flex: 1 1 auto !important;
        max-height: none !important;
        padding: 8px !important;
    }

    .site-notifications-more,
    button[data-notifications-actions-btn] {
        width: 38px !important;
        height: 38px !important;
    }

    .site-notifications-menu-item,
    button[data-notifications-mark-all],
    button[data-notifications-delete-all] {
        padding: 13px 12px !important;
        font-size: 14px !important;
    }

    .site-notifications-footer-link {
        flex: 0 0 auto !important;
        padding: 14px 16px !important;
        padding-bottom: calc(14px + env(safe-area-inset-bottom, 0px)) !important;
    }
}

@media (min-width: 641px) and (max-width: 1023.98px) {
    .site-notifications-panel {
        width: 360px !important;
        max-width: calc(100vw - 24px) !important;
    }
}
</style>
