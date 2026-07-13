<!doctype html>
<html>
<head>

  @include('partials.font-assets')
  
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Bildirimli Sosyal Akis</title>
      <style id="base-styles">
        * {
          box-sizing: border-box;
        }
        html,
        body {
          margin: 0;
          padding: 0;
          background-color: var(--background);
          min-height: 100%;
        }
        .export-wrapper {
          margin: 0;
          padding: 0;
          background-color: var(--background);
          font-family: var(
            --font-family-body,
            system-ui,
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            Roboto,
            sans-serif
          );
          color: var(--foreground);
          min-height: 100vh;
          -webkit-font-smoothing: antialiased;
          -moz-osx-font-smoothing: grayscale;
          font-weight: 400;
        }

        .export-wrapper :where(em, i) {
          font-style: italic;
          font-weight: 400 !important;
        }

        .glass-header {
          background: var(--background);
          backdrop-filter: blur(16px);
          border-bottom: 1px solid rgba(0, 0, 0, 0.04);
          box-shadow: 0 1px 3px rgba(0, 0, 0, 0.01);
        }
      </style>
      <style id="layout-styles">
        .app-container {
          display: flex;
          flex-direction: column;
          min-height: 100vh;
        }
        .main-grid {
          max-width: 1360px;
          margin: 0 auto;
          width: 100%;
          padding: 32px 24px;
          display: grid;
          grid-template-columns: 240px minmax(0, 1fr) 300px;
          gap: 48px;
          align-items: start;
        }
        @media (max-width: 1024px) {
          .main-grid {
            grid-template-columns: 240px minmax(0, 1fr);
          }
          .right-sidebar {
            display: none !important;
          }
        }
      </style>
      <style id="component-styles">
        .btn {
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 8px;
          padding: 10px 20px;
          border-radius: 24px;
          font-weight: 500;
          font-size: 14px;
          cursor: pointer;
          white-space: nowrap;
          border: none;
        }
        .btn-primary {
          background: var(--primary, #18181b);
          color: var(--primary-foreground, #ffffff);
        }
        .card {
          background: #ffffff;
          border-radius: 20px;
          border: 1px solid rgba(0, 0, 0, 0.05);
          box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
          padding: 28px;
        }
        .nav-item {
          display: flex;
          align-items: center;
          gap: 14px;
          padding: 12px 16px;
          border-radius: 12px;
          color: var(--muted-foreground);
          font-weight: 500;
          cursor: pointer;
          font-size: 15px;
        }
        .nav-item.active {
          background: var(--secondary, #f4f4f5);
          color: var(--foreground);
        }
        .nav-item.active iconify-icon {
          color: var(--primary);
        }
        .category-item {
          display: flex;
          align-items: center;
          gap: 14px;
          padding: 10px 16px;
          border-radius: 12px;
          color: var(--foreground);
          cursor: pointer;
          font-size: 14px;
          font-weight: 500;
        }
        .category-icon {
          width: 32px;
          height: 32px;
          border-radius: 10px;
          display: flex;
          align-items: center;
          justify-content: center;
        }
        .feed-tab {
          padding-bottom: 16px;
          color: var(--muted-foreground);
          font-weight: 500;
          font-size: 15px;
          cursor: pointer;
          white-space: nowrap;
          position: relative;
        }
        .feed-tab.active {
          color: var(--foreground);
        }
        .feed-tab.active::after {
          content: "";
          position: absolute;
          bottom: -1px;
          left: 0;
          right: 0;
          height: 3px;
          background: var(--primary, #18181b);
          border-radius: 3px 3px 0 0;
        }
        .action-btn {
          display: flex;
          align-items: center;
          gap: 8px;
          color: var(--muted-foreground);
          font-size: 14px;
          font-weight: 500;
          cursor: pointer;
        }
        .tag-pill {
          color: var(--muted-foreground);
          background: var(--secondary, #f4f4f5);
          padding: 6px 12px;
          border-radius: 8px;
          font-size: 13px;
          font-weight: 500;
          cursor: pointer;
        }
      </style>
      <style id="notification-styles">
        .notification-item {
          padding: 16px 24px;
          display: flex;
          gap: 16px;
          border-bottom: 1px solid rgba(0, 0, 0, 0.04);
          cursor: pointer;
        }
        .notification-item:last-child {
          border-bottom: none;
        }
        .notification-item.unread {
          background: rgba(0, 0, 0, 0.02);
        }
        .notification-text {
          font-size: 14px;
          line-height: 1.5;
          color: var(--foreground);
        }
        .notification-text strong {
          font-weight: 500;
          color: var(--foreground);
        }
        .notification-time {
          font-size: 13px;
          color: var(--muted-foreground);
          margin-top: 4px;
          font-weight: 500;
        }
        .notification-tabs {
          display: flex;
          gap: 24px;
          padding: 0 24px;
          border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }
        .notification-tab {
          padding: 16px 0;
          font-size: 14px;
          font-weight: 500;
          color: var(--muted-foreground);
          cursor: pointer;
          position: relative;
        }
        .notification-tab.active {
          color: var(--foreground);
        }
        .notification-tab.active::after {
          content: "";
          position: absolute;
          bottom: -1px;
          left: 0;
          right: 0;
          height: 2px;
          background: var(--primary, #18181b);
          border-radius: 2px 2px 0 0;
        }
      </style>
    
</head>
<body>
<div
  class="export-wrapper"
  style="
    width: 1440px;
    min-height: 812px;
    position: relative;
    margin: 0 auto;
    font-family: var(--font-family-body);
    background-color: var(--background);
  "
>
      <div class="app-container">
        <!-- Header -->
        <header
          class="glass-header"
          style="
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 50;
          "
        >
          <!-- Logo -->
          <div
            style="
              display: flex;
              align-items: center;
              gap: 10px;
              font-size: 24px;
              font-weight: 500;
              color: var(--foreground);
              letter-spacing: -0.5px;
            "
          >
            <div
              style="
                width: 36px;
                height: 36px;
                background: var(--primary, #18181b);
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--primary-foreground, #ffffff);
              "
            >
              <iconify-icon
                icon="lucide:box"
                style="font-size: 22px"
              ></iconify-icon>
            </div>
            Nexus
          </div>

          <!-- Search Area -->
          <div
            style="
              flex: 1;
              max-width: 560px;
              margin: 0 40px;
              position: relative;
            "
          >
            <div
              style="
                position: absolute;
                left: 18px;
                top: 50%;
                transform: translateY(-50%);
                color: var(--muted-foreground);
                display: flex;
              "
            >
              <iconify-icon
                icon="lucide:search"
                style="font-size: 18px"
              ></iconify-icon>
            </div>
            <input
              type="text"
              placeholder="Gonderi, etiket veya profil ara..."
              style="
                width: 100%;
                height: 44px;
                border-radius: 22px;
                border: none;
                background: var(--secondary, #f4f4f5);
                padding: 0 20px 0 48px;
                font-family: inherit;
                font-size: 14px;
                font-weight: 500;
                color: var(--foreground);
                outline: none;
              "
            />
          </div>

          <!-- Right Actions -->
          <div style="display: flex; align-items: center; gap: 24px">
            <div data-media-type="banani-button" class="btn btn-primary">
              <iconify-icon
                icon="lucide:pen-line"
                style="font-size: 16px"
              ></iconify-icon>
              Gonderi Olustur
            </div>
            <div
              style="
                display: flex;
                align-items: center;
                gap: 20px;
                border-left: 1px solid rgba(0, 0, 0, 0.08);
                padding-left: 24px;
              "
            >
              <!-- Notification Area with Dropdown -->
              <div style="position: relative; display: flex">
                <!-- Bell Button (Active State) -->
                <div
                  id="notification-toggle"
                  data-media-type="banani-button"
                  style="
                    position: relative;
                    cursor: pointer;
                    color: var(--foreground);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    background: var(--secondary, #f4f4f5);
                  "
                >
                  <iconify-icon
                    icon="lucide:bell"
                    style="font-size: 20px"
                  ></iconify-icon>
                  <div
                    style="
                      position: absolute;
                      top: 10px;
                      right: 10px;
                      width: 8px;
                      height: 8px;
                      background: var(--destructive, #ef4444);
                      border-radius: 50%;
                    "
                  ></div>
                </div>

                <!-- Notification Dropdown Panel -->
                <div
                  id="notification-panel"
                  class="card"
                  style="
                    position: absolute;
                    top: 54px;
                    right: -20px;
                    width: 420px;
                    padding: 0;
                    display: none;
                    flex-direction: column;
                    overflow: hidden;
                    z-index: 100;
                    box-shadow: 0 12px 48px rgba(0, 0, 0, 0.12);
                    border: 1px solid rgba(0, 0, 0, 0.08);
                    cursor: default;
                    border-radius: 16px;
                    background: #ffffff;
                  "
                >
                  <!-- Panel Header -->
                  <div
                    style="
                      padding: 20px 24px;
                      display: flex;
                      justify-content: space-between;
                      align-items: center;
                    "
                  >
                    <h3 style="margin: 0; font-size: 18px; font-weight: 500">
                      Bildirimler
                    </h3>
                    <div
                      data-media-type="banani-button"
                      style="
                        font-size: 13px;
                        font-weight: 500;
                        color: var(--primary, #18181b);
                        cursor: pointer;
                      "
                    >
                      Tumunu okundu isaretle
                    </div>
                  </div>

                  <!-- Tabs -->
                  <div class="notification-tabs">
                    <div
                      data-media-type="banani-button"
                      class="notification-tab active"
                    >
                      All
                    </div>
                    <div
                      data-media-type="banani-button"
                      class="notification-tab"
                    >
                      Unread (2)
                    </div>
                    <div
                      data-media-type="banani-button"
                      class="notification-tab"
                    >
                      Mentions
                    </div>
                    <div
                      data-media-type="banani-button"
                      class="notification-tab"
                    >
                      Sent
                    </div>
                  </div>

                  <!-- List of Notifications -->
                  <div style="max-height: 480px; overflow-y: auto">
                    <!-- Item 1: Like (Unread) -->
                    <div
                      data-media-type="banani-button"
                      class="notification-item unread"
                    >
                      <div
                        style="
                          position: relative;
                          width: 44px;
                          height: 44px;
                          flex-shrink: 0;
                        "
                      >
                        <img
                          src="https://storage.googleapis.com/banani-avatars/avatar%2Fmale%2F25-35%2FNorth%20American%2F3"
                          style="
                            width: 44px;
                            height: 44px;
                            border-radius: 50%;
                            object-fit: cover;
                          "
                        />
                        <div
                          style="
                            position: absolute;
                            bottom: -2px;
                            right: -2px;
                            width: 22px;
                            height: 22px;
                            background: #ef4444;
                            border-radius: 50%;
                            border: 2px solid #ffffff;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                          "
                        >
                          <iconify-icon
                            icon="lucide:heart"
                            style="font-size: 11px"
                          ></iconify-icon>
                        </div>
                      </div>
                      <div style="flex: 1">
                        <div class="notification-text">
                          <strong>James Carter</strong> liked your post
                          <strong
                            >"The Future of Minimalist UI Design in
                            2025"</strong
                          >
                        </div>
                        <div class="notification-time">10 minutes ago</div>
                      </div>
                      <div
                        style="
                          width: 8px;
                          height: 8px;
                          background: var(--primary, #18181b);
                          border-radius: 50%;
                          flex-shrink: 0;
                          margin-top: 6px;
                        "
                      ></div>
                    </div>

                    <!-- Item 2: Comment (Unread) -->
                    <div
                      data-media-type="banani-button"
                      class="notification-item unread"
                    >
                      <div
                        style="
                          position: relative;
                          width: 44px;
                          height: 44px;
                          flex-shrink: 0;
                        "
                      >
                        <img
                          src="https://storage.googleapis.com/banani-avatars/avatar%2Ffemale%2F18-25%2FAsian%2F2"
                          style="
                            width: 44px;
                            height: 44px;
                            border-radius: 50%;
                            object-fit: cover;
                          "
                        />
                        <div
                          style="
                            position: absolute;
                            bottom: -2px;
                            right: -2px;
                            width: 22px;
                            height: 22px;
                            background: #3b82f6;
                            border-radius: 50%;
                            border: 2px solid #ffffff;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                          "
                        >
                          <iconify-icon
                            icon="lucide:message-circle"
                            style="font-size: 11px"
                          ></iconify-icon>
                        </div>
                      </div>
                      <div style="flex: 1">
                        <div class="notification-text">
                          <strong>Sarah Lee</strong> commented on your post:
                          "This looks amazing, great insights!"
                        </div>
                        <div class="notification-time">1 hour ago</div>
                      </div>
                      <div
                        style="
                          width: 8px;
                          height: 8px;
                          background: var(--primary, #18181b);
                          border-radius: 50%;
                          flex-shrink: 0;
                          margin-top: 6px;
                        "
                      ></div>
                    </div>

                    <!-- Item 3: Follow (Read) -->
                    <div
                      data-media-type="banani-button"
                      class="notification-item"
                    >
                      <div
                        style="
                          position: relative;
                          width: 44px;
                          height: 44px;
                          flex-shrink: 0;
                        "
                      >
                        <img
                          src="https://storage.googleapis.com/banani-avatars/avatar%2Ffemale%2F18-25%2FHispanic%2F1"
                          style="
                            width: 44px;
                            height: 44px;
                            border-radius: 50%;
                            object-fit: cover;
                          "
                        />
                        <div
                          style="
                            position: absolute;
                            bottom: -2px;
                            right: -2px;
                            width: 22px;
                            height: 22px;
                            background: #22c55e;
                            border-radius: 50%;
                            border: 2px solid #ffffff;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                          "
                        >
                          <iconify-icon
                            icon="lucide:user-plus"
                            style="font-size: 11px"
                          ></iconify-icon>
                        </div>
                      </div>
                      <div style="flex: 1">
                        <div class="notification-text">
                          <strong>Elena Rodriguez</strong> started following you
                        </div>
                        <div class="notification-time">3 hours ago</div>
                      </div>
                      <div
                        style="
                          padding: 6px 14px;
                          background: var(--secondary, #f4f4f5);
                          border-radius: 16px;
                          font-size: 13px;
                          font-weight: 500;
                          color: var(--foreground);
                          height: max-content;
                        "
                      >
                        Geri Takip Et
                      </div>
                    </div>

                    <!-- Item 4: System (Read) -->
                    <div
                      data-media-type="banani-button"
                      class="notification-item"
                    >
                      <div
                        style="
                          position: relative;
                          width: 44px;
                          height: 44px;
                          flex-shrink: 0;
                          background: var(--secondary, #f4f4f5);
                          border-radius: 50%;
                          display: flex;
                          align-items: center;
                          justify-content: center;
                          color: var(--foreground);
                        "
                      >
                        <iconify-icon
                          icon="lucide:trending-up"
                          style="font-size: 20px"
                        ></iconify-icon>
                        <div
                          style="
                            position: absolute;
                            bottom: -2px;
                            right: -2px;
                            width: 22px;
                            height: 22px;
                            background: #8b5cf6;
                            border-radius: 50%;
                            border: 2px solid #ffffff;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                          "
                        >
                          <iconify-icon
                            icon="lucide:sparkles"
                            style="font-size: 11px"
                          ></iconify-icon>
                        </div>
                      </div>
                      <div style="flex: 1">
                        <div class="notification-text">
                          <strong>Your weekly analytics report</strong> is
                          ready. You had a 24% increase in profile views this
                          week!
                        </div>
                        <div class="notification-time">Yesterday</div>
                      </div>
                    </div>

                    <!-- Item 5: Mention (Read) -->
                    <div
                      data-media-type="banani-button"
                      class="notification-item"
                    >
                      <div
                        style="
                          position: relative;
                          width: 44px;
                          height: 44px;
                          flex-shrink: 0;
                        "
                      >
                        <img
                          src="https://storage.googleapis.com/banani-avatars/avatar%2Fmale%2F35-50%2FEuropean%2F2"
                          style="
                            width: 44px;
                            height: 44px;
                            border-radius: 50%;
                            object-fit: cover;
                          "
                        />
                        <div
                          style="
                            position: absolute;
                            bottom: -2px;
                            right: -2px;
                            width: 22px;
                            height: 22px;
                            background: #f59e0b;
                            border-radius: 50%;
                            border: 2px solid #ffffff;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                          "
                        >
                          <iconify-icon
                            icon="lucide:at-sign"
                            style="font-size: 11px"
                          ></iconify-icon>
                        </div>
                      </div>
                      <div style="flex: 1">
                        <div class="notification-text">
                          <strong>Alex Mercer</strong> mentioned you in a post:
                          "Thanks to @your_handle for the inspiration."
                        </div>
                        <div class="notification-time">2 days ago</div>
                      </div>
                    </div>

                    <!-- Item 6: Sent (Read) -->
                    <div
                      data-media-type="banani-button"
                      class="notification-item"
                    >
                      <div
                        style="
                          position: relative;
                          width: 44px;
                          height: 44px;
                          flex-shrink: 0;
                        "
                      >
                        <img
                          src="https://storage.googleapis.com/banani-avatars/avatar%2Fmale%2F35-50%2FEuropean%2F2"
                          style="
                            width: 44px;
                            height: 44px;
                            border-radius: 50%;
                            object-fit: cover;
                          "
                        />
                        <div
                          style="
                            position: absolute;
                            bottom: -2px;
                            right: -2px;
                            width: 22px;
                            height: 22px;
                            background: #0ea5e9;
                            border-radius: 50%;
                            border: 2px solid #ffffff;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                          "
                        >
                          <iconify-icon
                            icon="lucide:send"
                            style="font-size: 11px"
                          ></iconify-icon>
                        </div>
                      </div>
                      <div style="flex: 1">
                        <div class="notification-text">
                          You sent a message to <strong>Alex Mercer</strong>
                        </div>
                        <div class="notification-time">5 hours ago</div>
                      </div>
                    </div>

                    <!-- Item 7: Sent (Read) -->
                    <div
                      data-media-type="banani-button"
                      class="notification-item"
                    >
                      <div
                        style="
                          position: relative;
                          width: 44px;
                          height: 44px;
                          flex-shrink: 0;
                        "
                      >
                        <img
                          src="https://storage.googleapis.com/banani-avatars/avatar%2Ffemale%2F18-25%2FHispanic%2F1"
                          style="
                            width: 44px;
                            height: 44px;
                            border-radius: 50%;
                            object-fit: cover;
                          "
                        />
                        <div
                          style="
                            position: absolute;
                            bottom: -2px;
                            right: -2px;
                            width: 22px;
                            height: 22px;
                            background: #6366f1;
                            border-radius: 50%;
                            border: 2px solid #ffffff;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                          "
                        >
                          <iconify-icon
                            icon="lucide:share-2"
                            style="font-size: 11px"
                          ></iconify-icon>
                        </div>
                      </div>
                      <div style="flex: 1">
                        <div class="notification-text">
                          You shared your post with
                          <strong>Elena Rodriguez</strong>
                        </div>
                        <div class="notification-time">Yesterday</div>
                      </div>
                    </div>
                  </div>

                  <!-- Panel Footer -->
                  <div
                    data-media-type="banani-button"
                    style="
                      padding: 16px;
                      text-align: center;
                      border-top: 1px solid rgba(0, 0, 0, 0.06);
                      font-size: 14px;
                      font-weight: 500;
                      color: var(--muted-foreground);
                      cursor: pointer;
                      background: var(--secondary, #f4f4f5);
                    "
                  >
                    Tum bildirimleri gor
                  </div>
                </div>
              </div>
              <img
                src="https://storage.googleapis.com/banani-avatars/avatar%2Ffemale%2F25-35%2FEuropean%2F1"
                style="
                  width: 40px;
                  height: 40px;
                  border-radius: 50%;
                  object-fit: cover;
                  cursor: pointer;
                  border: 1px solid rgba(0, 0, 0, 0.05);
                "
              />
            </div>
          </div>
        </header>

        <!-- Main Content -->
        <main class="main-grid">
          <!-- Left Sidebar -->
          <aside
            style="
              display: flex;
              flex-direction: column;
              gap: 40px;
              position: sticky;
              top: 104px;
            "
          >
            <!-- Main Nav -->
            <nav style="display: flex; flex-direction: column; gap: 4px">
              <div data-media-type="banani-button" class="nav-item active">
                <iconify-icon
                  icon="lucide:home"
                  style="font-size: 22px"
                ></iconify-icon>
                Ana Akis
              </div>
              <div data-media-type="banani-button" class="nav-item">
                <iconify-icon
                  icon="lucide:compass"
                  style="font-size: 22px"
                ></iconify-icon>
                Explore
              </div>
              <div data-media-type="banani-button" class="nav-item">
                <iconify-icon
                  icon="lucide:message-square"
                  style="font-size: 22px"
                ></iconify-icon>
                Mesajlar
              </div>
              <div data-media-type="banani-button" class="nav-item">
                <iconify-icon
                  icon="lucide:bookmark"
                  style="font-size: 22px"
                ></iconify-icon>
                Kaydedilenler
              </div>
              <div data-media-type="banani-button" class="nav-item">
                <iconify-icon
                  icon="lucide:users"
                  style="font-size: 22px"
                ></iconify-icon>
                Topluluklar
              </div>
            </nav>

            <!-- Categories -->
            <div>
              <h3
                style="
                  font-size: 13px;
                  text-transform: uppercase;
                  color: var(--muted-foreground);
                  font-weight: 500;
                  margin: 0 0 16px 16px;
                  letter-spacing: 0.5px;
                "
              >
                Categories
              </h3>
              <div style="display: flex; flex-direction: column; gap: 2px">
                <div data-media-type="banani-button" class="category-item">
                  <div
                    class="category-icon"
                    style="background: rgba(239, 68, 68, 0.1); color: #ef4444"
                  >
                    <iconify-icon
                      icon="lucide:newspaper"
                      style="font-size: 16px"
                    ></iconify-icon>
                  </div>
                  News
                </div>
                <div data-media-type="banani-button" class="category-item">
                  <div
                    class="category-icon"
                    style="background: rgba(236, 72, 153, 0.1); color: #ec4899"
                  >
                    <iconify-icon
                      icon="lucide:coffee"
                      style="font-size: 16px"
                    ></iconify-icon>
                  </div>
                  Lifestyle
                </div>
                <div data-media-type="banani-button" class="category-item">
                  <div
                    class="category-icon"
                    style="background: rgba(2, 132, 199, 0.1); color: #0284c7"
                  >
                    <iconify-icon
                      icon="lucide:monitor"
                      style="font-size: 16px"
                    ></iconify-icon>
                  </div>
                  Technology
                </div>
                <div data-media-type="banani-button" class="category-item">
                  <div
                    class="category-icon"
                    style="background: rgba(34, 197, 94, 0.1); color: #22c55e"
                  >
                    <iconify-icon
                      icon="lucide:briefcase"
                      style="font-size: 16px"
                    ></iconify-icon>
                  </div>
                  Business
                </div>
                <div data-media-type="banani-button" class="category-item">
                  <div
                    class="category-icon"
                    style="background: rgba(234, 179, 8, 0.1); color: #eab308"
                  >
                    <iconify-icon
                      icon="lucide:bitcoin"
                      style="font-size: 16px"
                    ></iconify-icon>
                  </div>
                  Crypto
                </div>
                <div data-media-type="banani-button" class="category-item">
                  <div
                    class="category-icon"
                    style="background: rgba(249, 115, 22, 0.1); color: #f97316"
                  >
                    <iconify-icon
                      icon="lucide:trophy"
                      style="font-size: 16px"
                    ></iconify-icon>
                  </div>
                  Sports
                </div>
              </div>
            </div>
          </aside>

          <!-- Center Feed -->
          <div style="display: flex; flex-direction: column; gap: 32px">
            <!-- Create Post Widget -->
            <div class="card" style="padding: 24px">
              <div style="display: flex; gap: 16px">
                <img
                  src="https://storage.googleapis.com/banani-avatars/avatar%2Ffemale%2F25-35%2FEuropean%2F1"
                  style="
                    width: 48px;
                    height: 48px;
                    border-radius: 50%;
                    object-fit: cover;
                    flex-shrink: 0;
                    border: 1px solid rgba(0, 0, 0, 0.05);
                  "
                />
                <div style="flex: 1">
                  <div
                    data-media-type="banani-button"
                    style="
                      background: var(--secondary, #f4f4f5);
                      border-radius: 24px;
                      padding: 14px 20px;
                      color: var(--muted-foreground);
                      font-size: 15px;
                      font-weight: 500;
                      cursor: text;
                    "
                  >
                    Dusuncelerini paylas, bir yazi gonder veya gorsel yukle...
                  </div>
                  <div
                    style="
                      display: flex;
                      justify-content: space-between;
                      align-items: center;
                      margin-top: 20px;
                    "
                  >
                    <div style="display: flex; gap: 8px">
                      <div
                        data-media-type="banani-button"
                        style="
                          width: 40px;
                          height: 40px;
                          border-radius: 50%;
                          display: flex;
                          align-items: center;
                          justify-content: center;
                          color: var(--muted-foreground);
                          cursor: pointer;
                          background: transparent;
                        "
                      >
                        <iconify-icon
                          icon="lucide:image"
                          style="font-size: 20px"
                        ></iconify-icon>
                      </div>
                      <div
                        data-media-type="banani-button"
                        style="
                          width: 40px;
                          height: 40px;
                          border-radius: 50%;
                          display: flex;
                          align-items: center;
                          justify-content: center;
                          color: var(--muted-foreground);
                          cursor: pointer;
                          background: transparent;
                        "
                      >
                        <iconify-icon
                          icon="lucide:link"
                          style="font-size: 20px"
                        ></iconify-icon>
                      </div>
                      <div
                        data-media-type="banani-button"
                        style="
                          width: 40px;
                          height: 40px;
                          border-radius: 50%;
                          display: flex;
                          align-items: center;
                          justify-content: center;
                          color: var(--muted-foreground);
                          cursor: pointer;
                          background: transparent;
                        "
                      >
                        <iconify-icon
                          icon="lucide:smile"
                          style="font-size: 20px"
                        ></iconify-icon>
                      </div>
                      <div
                        data-media-type="banani-button"
                        style="
                          width: 40px;
                          height: 40px;
                          border-radius: 50%;
                          display: flex;
                          align-items: center;
                          justify-content: center;
                          color: var(--muted-foreground);
                          cursor: pointer;
                          background: transparent;
                        "
                      >
                        <iconify-icon
                          icon="lucide:bar-chart-2"
                          style="font-size: 20px"
                        ></iconify-icon>
                      </div>
                    </div>
                    <div
                      data-media-type="banani-button"
                      class="btn btn-primary"
                    >
                      Paylas
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Feed Tabs -->
            <div
              style="
                display: flex;
                gap: 32px;
                border-bottom: 1px solid rgba(0, 0, 0, 0.06);
                margin-bottom: -8px;
                padding-left: 8px;
              "
            >
              <div data-media-type="banani-button" class="feed-tab active">
                Son Gonderiler
              </div>
              <div data-media-type="banani-button" class="feed-tab">
                Gundemdekiler
              </div>
              <div data-media-type="banani-button" class="feed-tab">
                Takip Ettiklerin
              </div>
            </div>
            @php
              $posts = $posts ?? collect();
            @endphp

            @forelse($posts as $post)
              @php
                $postTitle = trim((string) ($post->title ?? ''));
                if ($postTitle === '') {
                    $slugTitle = trim((string) ($post->slug ?? ''));
                    if ($slugTitle !== '') {
                        $postTitle = '/' . ltrim($slugTitle, '/');
                    }
                }
                $excerptSource = $post->excerpt ?? $post->summary ?? $post->content ?? '';
                $postExcerpt = trim(strip_tags((string) $excerptSource));
                if ($postExcerpt !== '') {
                    $postExcerpt = \Illuminate\Support\Str::limit($postExcerpt, 220);
                }
                $author = $post->author;
                $authorName = optional($author)->name ?? 'Topluluk';
                $authorAvatar = optional($author)->profile_photo_url ?? optional($author)->profile_photo_path ?? null;
                $authorVerified = (bool) (optional($author)->is_verified ?? false);
                $categoryName = optional($post->category)->name ?? '';
                $categoryAvatar = optional($post->category)->profile_image_url ?? optional($post->category)->profile_image ?? null;
                $publishedAt = $post->published_at ?? $post->created_at;
                $publishedLabel = $publishedAt ? $publishedAt->diffForHumans() : 'Just now';
                $featuredImage = $post->featured_image_url ?? $post->featured_image ?? $post->cover_image ?? null;
                $tags = collect($post->tags ?? [])->take(3);
                $reactionsCount = (int) ($post->reactions_count ?? 0);
                $commentsCount = (int) ($post->comments_count ?? 0);
                $viewsCount = (int) ($post->views_count ?? 0);
                $postUrl = '#';
                if (!empty($post->slug)) {
                    $postUrl = route('blog.post', $post);
                }
                $authorInitials = '';
                if ($authorName !== '') {
                    $parts = preg_split('/\s+/', trim((string) $authorName));
                    $initials = '';
                    foreach (array_slice($parts, 0, 2) as $part) {
                        if ($part === '') {
                            continue;
                        }
                        $letter = function_exists('mb_substr') ? mb_substr($part, 0, 1) : substr($part, 0, 1);
                        $initials .= strtoupper((string) $letter);
                    }
                    $authorInitials = $initials;
                }
                $categoryInitials = '';
                if ($categoryName !== '') {
                    $parts = preg_split('/\s+/', trim((string) $categoryName));
                    $initials = '';
                    foreach (array_slice($parts, 0, 2) as $part) {
                        if ($part === '') {
                            continue;
                        }
                        $initials .= \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr((string) $part, 0, 1));
                    }
                    if ($initials === '') {
                        $initials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr((string) $categoryName, 0, 2));
                    }
                    $categoryInitials = \Illuminate\Support\Str::substr($initials, 0, 2);
                }
              @endphp

              <div
                class="card"
                style="display: flex; flex-direction: column; gap: 20px"
              >
                <!-- Header -->
                <div
                  style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                  "
                >
                  <div style="display: flex; gap: 12px; align-items: center">
                    <div
                      style="
                        position: relative;
                        width: 44px;
                        height: 44px;
                        flex-shrink: 0;
                      "
                    >
                      @if ($authorAvatar)
                        <img
                          src="{{ $authorAvatar }}"
                          alt="{{ $authorName }}"
                          style="
                            width: 44px;
                            height: 44px;
                            border-radius: 50%;
                            object-fit: cover;
                            display: block;
                            border: 1px solid rgba(0, 0, 0, 0.08);
                          "
                          loading="lazy"
                          decoding="async"
                        />
                      @else
                        <div
                          style="
                            width: 44px;
                            height: 44px;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            background: #f1f5f9;
                            color: #111111;
                            font-weight: 700;
                            font-size: 14px;
                            line-height: 1;
                            border: 1px solid rgba(0, 0, 0, 0.08);
                          "
                        >
                          {{ $authorInitials !== '' ? $authorInitials : 'U' }}
                        </div>
                      @endif
                      @if ($categoryName !== '')
                        <div
                          style="
                            position: absolute;
                            left: 24px;
                            bottom: -3px;
                            width: 22px;
                            height: 22px;
                            border-radius: 50%;
                            border: 2px solid #ffffff;
                            background: #ffffff;
                            color: #7a7a7a;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            font-weight: 700;
                            font-size: 9px;
                            line-height: 1;
                            text-transform: uppercase;
                            box-sizing: border-box;
                            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.04);
                            overflow: hidden;
                          "
                        >
                          @if ($categoryAvatar)
                            <img
                              src="{{ $categoryAvatar }}"
                              alt="{{ $categoryName }}"
                              style="
                                width: 100%;
                                height: 100%;
                                object-fit: cover;
                                display: block;
                              "
                              loading="lazy"
                              decoding="async"
                            />
                          @else
                            {{ $categoryInitials !== '' ? $categoryInitials : 'AI' }}
                          @endif
                        </div>
                      @endif
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0">
                      <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px">
                        <span
                          style="
                            font-weight: 700;
                            font-size: 15px;
                            line-height: 1.2;
                            color: #111111;
                          "
                          >{{ $authorName }}</span
                        >
                        @if ($authorVerified)
                          <x-verification-badge :user="$author" size="xs" />
                        @endif
                      </div>
                      <div
                        style="
                          display: flex;
                          align-items: center;
                          gap: 12px;
                          font-size: 14px;
                          line-height: 1.2;
                          color: #7a7a7a;
                          font-weight: 500;
                        "
                      >
                        @if ($categoryName !== '')
                          <span style="color: #7a7a7a; font-weight: 500"
                            >{{ $categoryName }}</span
                          >
                        @endif
                        <span>{{ $publishedLabel }}</span>
                      </div>
                    </div>
                  </div>
                  <div
                    data-media-type="banani-button"
                    style="
                      width: 36px;
                      height: 36px;
                      display: flex;
                      align-items: center;
                      justify-content: center;
                      color: var(--muted-foreground);
                      cursor: pointer;
                    "
                  >
                    <iconify-icon
                      icon="lucide:more-horizontal"
                      style="font-size: 22px"
                    ></iconify-icon>
                  </div>
                </div>

                <!-- Content -->
                <div style="display: flex; flex-direction: column; gap: 12px">
                  @if ($postTitle !== '')
                    <a
                      href="{{ $postUrl }}"
                      style="text-decoration: none; color: inherit"
                    >
                      <h2
                        style="
                          font-size: 20px;
                          font-weight: 500;
                          color: var(--foreground);
                          margin: 0;
                          line-height: 1.4;
                          letter-spacing: -0.3px;
                        "
                      >
                        {{ $postTitle }}
                      </h2>
                    </a>
                  @endif
                  @if ($postExcerpt !== '')
                    <p
                      style="
                        font-size: 16px;
                        color: var(--foreground);
                        line-height: 1.6;
                        margin: 0;
                        opacity: 0.9;
                      "
                    >
                      {{ $postExcerpt }}
                    </p>
                  @endif
                </div>

                <!-- Image -->
                @if ($featuredImage)
                  <a href="{{ $postUrl }}" style="display: block">
                    <img
                      data-aspect-ratio="16:9"
                      style="
                        width: 100%;
                        border-radius: 12px;
                        object-fit: cover;
                        border: 1px solid rgba(0, 0, 0, 0.05);
                      "
                      src="{{ $featuredImage }}"
                      alt="{{ $postTitle }}"
                      loading="lazy"
                      decoding="async"
                    />
                  </a>
                @endif

                <!-- Tags -->
                @if ($tags->isNotEmpty())
                  <div
                    style="
                      display: flex;
                      gap: 12px;
                      flex-wrap: wrap;
                      margin-top: 4px;
                    "
                  >
                    @foreach ($tags as $tag)
                      <span class="tag-pill">#{{ $tag->name }}</span>
                    @endforeach
                  </div>
                @endif

                <!-- Action Footer -->
                <div
                  style="
                    display: flex;
                    gap: 32px;
                    border-top: 1px solid rgba(0, 0, 0, 0.04);
                    padding-top: 20px;
                    margin-top: 4px;
                  "
                >
                  <div data-media-type="banani-button" class="action-btn">
                    <iconify-icon
                      icon="lucide:heart"
                      style="font-size: 20px"
                    ></iconify-icon>
                    <span>{{ number_format($reactionsCount) }}</span>
                  </div>
                  <div data-media-type="banani-button" class="action-btn">
                    <iconify-icon
                      icon="lucide:message-circle"
                      style="font-size: 20px"
                    ></iconify-icon>
                    <span>{{ number_format($commentsCount) }}</span>
                  </div>
                  <div data-media-type="banani-button" class="action-btn">
                    <iconify-icon
                      icon="lucide:eye"
                      style="font-size: 20px"
                    ></iconify-icon>
                    <span>{{ number_format($viewsCount) }}</span>
                  </div>
                  <a
                    href="{{ $postUrl }}"
                    data-media-type="banani-button"
                    class="action-btn"
                    style="margin-left: auto"
                  >
                    <iconify-icon
                      icon="lucide:share-2"
                      style="font-size: 20px"
                    ></iconify-icon>
                  </a>
                </div>
              </div>
            @empty
              <div
                class="card"
                style="
                  display: flex;
                  flex-direction: column;
                  gap: 12px;
                  text-align: center;
                "
              >
                <div
                  style="
                    font-size: 15px;
                    color: var(--muted-foreground);
                    font-weight: 500;
                  "
                >
                  Henuz yazi bulunamadi.
                </div>
              </div>
            @endforelse
          </div>

          <!-- Right Sidebar -->
          <aside
            class="right-sidebar"
            style="
              display: flex;
              flex-direction: column;
              gap: 32px;
              position: sticky;
              top: 104px;
            "
          >
            <!-- Popular Tags Card -->
            <div class="card" style="padding: 24px">
              <h3
                style="
                  font-size: 18px;
                  font-weight: 500;
                  color: var(--foreground);
                  margin: 0 0 24px 0;
                "
              >
                Popular Tags
              </h3>
              <div style="display: flex; flex-direction: column; gap: 20px">
                <!-- Tag Item -->
                <div
                  data-media-type="banani-button"
                  style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    cursor: pointer;
                  "
                >
                  <div style="display: flex; align-items: center; gap: 14px">
                    <div
                      style="
                        width: 40px;
                        height: 40px;
                        border-radius: 12px;
                        background: var(--secondary, #f4f4f5);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: var(--foreground);
                      "
                    >
                      <iconify-icon
                        icon="lucide:hash"
                        style="font-size: 18px"
                      ></iconify-icon>
                    </div>
                    <div>
                      <div
                        style="
                          font-size: 15px;
                          font-weight: 500;
                          color: var(--foreground);
                        "
                      >
                        Technology
                      </div>
                      <div
                        style="
                          font-size: 13px;
                          color: var(--muted-foreground);
                          margin-top: 4px;
                          font-weight: 500;
                        "
                      >
                        12.4k posts
                      </div>
                    </div>
                  </div>
                  <iconify-icon
                    icon="lucide:trending-up"
                    style="color: var(--success, #22c55e); font-size: 18px"
                  ></iconify-icon>
                </div>
                <!-- Tag Item -->
                <div
                  data-media-type="banani-button"
                  style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    cursor: pointer;
                  "
                >
                  <div style="display: flex; align-items: center; gap: 14px">
                    <div
                      style="
                        width: 40px;
                        height: 40px;
                        border-radius: 12px;
                        background: var(--secondary, #f4f4f5);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: var(--foreground);
                      "
                    >
                      <iconify-icon
                        icon="lucide:hash"
                        style="font-size: 18px"
                      ></iconify-icon>
                    </div>
                    <div>
                      <div
                        style="
                          font-size: 15px;
                          font-weight: 500;
                          color: var(--foreground);
                        "
                      >
                        ArtificialIntell...
                      </div>
                      <div
                        style="
                          font-size: 13px;
                          color: var(--muted-foreground);
                          margin-top: 4px;
                          font-weight: 500;
                        "
                      >
                        8.1k posts
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Tag Item -->
                <div
                  data-media-type="banani-button"
                  style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    cursor: pointer;
                  "
                >
                  <div style="display: flex; align-items: center; gap: 14px">
                    <div
                      style="
                        width: 40px;
                        height: 40px;
                        border-radius: 12px;
                        background: var(--secondary, #f4f4f5);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: var(--foreground);
                      "
                    >
                      <iconify-icon
                        icon="lucide:hash"
                        style="font-size: 18px"
                      ></iconify-icon>
                    </div>
                    <div>
                      <div
                        style="
                          font-size: 15px;
                          font-weight: 500;
                          color: var(--foreground);
                        "
                      >
                        WebDev
                      </div>
                      <div
                        style="
                          font-size: 13px;
                          color: var(--muted-foreground);
                          margin-top: 4px;
                          font-weight: 500;
                        "
                      >
                        5.2k posts
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Tag Item -->
                <div
                  data-media-type="banani-button"
                  style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    cursor: pointer;
                  "
                >
                  <div style="display: flex; align-items: center; gap: 14px">
                    <div
                      style="
                        width: 40px;
                        height: 40px;
                        border-radius: 12px;
                        background: var(--secondary, #f4f4f5);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: var(--foreground);
                      "
                    >
                      <iconify-icon
                        icon="lucide:hash"
                        style="font-size: 18px"
                      ></iconify-icon>
                    </div>
                    <div>
                      <div
                        style="
                          font-size: 15px;
                          font-weight: 500;
                          color: var(--foreground);
                        "
                      >
                        Crypto
                      </div>
                      <div
                        style="
                          font-size: 13px;
                          color: var(--muted-foreground);
                          margin-top: 4px;
                          font-weight: 500;
                        "
                      >
                        4.3k posts
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div
                data-media-type="banani-button"
                style="
                  margin-top: 24px;
                  font-size: 14px;
                  color: var(--primary);
                  font-weight: 500;
                  cursor: pointer;
                  display: inline-block;
                "
              >
                Show all tags
              </div>
            </div>

            <!-- Top Creators Card -->
            <div class="card" style="padding: 24px">
              <h3
                style="
                  font-size: 18px;
                  font-weight: 500;
                  color: var(--foreground);
                  margin: 0 0 24px 0;
                "
              >
                Gundemdeki Profiller
              </h3>
              <div style="display: flex; flex-direction: column; gap: 20px">
                <!-- Author 1 -->
                <div
                  style="
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                  "
                >
                  <div
                    data-media-type="banani-button"
                    style="
                      display: flex;
                      align-items: center;
                      gap: 12px;
                      cursor: pointer;
                    "
                  >
                    <img
                      src="https://storage.googleapis.com/banani-avatars/avatar%2Fmale%2F35-50%2FEuropean%2F2"
                      style="
                        width: 44px;
                        height: 44px;
                        border-radius: 50%;
                        object-fit: cover;
                        border: 1px solid rgba(0, 0, 0, 0.05);
                      "
                    />
                    <div>
                      <div
                        style="
                          font-size: 15px;
                          font-weight: 500;
                          color: var(--foreground);
                        "
                      >
                        Alex Mercer
                      </div>
                      <div
                        style="
                          font-size: 13px;
                          color: var(--muted-foreground);
                          margin-top: 4px;
                          font-weight: 500;
                        "
                      >
                        142k followers
                      </div>
                    </div>
                  </div>
                  <div
                    data-media-type="banani-button"
                    style="
                      padding: 8px 16px;
                      border-radius: 20px;
                      background: var(--foreground);
                      color: var(--background, #ffffff);
                      font-size: 13px;
                      font-weight: 500;
                      cursor: pointer;
                    "
                  >
                    Takip Et
                  </div>
                </div>
                <!-- Author 2 -->
                <div
                  style="
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                  "
                >
                  <div
                    data-media-type="banani-button"
                    style="
                      display: flex;
                      align-items: center;
                      gap: 12px;
                      cursor: pointer;
                    "
                  >
                    <img
                      src="https://storage.googleapis.com/banani-avatars/avatar%2Ffemale%2F18-25%2FHispanic%2F1"
                      style="
                        width: 44px;
                        height: 44px;
                        border-radius: 50%;
                        object-fit: cover;
                        border: 1px solid rgba(0, 0, 0, 0.05);
                      "
                    />
                    <div>
                      <div
                        style="
                          font-size: 15px;
                          font-weight: 500;
                          color: var(--foreground);
                        "
                      >
                        Elena Rodriguez
                      </div>
                      <div
                        style="
                          font-size: 13px;
                          color: var(--muted-foreground);
                          margin-top: 4px;
                          font-weight: 500;
                        "
                      >
                        89k followers
                      </div>
                    </div>
                  </div>
                  <div
                    data-media-type="banani-button"
                    style="
                      padding: 8px 16px;
                      border-radius: 20px;
                      background: var(--foreground);
                      color: var(--background, #ffffff);
                      font-size: 13px;
                      font-weight: 500;
                      cursor: pointer;
                    "
                  >
                    Takip Et
                  </div>
                </div>
                <!-- Author 3 -->
                <div
                  style="
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                  "
                >
                  <div
                    data-media-type="banani-button"
                    style="
                      display: flex;
                      align-items: center;
                      gap: 12px;
                      cursor: pointer;
                    "
                  >
                    <img
                      src="https://storage.googleapis.com/banani-avatars/avatar%2Fmale%2F25-35%2FAfrican%2F1"
                      style="
                        width: 44px;
                        height: 44px;
                        border-radius: 50%;
                        object-fit: cover;
                        border: 1px solid rgba(0, 0, 0, 0.05);
                      "
                    />
                    <div>
                      <div
                        style="
                          font-size: 15px;
                          font-weight: 500;
                          color: var(--foreground);
                        "
                      >
                        Marcus Johnson
                      </div>
                      <div
                        style="
                          font-size: 13px;
                          color: var(--muted-foreground);
                          margin-top: 4px;
                          font-weight: 500;
                        "
                      >
                        65k followers
                      </div>
                    </div>
                  </div>
                  <div
                    data-media-type="banani-button"
                    style="
                      padding: 8px 16px;
                      border-radius: 20px;
                      background: var(--foreground);
                      color: var(--background, #ffffff);
                      font-size: 13px;
                      font-weight: 500;
                      cursor: pointer;
                    "
                  >
                    Takip Et
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer Links -->
            <div
              style="
                font-size: 13px;
                color: var(--muted-foreground);
                line-height: 1.8;
                padding: 0 16px;
                font-weight: 500;
              "
            >
              <div
                style="
                  display: flex;
                  gap: 14px;
                  flex-wrap: wrap;
                  margin-bottom: 8px;
                "
              >
                <span data-media-type="banani-button" style="cursor: pointer"
                  >About</span
                >
                <span data-media-type="banani-button" style="cursor: pointer"
                  >Help Center</span
                >
                <span data-media-type="banani-button" style="cursor: pointer"
                  >Terms of Service</span
                >
                <span data-media-type="banani-button" style="cursor: pointer"
                  >Privacy Policy</span
                >
                <span data-media-type="banani-button" style="cursor: pointer"
                  >Cookie Policy</span
                >
                <span data-media-type="banani-button" style="cursor: pointer"
                  >Accessibility</span
                >
              </div>
              <div>© 2025 Nexus Social, Inc.</div>
            </div>
          </aside>
        </main>
      </div>
    
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js" defer></script>
  <script>
    (function () {
      var toggle = document.getElementById('notification-toggle');
      var panel = document.getElementById('notification-panel');
      if (!toggle || !panel) return;
      toggle.addEventListener('click', function (event) {
        event.stopPropagation();
        var isHidden =
          panel.style.display === 'none' ||
          window.getComputedStyle(panel).display === 'none';
        panel.style.display = isHidden ? 'flex' : 'none';
      });
    })();
  </script>
  <style>
    :root {
      --background: #f7f7f8;
      --foreground: #111827;
      --border: #00000014;
      --input: #ffffff;
      --primary: #0b84ff;
      --primary-foreground: #ffffff;
      --secondary: #f1f5f9;
      --secondary-foreground: #0f172a;
      --muted: #e6e9eb;
      --muted-foreground: #6b7280;
      --success: #10b981;
      --success-foreground: #ffffff;
      --accent: #ffb6c1;
      --accent-foreground: #111827;
      --destructive: #ef4444;
      --destructive-foreground: #ffffff;
      --warning: #f59e0b;
      --warning-foreground: #111827;
      --card: #ffffff;
      --card-foreground: #111827;
      --sidebar: #f3f4f6;
      --sidebar-foreground: #0f172a;
      --sidebar-primary: #e6f6ff;
      --sidebar-primary-foreground: #0b84ff;
      --radius-sm: 4px;
      --radius-md: 6px;
      --radius-lg: 8px;
      --radius-xl: 12px;
      --font-family-body: Poppins;
    }
  </style>
</div>

@include('partials.external-link-bridge')
@include('partials.image-lightbox')
</body>
</html>


