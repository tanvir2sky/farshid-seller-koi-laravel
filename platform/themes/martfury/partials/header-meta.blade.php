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
