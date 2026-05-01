<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $pwaAppName = theme_option('site_title') ?: config('app.name', 'Seller Koi');
        $pwaThemeColor = theme_option('primary_color', '#fcb800');
        $pwaDescription = trim((string) rescue(fn () => strip_tags((string) SeoHelper::getDescription()), ''))
            ?: __('Shop and browse :siteName with a fast, app-like experience.', ['siteName' => $pwaAppName]);
    @endphp
    <meta name="application-name" content="{{ $pwaAppName }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $pwaAppName }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="{{ $pwaThemeColor }}">
    <meta name="description" content="{{ $pwaDescription }}">
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('pwa/icons/icon-180.png') }}">

    {!! BaseHelper::googleFonts('https://fonts.googleapis.com/css2?family=' . urlencode(theme_option('primary_font', 'Work Sans')) . ':wght@300;400;500;600;700&display=swap') !!}

    <style>
        :root {
            --color-1st: {{ theme_option('primary_color', '#fcb800') }};
            --primary-color: {{ theme_option('primary_color', '#fcb800') }};
            --color-2nd: {{ theme_option('secondary_color', '#222222') }};
            --secondary-color: {{ theme_option('secondary_color', '#222222') }};
            --primary-font: '{{ theme_option('primary_font', 'Work Sans') }}', sans-serif;
            --button-text-color: {{ theme_option('button_text_color', '#000') }};
            --header-text-color: {{ theme_option('header_text_color', '#000') }};
            --header-button-background-color: {{ theme_option('header_button_background_color', '#000') }};
            --header-button-text-color: {{ theme_option('header_button_text_color', '#fff') }};
            --header-text-hover-color: {{ theme_option('header_text_hover_color', '#fff') }};
            --header-text-accent-color: {{ theme_option('header_text_accent_color', '#222222') }};
            --header-diliver-border-color: {{ BaseHelper::hexToRgba(theme_option('header_text_color', '#000'), 0.15) }};
        }

        .pwa-install-prompt {
            position: fixed;
            left: 16px;
            right: 16px;
            bottom: 16px;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
            pointer-events: none;
        }

        .pwa-install-prompt.is-visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            pointer-events: auto;
        }

        .pwa-install-prompt__card {
            margin: 0 auto;
            width: min(100%, 420px);
            border-radius: 20px;
            background: #ffffff;
            color: #1f2937;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18);
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, 0.08);
        }

        .pwa-install-prompt__content {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            padding: 18px 18px 14px;
        }

        .pwa-install-prompt__icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            object-fit: cover;
            flex: 0 0 52px;
            background: #f3f4f6;
        }

        .pwa-install-prompt__title {
            margin: 0 0 6px;
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .pwa-install-prompt__description {
            margin: 0;
            font-size: 1.3rem;
            line-height: 1.55;
            color: #6b7280;
        }

        .pwa-install-prompt__actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 0 18px 18px;
        }

        .pwa-install-prompt__button {
            border: 0;
            border-radius: 999px;
            padding: 11px 18px;
            font-size: 1.3rem;
            font-weight: 600;
            line-height: 1;
            cursor: pointer;
        }

        .pwa-install-prompt__button--ghost {
            background: #eef2f7;
            color: #111827;
        }

        .pwa-install-prompt__button--primary {
            background: var(--primary-color);
            color: #111827;
        }

        @media (min-width: 768px) {
            .pwa-install-prompt {
                left: auto;
                right: 24px;
                bottom: 24px;
                width: 420px;
            }
        }
    </style>

    @php
        Theme::asset()->remove('language-css');
        Theme::asset()->container('footer')->remove('language-public-js');
        Theme::asset()->container('footer')->remove('simple-slider-owl-carousel-css');
        Theme::asset()->container('footer')->remove('simple-slider-owl-carousel-js');
        Theme::asset()->container('footer')->remove('simple-slider-css');
        Theme::asset()->container('footer')->remove('simple-slider-js');
    @endphp

    {!! Theme::header() !!}
</head>
