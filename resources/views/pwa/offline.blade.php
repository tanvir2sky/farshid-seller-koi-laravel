<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="{{ theme_option('primary_color', '#fcb800') }}">
    <title>{{ __('Offline') }} | {{ theme_option('site_title') ?: config('app.name', 'Seller Koi') }}</title>
    <style>
        :root {
            color-scheme: light;
            --pwa-primary: {{ theme_option('primary_color', '#fcb800') }};
            --pwa-text: #1f2937;
            --pwa-muted: #6b7280;
            --pwa-border: #e5e7eb;
            --pwa-surface: #ffffff;
            --pwa-background: #f9fafb;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--pwa-background);
            color: var(--pwa-text);
        }

        .offline-card {
            width: min(100%, 32rem);
            padding: 32px;
            border: 1px solid var(--pwa-border);
            border-radius: 20px;
            background: var(--pwa-surface);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
            text-align: center;
        }

        .offline-badge {
            width: 72px;
            height: 72px;
            margin: 0 auto 20px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: {{ BaseHelper::hexToRgba(theme_option('primary_color', '#fcb800'), 0.14) }};
            color: var(--pwa-text);
            font-size: 28px;
            font-weight: 700;
        }

        h1 {
            margin: 0 0 12px;
            font-size: clamp(1.75rem, 4vw, 2.25rem);
            line-height: 1.1;
        }

        p {
            margin: 0;
            color: var(--pwa-muted);
            line-height: 1.7;
        }

        .offline-actions {
            margin-top: 24px;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .offline-button {
            border: 0;
            border-radius: 999px;
            padding: 12px 20px;
            font: inherit;
            text-decoration: none;
            cursor: pointer;
        }

        .offline-button-primary {
            background: var(--pwa-primary);
            color: #111827;
            font-weight: 600;
        }

        .offline-button-secondary {
            background: #111827;
            color: #ffffff;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <main class="offline-card">
        <div class="offline-badge">!</div>
        <h1>{{ __('You are offline') }}</h1>
        <p>{{ __('This page is not available right now. Once your connection is back, you can continue browsing the store as usual.') }}</p>
        <div class="offline-actions">
            <button class="offline-button offline-button-primary" type="button" onclick="window.location.reload()">
                {{ __('Try again') }}
            </button>
            <a class="offline-button offline-button-secondary" href="{{ url('/') }}">
                {{ __('Go to homepage') }}
            </a>
        </div>
    </main>
</body>
</html>
