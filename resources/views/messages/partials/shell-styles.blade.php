@once
    @push('head')
        <style>
            .messages-page {
                min-height: 100vh;
                background: #f3f4f6;
            }

            .messages-shell {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                gap: 16px;
                width: 100%;
                align-items: stretch;
            }

            .messages-shell--simple {
                grid-template-columns: minmax(0, 1fr) !important;
            }

            .messages-card {
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                background: #ffffff;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            }

            .messages-sidebar-panel,
            .messages-main-panel {
                min-height: 500px;
            }

            .messages-sidebar-panel {
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            .messages-main-panel {
                display: flex;
                min-width: 0;
                flex-direction: column;
                overflow: hidden;
            }

            .messages-show-layout {
                display: flex;
                min-height: calc(100vh - var(--site-header-height, 70px) - 32px);
                flex-direction: column;
                gap: 16px;
            }

            .messages-shell--show {
                flex: 1 1 auto;
                min-height: 0;
            }

            .messages-shell--show .messages-main-panel,
            .messages-shell--show .messages-sidebar-panel {
                min-height: 0;
                height: 100%;
            }

            .messages-header-card {
                position: sticky;
                top: calc(var(--site-header-height, 70px) + 12px);
                z-index: 30;
                flex-shrink: 0;
            }

            .messages-composer-dock {
                position: fixed;
                right: 12px;
                left: 12px;
                bottom: calc(12px + env(safe-area-inset-bottom));
                z-index: 35;
                flex-shrink: 0;
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                background: rgba(255, 255, 255, 0.97);
                box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
                backdrop-filter: blur(12px);
            }

            .messages-thread-scroller {
                padding-bottom: 176px !important;
            }

            .messages-conversation-actions {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
            }

            .messages-thread-item {
                transition: background-color 0.18s ease;
            }

            .messages-thread-item:hover {
                background: #f9fafb;
            }

            .messages-thread-item.is-active {
                background: #f9fafb;
            }

            .messages-bubble {
                max-width: min(85%, 28rem);
                padding: 12px 16px;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
                overflow-wrap: anywhere;
            }

            .messages-bubble--mine {
                border-radius: 16px;
                border-bottom-right-radius: 8px;
                background: #1f2937;
                color: #ffffff;
            }

            .messages-bubble--other {
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                border-bottom-left-radius: 8px;
                background: #ffffff;
                color: #1f2937;
            }

            .messages-empty-state {
                border: 1px dashed #d1d5db;
                border-radius: 16px;
                background: #f9fafb;
            }

            .message-scrollbar::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }

            .message-scrollbar::-webkit-scrollbar-thumb {
                background: #d1d5db;
                border-radius: 999px;
            }

            .message-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .messages-sidebar-overlay {
                display: none;
            }

            .messages-mobile-drawer {
                position: fixed;
                inset: 12px auto 12px 12px;
                z-index: 60;
                width: min(calc(100vw - 24px), 340px);
                transform: translateX(calc(-100% - 20px));
                transition: transform 0.22s ease;
            }

            .messages-mobile-drawer.is-open {
                transform: translateX(0);
            }

            .message-fade {
                animation: messageFadeUp 0.18s ease;
            }

            @keyframes messageFadeUp {
                from {
                    opacity: 0;
                    transform: translateY(8px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @media (min-width: 1024px) {
                .messages-shell {
                    grid-template-columns: minmax(0, 340px) minmax(0, 1fr);
                    gap: 24px;
                }

                .messages-shell--simple .messages-sidebar-panel {
                    max-width: 100%;
                }

                .messages-composer-dock {
                    left: calc(50% + 182px);
                    right: auto;
                    width: min(788px, calc(100vw - 396px));
                    bottom: 16px;
                    transform: translateX(-50%);
                }

                .messages-thread-scroller {
                    padding-bottom: 168px !important;
                }

                .messages-mobile-drawer {
                    position: static;
                    inset: auto;
                    width: auto;
                    transform: none !important;
                }

                .messages-sidebar-panel,
                .messages-main-panel {
                    min-height: 600px;
                }
            }

            @media (max-width: 1023px) {
                .messages-sidebar-overlay.is-visible {
                    position: fixed;
                    inset: 0;
                    z-index: 50;
                    display: block;
                    background: rgba(15, 23, 42, 0.35);
                }
            }

            @media (max-width: 639px) {
                .messages-page {
                    padding-bottom: calc(86px + env(safe-area-inset-bottom));
                }

                .messages-thread-scroller {
                    padding-bottom: 188px !important;
                }

                .messages-show-layout {
                    min-height: calc(100vh - var(--site-header-height, 70px) - 24px);
                    gap: 12px;
                }

                .messages-mobile-drawer {
                    inset: 10px auto 10px 10px;
                    width: min(calc(100vw - 20px), 360px);
                    max-height: calc(100vh - 20px);
                }

                .messages-conversation-actions {
                    width: 100%;
                    justify-content: flex-end;
                }

                .messages-bubble {
                    max-width: 88%;
                }
            }

            body.alma-app:has(.messages-page) .layout-main,
            body.alma-app:has(.messages-page) .site-main-shell {
                width: 100% !important;
                max-width: 960px !important;
            }

            body.alma-app:has(.messages-page) .messages-page {
                min-height: calc(100vh - var(--site-header-height, 64px));
                padding: 0 !important;
                background: transparent !important;
                color: #6b7280 !important;
            }

            body.alma-app:has(.messages-page) .messages-page *,
            body.alma-app:has(.messages-page) .messages-page *::before,
            body.alma-app:has(.messages-page) .messages-page *::after {
                transition: none !important;
                animation: none !important;
                box-shadow: none !important;
                box-sizing: border-box !important;
            }

            body.alma-app:has(.messages-page) .messages-page .site-main-shell {
                padding: 24px 12px 124px !important;
            }

            body.alma-app:has(.messages-page) .messages-card {
                border: 0 !important;
                border-radius: 18px !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 24px !important;
                background: #ffffff !important;
                color: #1f2937 !important;
                overflow: hidden !important;
            }

            body.alma-app:has(.messages-page) .messages-page h1 {
                margin: 0 !important;
                color: #4b5563 !important;
                font-size: 18px !important;
                line-height: 1.2 !important;
                font-weight: 700 !important;
                letter-spacing: 0 !important;
                text-transform: lowercase !important;
            }

            body.alma-app:has(.messages-page) .messages-conversation-actions a,
            body.alma-app:has(.messages-page) .messages-conversation-actions button {
                border: 0 !important;
                background: transparent !important;
                color: #7f8388 !important;
                text-decoration: underline !important;
                font-size: 17px !important;
                font-weight: 400 !important;
            }

            body.alma-app:has(.messages-page) .messages-shell {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) !important;
                gap: 18px !important;
                width: 100% !important;
            }

            body.alma-app:has(.messages-page) .messages-main-panel.hidden {
                display: none !important;
            }

            body.alma-app:has(.messages-page) .messages-sidebar-panel {
                width: 100% !important;
                min-height: 0 !important;
                border-radius: 0 !important;
                background: transparent !important;
                overflow: visible !important;
            }

            body.alma-app:has(.messages-page) .messages-sidebar-panel > .border-b {
                border: 0 !important;
                padding: 0 0 22px !important;
            }

            body.alma-app:has(.messages-page) [data-message-search] {
                width: 100% !important;
                height: 72px !important;
                border: 0 !important;
                border-radius: 18px !important;
                border: 1px solid #e5e7eb !important;
                background: #ffffff !important;
                color: #111827 !important;
                padding: 0 24px !important;
                font-size: 18px !important;
                outline: none !important;
            }

            body.alma-app:has(.messages-page) [data-message-search]::placeholder {
                color: #9ca3af !important;
            }

            body.alma-app:has(.messages-page) .messages-sidebar-panel .message-scrollbar {
                overflow: visible !important;
            }

            body.alma-app:has(.messages-page) .messages-sidebar-panel .divide-y {
                display: flex !important;
                flex-direction: column !important;
                gap: 18px !important;
                border: 0 !important;
            }

            body.alma-app:has(.messages-page) .messages-sidebar-panel .divide-y > * + * {
                border-top: 0 !important;
            }

            body.alma-app:has(.messages-page) .messages-thread-item {
                min-height: 96px !important;
                display: flex !important;
                align-items: center !important;
                border: 0 !important;
                border-radius: 18px !important;
                background: #ffffff !important;
                color: #111827 !important;
                padding: 16px 18px !important;
                gap: 14px !important;
            }

            body.alma-app:has(.messages-page) .messages-thread-item:hover,
            body.alma-app:has(.messages-page) .messages-thread-item.is-active {
                background: #ffffff !important;
            }

            body.alma-app:has(.messages-page) .messages-thread-item img,
            body.alma-app:has(.messages-page) .messages-thread-item .rounded-full {
                width: 68px !important;
                height: 68px !important;
                min-width: 68px !important;
                background: #f3f4f6 !important;
                color: #6b7280 !important;
                font-size: 18px !important;
            }

            body.alma-app:has(.messages-page) .messages-thread-item h3 {
                color: #111827 !important;
                font-size: 22px !important;
                line-height: 1.1 !important;
                font-weight: 400 !important;
            }

            body.alma-app:has(.messages-page) .messages-thread-item p,
            body.alma-app:has(.messages-page) .messages-thread-item span {
                color: #6b7280 !important;
            }

            body.alma-app:has(.messages-show-layout) .site-main-shell.messages-show-layout {
                max-width: 656px !important;
                min-height: calc(100vh - var(--site-header-height, 64px)) !important;
                gap: 0 !important;
                padding: 0 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-header-card,
            body.alma-app:has(.messages-show-layout) .messages-shell--show .messages-sidebar-panel {
                display: none !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-shell--show {
                display: block !important;
                flex: 1 1 auto !important;
                min-height: calc(100vh - var(--site-header-height, 64px)) !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-page,
            body.alma-app:has(.messages-show-layout) .messages-main-panel,
            body.alma-app:has(.messages-show-layout) .messages-thread-scroller {
                background: transparent !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-main-panel {
                min-height: calc(100vh - var(--site-header-height, 64px)) !important;
                border-radius: 0 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-thread-scroller {
                min-height: calc(100vh - var(--site-header-height, 64px)) !important;
                padding: 44px 24px 188px !important;
            }

            body.alma-app:has(.messages-show-layout) [data-thread] {
                max-width: 100% !important;
                gap: 42px !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble {
                position: relative !important;
                display: inline-flex !important;
                width: auto !important;
                max-width: min(78%, 360px) !important;
                min-width: 0 !important;
                min-height: 0 !important;
                white-space: normal !important;
                word-break: normal !important;
                overflow-wrap: break-word !important;
                border: 0 !important;
                border-radius: 22px !important;
                padding: 18px 26px !important;
                color: #6b7280 !important;
                font-size: 16px !important;
                line-height: 1.45 !important;
                overflow-wrap: break-word !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble--other {
                background: #f8fafc !important;
                color: #6b7280 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble--mine {
                background: #ffffff !important;
                color: #85898d !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble--other::before,
            body.alma-app:has(.messages-show-layout) .messages-bubble--mine::before {
                content: "" !important;
                position: absolute !important;
                top: 0 !important;
                width: 28px !important;
                height: 28px !important;
                background: inherit !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble--other::before {
                left: 18px !important;
                clip-path: polygon(0 0, 100% 100%, 0 100%) !important;
                transform: translateY(-14px) !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble--mine::before {
                right: 18px !important;
                clip-path: polygon(100% 0, 100% 100%, 0 100%) !important;
                transform: translateY(-14px) !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble,
            body.alma-app:has(.messages-show-layout) .messages-bubble * {
                white-space: normal !important;
                word-break: normal !important;
                overflow-wrap: break-word !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble p {
                margin: 0 !important;
                max-width: 100% !important;
                white-space: normal !important;
                word-break: normal !important;
                overflow-wrap: break-word !important;
                font-size: 16px !important;
                line-height: 1.45 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble + div,
            body.alma-app:has(.messages-show-layout) .message-fade .text-gray-400 {
                color: #85898d !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-composer-dock {
                position: fixed !important;
                left: calc((100vw - min(100vw, 1272px)) / 2 + 256px) !important;
                right: auto !important;
                bottom: 20px !important;
                width: min(656px, calc(100vw - 28px)) !important;
                transform: none !important;
                border: 1px solid rgba(226, 232, 240, 0.9) !important;
                border-radius: 24px !important;
                background: rgba(255, 255, 255, 0.96) !important;
                padding: 8px !important;
                box-shadow: 0 10px 28px rgba(15, 23, 42, 0.07) !important;
                backdrop-filter: blur(14px) !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-composer-dock form > .flex {
                flex-direction: row !important;
                align-items: center !important;
                gap: 10px !important;
            }

            body.alma-app:has(.messages-show-layout) #messageInput {
                height: 50px !important;
                min-width: 0 !important;
                border: 1px solid #eef2f7 !important;
                border-radius: 18px !important;
                background: #ffffff !important;
                color: #6b7280 !important;
                padding: 0 20px !important;
                font-size: 16px !important;
                outline: none !important;
            }

            body.alma-app:has(.messages-show-layout) #messageInput::placeholder {
                color: #8a929d !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-composer-dock .flex.items-center.justify-end {
                flex: 0 0 auto !important;
                gap: 10px !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-composer-dock button,
            body.alma-app:has(.messages-show-layout) .messages-composer-dock label {
                width: 50px !important;
                height: 50px !important;
                min-width: 50px !important;
                border: 1px solid transparent !important;
                border-radius: 18px !important;
                padding: 0 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-composer-dock button[type="button"] {
                background: rgba(59, 130, 246, 0.10) !important;
                color: #0e7c86 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-composer-dock label[for="messageAttachmentInput"] {
                background: rgba(99, 102, 241, 0.10) !important;
                color: #6366f1 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-composer-dock button[type="submit"] {
                background: linear-gradient(135deg, rgba(14, 165, 233, 0.14), rgba(16, 185, 129, 0.14)) !important;
                color: #0f766e !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-composer-dock button svg,
            body.alma-app:has(.messages-show-layout) .messages-composer-dock label svg {
                width: 25px !important;
                height: 25px !important;
                color: currentColor !important;
            }

            @media (max-width: 1199px) and (min-width: 901px) {
                body.alma-app:has(.messages-show-layout) .messages-composer-dock {
                    left: calc((100vw - 912px) / 2 + 256px) !important;
                }
            }

            @media (max-width: 900px) {
                body.alma-app:has(.messages-page) .messages-page .site-main-shell {
                    padding: 20px 14px 118px !important;
                }

                body.alma-app:has(.messages-show-layout) .site-main-shell.messages-show-layout,
                body.alma-app:has(.messages-show-layout) .messages-page .site-main-shell {
                    padding: 0 !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-thread-scroller {
                    padding: 38px 18px 228px !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-bubble {
                    max-width: min(80%, 320px) !important;
                    padding: 16px 18px !important;
                    border-radius: 20px !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-composer-dock {
                    left: 14px !important;
                    width: calc(100vw - 28px) !important;
                    bottom: calc(84px + env(safe-area-inset-bottom)) !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-composer-dock form > .flex {
                    flex-direction: row !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-composer-dock button,
                body.alma-app:has(.messages-show-layout) .messages-composer-dock label {
                    width: 46px !important;
                    height: 46px !important;
                    min-width: 46px !important;
                    border-radius: 16px !important;
                }

                body.alma-app:has(.messages-show-layout) #messageInput {
                    height: 46px !important;
                    padding: 0 14px !important;
                    font-size: 15px !important;
                }
            }


            /* v3: daha küçük mesaj kutuları + main dış çizgisini kaldır */
            body.alma-app:has(.messages-show-layout) .messages-main-panel,
            body.alma-app:has(.messages-show-layout) .messages-card.messages-main-panel,
            body.alma-app:has(.messages-show-layout) #messagesContainer,
            body.alma-app:has(.messages-show-layout) .messages-thread-scroller {
                border: 0 !important;
                outline: 0 !important;
                box-shadow: none !important;
                background: transparent !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-main-panel {
                overflow: visible !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble {
                display: block !important;
                width: max-content !important;
                max-width: min(68%, 340px) !important;
                min-width: 0 !important;
                min-height: 0 !important;
                padding: 12px 18px !important;
                border-radius: 18px !important;
                font-size: 15px !important;
                line-height: 1.38 !important;
                white-space: pre-wrap !important;
                word-break: normal !important;
                overflow-wrap: break-word !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble p {
                display: block !important;
                width: max-content !important;
                max-width: 100% !important;
                margin: 0 !important;
                font-size: 15px !important;
                line-height: 1.38 !important;
                white-space: pre-wrap !important;
                word-break: normal !important;
                overflow-wrap: break-word !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble::before {
                width: 22px !important;
                height: 22px !important;
                transform: translateY(-11px) !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble--mine::before {
                right: 16px !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble--other::before {
                left: 16px !important;
            }

            body.alma-app:has(.messages-show-layout) [data-thread] {
                gap: 30px !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble + div,
            body.alma-app:has(.messages-show-layout) .message-fade .text-gray-400 {
                margin-top: 6px !important;
                font-size: 11px !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-card,
            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) [data-message-search],
            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item {
                border: 0 !important;
                box-shadow: none !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) [data-message-search] {
                height: 62px !important;
                border-radius: 20px !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item {
                min-height: 86px !important;
                border-radius: 20px !important;
            }

            @media (max-width: 900px) {
                body.alma-app:has(.messages-show-layout) .messages-thread-scroller {
                    padding: 30px 18px 220px !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-bubble {
                    max-width: min(76%, 300px) !important;
                    padding: 11px 16px !important;
                    border-radius: 17px !important;
                    font-size: 15px !important;
                    line-height: 1.36 !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-bubble p {
                    font-size: 15px !important;
                    line-height: 1.36 !important;
                }
            }



            /* v4: referanstaki gibi konuşma teması */
            body.alma-app:has(.messages-show-layout) .messages-page,
            body.alma-app:has(.messages-show-layout) .messages-main-panel,
            body.alma-app:has(.messages-show-layout) .messages-thread-scroller {
                background-color: #f4f4f1 !important;
                background-image:
                    linear-gradient(rgba(30, 41, 59, 0.08) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(30, 41, 59, 0.08) 1px, transparent 1px) !important;
                background-size: 28px 28px !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-main-panel,
            body.alma-app:has(.messages-show-layout) .messages-card.messages-main-panel,
            body.alma-app:has(.messages-show-layout) #messagesContainer {
                border: 0 !important;
                box-shadow: none !important;
                outline: 0 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-thread-scroller {
                padding: 26px 18px 220px !important;
            }

            body.alma-app:has(.messages-show-layout) [data-thread] {
                gap: 18px !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble,
            body.alma-app:has(.messages-show-layout) .messages-bubble p {
                white-space: normal !important;
                word-break: normal !important;
                overflow-wrap: break-word !important;
                line-break: auto !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble {
                display: inline-block !important;
                width: fit-content !important;
                max-width: min(78%, 290px) !important;
                padding: 12px 14px !important;
                border-radius: 18px !important;
                min-width: 0 !important;
                min-height: 0 !important;
                font-size: 14px !important;
                line-height: 1.35 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble p {
                margin: 0 !important;
                max-width: 100% !important;
                font-size: 14px !important;
                line-height: 1.35 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble::before,
            body.alma-app:has(.messages-show-layout) .messages-bubble--mine::before,
            body.alma-app:has(.messages-show-layout) .messages-bubble--other::before {
                display: none !important;
                content: none !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble--mine {
                background: #343447 !important;
                color: #ffffff !important;
                border-radius: 18px !important;
                border-top-right-radius: 8px !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble--other {
                background: #bcdced !important;
                color: #1f2937 !important;
                border-radius: 18px !important;
                border-top-left-radius: 8px !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble + div,
            body.alma-app:has(.messages-show-layout) .message-fade .text-gray-400 {
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                gap: 8px !important;
                width: 100% !important;
                margin-top: 6px !important;
                color: #111827 !important;
                font-size: 12px !important;
                line-height: 1.2 !important;
                text-align: center !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-composer-dock {
                border: 0 !important;
                border-radius: 24px !important;
                background: rgba(255,255,255,0.92) !important;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08) !important;
                backdrop-filter: blur(10px) !important;
                padding: 10px !important;
            }

            body.alma-app:has(.messages-show-layout) #messageInput {
                height: 48px !important;
                border: 0 !important;
                border-radius: 18px !important;
                background: #ffffff !important;
                color: #4b5563 !important;
                padding: 0 16px !important;
                font-size: 15px !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-composer-dock label[for="messageAttachmentInput"] {
                background: rgba(99, 102, 241, 0.10) !important;
                color: #6366f1 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-composer-dock button[type="submit"] {
                background: rgba(20, 184, 166, 0.12) !important;
                color: #0f766e !important;
            }

            @media (min-width: 901px) {
                body.alma-app:has(.messages-show-layout) .messages-thread-scroller {
                    padding: 30px 26px 180px !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-bubble {
                    max-width: min(60%, 360px) !important;
                    padding: 13px 16px !important;
                    font-size: 15px !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-bubble p {
                    font-size: 15px !important;
                }
            }

            body.alma-app:has(.messages-show-layout) .messages-page,
            body.alma-app:has(.messages-show-layout) .site-main-shell,
            body.alma-app:has(.messages-show-layout) .messages-shell--show,
            body.alma-app:has(.messages-show-layout) .messages-main-panel,
            body.alma-app:has(.messages-show-layout) .messages-card.messages-main-panel,
            body.alma-app:has(.messages-show-layout) #messagesContainer,
            body.alma-app:has(.messages-show-layout) .messages-thread-scroller {
                background: transparent !important;
                background-color: transparent !important;
                background-image: none !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-composer-dock {
                background: transparent !important;
                background-color: transparent !important;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
                box-shadow: none !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble--mine {
                background: #343447 !important;
                color: #ffffff !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble--other {
                background: #bcdced !important;
                color: #1f2937 !important;
            }

            body.alma-app:has(.messages-show-layout) .message-fade > .flex {
                max-width: 78% !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble {
                display: block !important;
                width: fit-content !important;
                min-width: 132px !important;
                max-width: min(100%, 360px) !important;
                padding: 12px 16px !important;
                border-radius: 16px !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble p {
                display: block !important;
                width: auto !important;
                min-width: 0 !important;
                max-width: 100% !important;
                margin: 0 !important;
                white-space: pre-wrap !important;
                word-break: normal !important;
                overflow-wrap: break-word !important;
                line-height: 1.35 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble + div,
            body.alma-app:has(.messages-show-layout) .message-fade .text-gray-400 {
                width: 100% !important;
                justify-content: flex-end !important;
                text-align: right !important;
                color: #111827 !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-page {
                background: #f4f4f5 !important;
                padding: 0 !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .site-main-shell {
                max-width: 656px !important;
                padding: 20px 0 24px !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-page h1 {
                font-size: 18px !important;
                line-height: 1.2 !important;
                font-weight: 700 !important;
                color: #334155 !important;
                text-transform: lowercase !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-conversation-actions button,
            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-conversation-actions a {
                width: 32px !important;
                height: 32px !important;
                border: 0 !important;
                background: transparent !important;
                color: #475569 !important;
                padding: 0 !important;
                text-decoration: none !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-sidebar-panel {
                background: transparent !important;
                border: 0 !important;
                border-radius: 0 !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-sidebar-panel > .border-b {
                padding: 0 0 22px !important;
                border: 0 !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) [data-message-search] {
                height: 62px !important;
                border: 0 !important;
                border-radius: 18px !important;
                background: #ffffff !important;
                color: #334155 !important;
                padding: 0 24px !important;
                font-size: 16px !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) [data-message-search]::placeholder {
                color: #94a3b8 !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-sidebar-panel .message-scrollbar {
                overflow: visible !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-sidebar-panel .divide-y {
                display: flex !important;
                flex-direction: column !important;
                gap: 22px !important;
                border: 0 !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-sidebar-panel .divide-y > * + * {
                border-top: 0 !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item {
                min-height: 100px !important;
                border: 0 !important;
                border-radius: 18px !important;
                background: #ffffff !important;
                color: #334155 !important;
                padding: 16px 18px !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item > div {
                align-items: center !important;
                gap: 14px !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item img,
            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item .rounded-full {
                width: 54px !important;
                height: 54px !important;
                min-width: 54px !important;
                background: #e8f1ff !important;
                color: #7c8dff !important;
                font-size: 28px !important;
                font-weight: 400 !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item h3 {
                color: #020617 !important;
                font-size: 22px !important;
                line-height: 1.15 !important;
                font-weight: 400 !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item p {
                margin-top: 6px !important;
                color: #64748b !important;
                font-size: 12px !important;
                line-height: 1.35 !important;
            }

            body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item span {
                color: #64748b !important;
                font-size: 12px !important;
                font-weight: 400 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-page,
            body.alma-app:has(.messages-show-layout) .site-main-shell,
            body.alma-app:has(.messages-show-layout) .messages-shell--show,
            body.alma-app:has(.messages-show-layout) .messages-main-panel,
            body.alma-app:has(.messages-show-layout) .messages-card.messages-main-panel,
            body.alma-app:has(.messages-show-layout) #messagesContainer,
            body.alma-app:has(.messages-show-layout) .messages-thread-scroller {
                background: transparent !important;
                background-color: transparent !important;
                background-image: none !important;
                border: 0 !important;
                box-shadow: none !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-thread-scroller {
                padding: 30px 26px 180px !important;
            }

            body.alma-app:has(.messages-show-layout) .message-fade > .flex {
                max-width: 78% !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble {
                display: block !important;
                width: fit-content !important;
                min-width: 130px !important;
                max-width: min(100%, 360px) !important;
                padding: 12px 16px !important;
                border-radius: 16px !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble--mine {
                background: #343447 !important;
                color: #ffffff !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble--other {
                background: #bcdced !important;
                color: #1f2937 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-bubble p {
                display: block !important;
                width: auto !important;
                margin: 0 !important;
                white-space: pre-wrap !important;
                word-break: normal !important;
                overflow-wrap: break-word !important;
                line-height: 1.35 !important;
            }

            body.alma-app:has(.messages-show-layout) .messages-composer-dock {
                background: transparent !important;
                box-shadow: none !important;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
            }

            @media (max-width: 900px) {
                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .site-main-shell {
                    padding: 18px 14px 24px !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-thread-scroller {
                    padding: 24px 18px 160px !important;
                }
            }

            @media (max-width: 640px) {
                html:has(.messages-page),
                body.alma-app:has(.messages-page) {
                    width: 100% !important;
                    max-width: 100% !important;
                    overflow-x: hidden !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-page {
                    width: 100% !important;
                    max-width: 100% !important;
                    min-height: calc(100vh - var(--site-header-height, 64px)) !important;
                    overflow-x: hidden !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .site-main-shell {
                    width: 100% !important;
                    max-width: 100% !important;
                    padding: 28px 16px 24px !important;
                    overflow-x: hidden !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-page h1 {
                    font-size: 18px !important;
                    line-height: 1.2 !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-page .mb-4.flex,
                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-page .sm\:mb-6 {
                    margin-bottom: 28px !important;
                    display: grid !important;
                    grid-template-columns: minmax(0, 1fr) 32px !important;
                    align-items: center !important;
                    gap: 10px !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-conversation-actions {
                    width: 32px !important;
                    justify-content: flex-end !important;
                    justify-self: end !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) [data-message-search] {
                    height: 64px !important;
                    width: 100% !important;
                    border-radius: 18px !important;
                    padding: 0 24px !important;
                    font-size: 16px !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-sidebar-panel > .border-b {
                    padding-bottom: 22px !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-sidebar-panel .divide-y {
                    gap: 22px !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item {
                    width: 100% !important;
                    min-height: 102px !important;
                    padding: 18px !important;
                    border-radius: 18px !important;
                    overflow: hidden !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item > div {
                    width: 100% !important;
                    min-width: 0 !important;
                    display: grid !important;
                    grid-template-columns: 56px minmax(0, 1fr) !important;
                    gap: 14px !important;
                    align-items: center !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item img,
                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item .rounded-full {
                    width: 56px !important;
                    height: 56px !important;
                    min-width: 56px !important;
                    max-width: 56px !important;
                    font-size: 25px !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item .min-w-0.flex-1 {
                    min-width: 0 !important;
                    width: 100% !important;
                    overflow: hidden !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item .flex.items-center.justify-between {
                    min-width: 0 !important;
                    display: grid !important;
                    grid-template-columns: minmax(0, 1fr) auto !important;
                    gap: 10px !important;
                    align-items: center !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item h3 {
                    max-width: 100% !important;
                    overflow: hidden !important;
                    text-overflow: ellipsis !important;
                    white-space: nowrap !important;
                    font-size: 22px !important;
                    line-height: 1.1 !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item p {
                    max-width: 100% !important;
                    overflow: hidden !important;
                    text-overflow: ellipsis !important;
                    white-space: nowrap !important;
                    font-size: 12px !important;
                    line-height: 1.25 !important;
                }

                body.alma-app:has(.messages-page):not(:has(.messages-show-layout)) .messages-thread-item span {
                    white-space: nowrap !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-thread-scroller {
                    padding-bottom: 132px !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-composer-dock {
                    bottom: calc(8px + env(safe-area-inset-bottom)) !important;
                    left: 12px !important;
                    width: calc(100vw - 24px) !important;
                    padding: 10px !important;
                    border-radius: 22px !important;
                    background: rgba(255, 255, 255, 0.72) !important;
                    background-color: rgba(255, 255, 255, 0.72) !important;
                    backdrop-filter: blur(14px) !important;
                    -webkit-backdrop-filter: blur(14px) !important;
                }
            }

            @media (min-width: 901px) {
                body.alma-app:has(.messages-show-layout) .messages-composer-dock {
                    background: #ffffff !important;
                    background-color: #ffffff !important;
                    border-radius: 22px !important;
                    padding: 10px !important;
                }
            }

            .messages-mobile-thread-button {
                display: none;
            }

            @media (max-width: 1023px) {
                body.alma-app:has(.messages-show-layout) .messages-mobile-thread-button {
                    position: fixed !important;
                    top: calc(var(--site-header-height, 64px) + 12px) !important;
                    left: 12px !important;
                    z-index: 9993 !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    width: 42px !important;
                    height: 42px !important;
                    border: 0 !important;
                    border-radius: 14px !important;
                    background: #ffffff !important;
                    color: #111827 !important;
                    box-shadow: none !important;
                    padding: 0 !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-shell--show .messages-sidebar-panel.messages-mobile-drawer {
                    display: flex !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-mobile-drawer {
                    position: fixed !important;
                    inset: calc(var(--site-header-height, 64px) + 8px) auto 12px 12px !important;
                    z-index: 9995 !important;
                    width: min(calc(100vw - 24px), 340px) !important;
                    max-height: calc(100dvh - var(--site-header-height, 64px) - 20px) !important;
                    transform: translateX(calc(-100% - 24px)) !important;
                    transition: none !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-mobile-drawer.is-open {
                    transform: translateX(0) !important;
                }

                body.alma-app:has(.messages-show-layout) .messages-sidebar-overlay.is-visible {
                    position: fixed !important;
                    inset: var(--site-header-height, 64px) 0 0 !important;
                    z-index: 9994 !important;
                    display: block !important;
                }
            }

        </style>
    @endpush
@endonce
