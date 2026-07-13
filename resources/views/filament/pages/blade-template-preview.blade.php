<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Blade Onizleme</title>
    @include('partials.font-assets')
    @include('partials.tailwind-cdn')
    <style>
    :root {
        --site-header-height: 64px;
    }

    .layout-sticky {
        position: sticky;
        top: calc(var(--site-header-height) + 16px);
    }

    @media (min-width: 1024px) {
        .community-grid {
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }
    }

    .community-card {
        border-radius: 0.75rem;
    }

    .community-card-body {
        padding: 1rem;
    }

    .community-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 0.5rem;
        padding: 0.5rem 0.625rem;
        color: rgb(51 65 85 / 1);
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .community-link:hover {
        background: rgb(248 250 252 / 1);
        color: rgb(15 23 42 / 1);
    }

    .community-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        border: 1px solid rgb(226 232 240 / 1);
        background: rgb(248 250 252 / 1);
        padding: 0.25rem 0.625rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(51 65 85 / 1);
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .community-pill:hover {
        background: rgb(241 245 249 / 1);
        color: rgb(15 23 42 / 1);
    }

    .community-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.75rem;
        border: 1px solid rgb(226 232 240 / 1);
        background: #fff;
        padding: 0.5rem 0.875rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: rgb(51 65 85 / 1);
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    .community-btn:hover {
        border-color: rgb(203 213 225 / 1);
        background: rgb(248 250 252 / 1);
        color: rgb(15 23 42 / 1);
    }
</style>

    <style>
        body {
            margin: 0;
            padding: 24px;
            background: #f1f5f9;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            font-weight: 400;
        }
        body :where(h1, h2, h3, h4, h5, h6, strong, b, button, .font-light, .font-medium, .font-semibold, .font-bold, .font-extrabold, .font-black) {
            font-weight: 500 !important;
        }
        body :where(em, i) {
            font-style: italic;
            font-weight: 400 !important;
        }
        .preview-shell {
            max-width: 1100px;
            margin: 0 auto;
        }
        .preview-card {
            border: 1px dashed #cbd5f5;
            background: #fff;
            border-radius: 16px;
            padding: 20px;
        }
        .preview-error {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #b91c1c;
            padding: 16px;
            border-radius: 12px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="preview-shell">
        @if($error)
            <div class="preview-error">
                <div class="font-semibold">Render hatasi</div>
                <div class="mt-2">{{ $error }}</div>
            </div>
        @else
            <div class="preview-card">
                {!! $content !!}
            </div>
        @endif
    </div>
    @include('partials.external-link-bridge')
    @include('partials.image-lightbox')
</body>
</html>
