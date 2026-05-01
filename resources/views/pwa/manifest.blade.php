@php
    $appName = theme_option('site_title') ?: config('app.name', 'Seller Koi');
    $shortName = \Illuminate\Support\Str::limit($appName, 12, '');
    $themeColor = theme_option('primary_color', '#fcb800');
    $backgroundColor = '#ffffff';
    $description = trim((string) rescue(fn () => strip_tags((string) SeoHelper::getDescription()), ''))
        ?: __('Shop and browse :siteName with a fast, app-like experience.', ['siteName' => $appName]);
@endphp
{
    "name": @json($appName),
    "short_name": @json($shortName),
    "description": @json($description),
    "start_url": @json(url('/')),
    "scope": @json(url('/')),
    "display": "standalone",
    "orientation": "portrait",
    "background_color": @json($backgroundColor),
    "theme_color": @json($themeColor),
    "lang": @json(app()->getLocale()),
    "icons": [
        {
            "src": @json(asset('pwa/icons/icon-192.png')),
            "sizes": "192x192",
            "type": "image/png",
            "purpose": "any"
        },
        {
            "src": @json(asset('pwa/icons/icon-512.png')),
            "sizes": "512x512",
            "type": "image/png"
        }
    ]
}
