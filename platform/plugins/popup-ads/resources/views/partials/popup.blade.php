{{-- Popup Ads — injected before </body> via THEME_FRONT_FOOTER filter --}}
<div id="popup-ads-container">
    @foreach($ads as $ad)
        <div
            class="ps-popup ps-popup-ad"
            id="popup-ad-{{ $ad->id }}"
            data-ad-id="{{ $ad->id }}"
            data-delay="{{ (int) $ad->delay_seconds * 1000 }}"
            data-dismiss-duration="{{ $ad->dismiss_duration }}"
            style="display:none;"
        >
            <div class="ps-popup__content ps-popup-ad__content">
                <a class="ps-popup__close ps-popup-ad__close" href="#" title="{{ __('Close') }}" aria-label="{{ __('Close') }}">
                    <i class="icon-cross"></i>
                </a>

                @if($ad->url)
                    <a
                        class="ps-popup-ad__link"
                        href="{{ $ad->url }}"
                        @if($ad->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                        data-ad-id="{{ $ad->id }}"
                    >
                @endif

                @if($ad->image)
                    <img src="{{ $ad->image_url }}" alt="{{ $ad->name }}" class="ps-popup-ad__image" style="max-width:100%;display:block;width:100%;">
                @endif

                @if($ad->title || $ad->description)
                    <div class="ps-popup-ad__overlay">
                        @if($ad->title)
                            <h4 class="ps-popup-ad__title">{{ $ad->title }}</h4>
                        @endif
                        @if($ad->description)
                            <p class="ps-popup-ad__description">{{ $ad->description }}</p>
                        @endif
                    </div>
                @endif

                @if($ad->url)
                    </a>
                @endif
            </div>
        </div>
    @endforeach
</div>

<style>
.ps-popup-ad {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 99999;
    display: flex !important;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,.55);
    opacity: 0;
    visibility: hidden;
    transition: opacity .3s ease, visibility .3s ease;
}
.ps-popup-ad.active {
    opacity: 1;
    visibility: visible;
}
.ps-popup-ad__content {
    position: relative;
    max-width: 600px;
    width: 90%;
    background: #fff;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,.18);
}
.ps-popup-ad__close {
    position: absolute;
    top: 10px;
    right: 14px;
    z-index: 10;
    color: #fff;
    font-size: 20px;
    text-shadow: 0 1px 3px rgba(0,0,0,.5);
    line-height: 1;
}
.ps-popup-ad__close:hover { color: #ddd; }
.ps-popup-ad__image { width: 100%; height: auto; }
.ps-popup-ad__overlay {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 16px 20px;
    background: linear-gradient(transparent, rgba(0,0,0,.65));
    color: #fff;
}
.ps-popup-ad__title {
    margin: 0 0 4px;
    font-size: 20px;
    font-weight: 700;
    color: #fff;
}
.ps-popup-ad__description {
    margin: 0;
    font-size: 14px;
    color: rgba(255,255,255,.85);
}
</style>

@php
$adsConfig = $ads->map(function ($a) {
    return [
        'id'               => $a->id,
        'delay'            => (int) $a->delay_seconds * 1000,
        'dismiss_duration' => $a->dismiss_duration,
    ];
})->values();
@endphp
<script>
(function () {
    'use strict';

    var IMPRESSION_URL = '{{ route("public.popup-ads.impression") }}';
    var CLICK_URL      = '{{ route("public.popup-ads.click") }}';
    var CSRF_TOKEN     = '{{ csrf_token() }}';

    var ads = {!! json_encode($adsConfig) !!};

    // ── Cookie helpers ────────────────────────────────────────────────────
    function setCookie(name, value, days) {
        var expires = '';
        if (days !== null) {
            var d = new Date();
            d.setTime(d.getTime() + days * 864e5);
            expires = '; expires=' + d.toUTCString();
        }
        document.cookie = name + '=' + value + expires + '; path=/; SameSite=Lax';
    }

    function getCookie(name) {
        var v = '; ' + document.cookie;
        var parts = v.split('; ' + name + '=');
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    function isDismissed(adId) {
        return getCookie('popup_ad_dismissed_' + adId) !== null;
    }

    function dismiss(adId, duration) {
        switch (duration) {
            case 'session':  setCookie('popup_ad_dismissed_' + adId, '1', null); break;
            case '1_day':    setCookie('popup_ad_dismissed_' + adId, '1', 1);    break;
            case '7_days':   setCookie('popup_ad_dismissed_' + adId, '1', 7);    break;
            case '30_days':  setCookie('popup_ad_dismissed_' + adId, '1', 30);   break;
            case 'forever':  setCookie('popup_ad_dismissed_' + adId, '1', 3650); break;
            default:         setCookie('popup_ad_dismissed_' + adId, '1', 1);
        }
    }

    // ── Round-robin cycle cookie ──────────────────────────────────────────
    function getNextAd() {
        // Filter out ads that are dismissed in this context
        var available = ads.filter(function (ad) { return !isDismissed(ad.id); });
        if (!available.length) return null;

        var cycleIndex = parseInt(getCookie('popup_ads_cycle_index') || '0', 10);
        cycleIndex = cycleIndex % available.length;
        var ad = available[cycleIndex];

        // Advance for next page load (session cookie)
        setCookie('popup_ads_cycle_index', (cycleIndex + 1) % available.length, null);

        return ad;
    }

    // ── Track ─────────────────────────────────────────────────────────────
    function post(url, adId) {
        var fd = new FormData();
        fd.append('popup_ad_id', adId);
        fd.append('_token', CSRF_TOKEN);
        fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' });
    }

    // ── Show popup ────────────────────────────────────────────────────────
    function showAd(ad) {
        var el = document.getElementById('popup-ad-' + ad.id);
        if (!el) return;

        setTimeout(function () {
            el.style.display = 'flex';
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    el.classList.add('active');
                });
            });

            post(IMPRESSION_URL, ad.id);

            // Close button
            var closeBtn = el.querySelector('.ps-popup-ad__close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    el.classList.remove('active');
                    setTimeout(function () { el.style.display = 'none'; }, 300);
                    dismiss(ad.id, ad.dismiss_duration);
                });
            }

            // Click tracking on ad link
            var link = el.querySelector('.ps-popup-ad__link');
            if (link) {
                link.addEventListener('click', function () {
                    post(CLICK_URL, ad.id);
                });
            }

            // Close on backdrop click
            el.addEventListener('click', function (e) {
                if (e.target === el) {
                    el.classList.remove('active');
                    setTimeout(function () { el.style.display = 'none'; }, 300);
                    dismiss(ad.id, ad.dismiss_duration);
                }
            });
        }, ad.delay);
    }

    // ── Init ──────────────────────────────────────────────────────────────
    function init() {
        if (!ads || !ads.length) return;
        var ad = getNextAd();
        if (ad) showAd(ad);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
</script>
