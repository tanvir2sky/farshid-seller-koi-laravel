 <footer class="ps-footer">
        <div class="ps-container">
            <div class="ps-footer__widgets">
                @if (theme_option('hotline') || theme_option('address') || theme_option('email') || theme_option('social-name-1'))
                    <aside class="widget widget_footer widget_contact-us">
                        <h4 class="widget-title">{{ __('Contact us') }}</h4>
                        <div class="widget_content">
                            @if (theme_option('hotline'))
                                <p>{{ __('Call us 24/7') }}</p>
                                <h3>{{ theme_option('hotline') }}</h3>
                            @endif
                            <p>{{ theme_option('address') }} <br><a href="mailto:{{ theme_option('email') }}">{{ theme_option('email') }}</a></p>
                            {!! Theme::partial('social-links') !!}
                        </div>
                    </aside>
                @endif
                {!! dynamic_sidebar('footer_sidebar') !!}
            </div>
            @if (Widget::group('bottom_footer_sidebar')->getWidgets())
                <div class="ps-footer__links" id="footer-links">
                    {!! dynamic_sidebar('bottom_footer_sidebar') !!}
                </div>
            @endif
            <div class="ps-footer__copyright">
                <p class="site-copyright">{!! Theme::getSiteCopyright() !!}</p>
                @php $paymentMethods = array_filter(json_decode(theme_option('payment_methods', []), true)); @endphp
                @if ($paymentMethods)
                    <div class="footer-payments">
                        <span class="payment-method-title">{{ __('We Using Safe Payment For') }}:</span>
                        <p class="d-sm-inline-block d-block">
                            @if (theme_option('payment_methods_link'))
                                <a href="{{ url(theme_option('payment_methods_link')) }}" target="_blank">
                            @endif
                            @foreach($paymentMethods as $method)
                                @if (!empty($method))
                                    <span>
                                        {!! RvMedia::image($method, __('Payment methods')) !!}
                                    </span>
                                @endif
                            @endforeach
                            @if (theme_option('payment_methods_link'))
                                </a>
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </footer>

    @if (is_plugin_active('newsletter') && theme_option('enable_newsletter_popup', 'yes') === 'yes')
        <div data-session-domain="{{ config('session.domain') ?? request()->getHost() }}"></div>
        <div class="ps-popup" id="subscribe" data-time="{{ (int)theme_option('newsletter_show_after_seconds', 10) * 1000 }}">
            <div class="ps-popup__content bg--cover" data-background="{{ RvMedia::getImageUrl(theme_option('newsletter_image')) }}" style="background-size: cover!important;"><a class="ps-popup__close" title="{{ __('Close') }}" href="#"><i class="icon-cross"></i></a>
                <form method="post" action="{{ route('public.newsletter.subscribe') }}" class="ps-form--subscribe-popup newsletter-form">
                    @csrf
                    <div class="ps-form__content">
                        <h4>{{ theme_option('newsletter_popup_title') ?: __('Get 25% Discount') }}</h4>
                        <p>{{ theme_option('newsletter_popup_description') ?: __('Subscribe to the mailing list to receive updates on new arrivals, special offers and our promotions.') }}</p>
                        <div class="form-group">
                            <input class="form-control" name="email" type="email"  placeholder="{{ __('Email Address') }}" required>
                        </div>

                        {!! apply_filters('form_extra_fields_render', null, \Botble\Contact\Forms\Fronts\ContactForm::class) !!}

                        <div class="form-group">
                            <button class="ps-btn" type="submit" >{{ __('Subscribe') }}</button>
                        </div>
                        <div class="ps-checkbox">
                            <input class="form-control" type="checkbox" id="dont_show_again" name="dont_show_again">
                            <label for="dont_show_again">{{ __("Don't show this popup again") }}</label>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {!! Theme::get('bottomFooter') !!}

    <div id="back2top"><i class="icon icon-arrow-up"></i></div>
    <div class="ps-site-overlay"></div>
    @if (is_plugin_active('ecommerce'))
        <div class="ps-search" id="site-search"><a class="ps-btn--close" href="#"></a>
            <div class="ps-search__content">
                <form class="ps-form--primary-search" action="{{ route('public.products') }}" data-ajax-url="{{ route('public.ajax.search-products') }}" method="get">
                    <input class="form-control input-search-product" name="q" value="{{ BaseHelper::stringify(request()->query('q')) }}" type="text" autocomplete="off" placeholder="{{ __('Search for...') }}">
                    <div class="spinner-icon">
                        <i class="fa fa-spin fa-spinner"></i>
                    </div>
                    <button title="{{ __('Search') }}"><i class="aroma-magnifying-glass"></i></button>
                    <div class="ps-panel--search-result"></div>
                </form>
            </div>
        </div>
    @endif
    <div class="modal fade" id="product-quickview" tabindex="-1" role="dialog" aria-labelledby="product-quickview" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content"><span class="modal-close" data-dismiss="modal"><i class="icon-cross2"></i></span>
                <article class="ps-product--detail ps-product--fullwidth ps-product--quickview">
                </article>
            </div>
        </div>
    </div>

    <script>
        window.trans = {
            "View All": "{{ __('View All') }}",
            "No reviews!": "{{ __('No reviews!') }}",
        };
    </script>

    <div class="pwa-install-prompt" id="pwa-install-prompt" aria-hidden="true">
        <div class="pwa-install-prompt__card">
            <div class="pwa-install-prompt__content">
                <img
                    class="pwa-install-prompt__icon"
                    src="{{ asset('pwa/icons/icon-192.png') }}"
                    alt="{{ theme_option('site_title') ?: config('app.name', 'Seller Koi') }}"
                >
                <div>
                    <h2 class="pwa-install-prompt__title">{{ __('Add to Home Screen') }}</h2>
                    <p class="pwa-install-prompt__description">
                        {{ __('Install :appName for a faster, full-screen shopping experience on your Android device.', ['appName' => theme_option('site_title') ?: config('app.name', 'Seller Koi')]) }}
                    </p>
                </div>
            </div>
            <div class="pwa-install-prompt__actions">
                <button class="pwa-install-prompt__button pwa-install-prompt__button--ghost" type="button" id="pwa-install-dismiss">
                    {{ __('Not now') }}
                </button>
                <button class="pwa-install-prompt__button pwa-install-prompt__button--primary" type="button" id="pwa-install-button">
                    {{ __('Install') }}
                </button>
            </div>
        </div>
    </div>

    {!! Theme::footer() !!}

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register(@json(route('pwa.sw')), { scope: '/' }).catch(function (error) {
                    console.warn('PWA service worker registration failed.', error);
                });
            });
        }
    </script>

    <script>
        (function () {
            var promptKey = 'seller-koi-pwa-install-dismissed-at';
            var dismissCooldown = 7 * 24 * 60 * 60 * 1000;
            var deferredPrompt = null;
            var promptElement = document.getElementById('pwa-install-prompt');
            var installButton = document.getElementById('pwa-install-button');
            var dismissButton = document.getElementById('pwa-install-dismiss');

            if (!promptElement || !installButton || !dismissButton) {
                return;
            }

            function isAndroid() {
                return /android/i.test(window.navigator.userAgent || '');
            }

            function isStandalone() {
                return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
            }

            function wasDismissedRecently() {
                var dismissedAt = Number(window.localStorage.getItem(promptKey) || 0);

                return dismissedAt && (Date.now() - dismissedAt) < dismissCooldown;
            }

            function showPrompt() {
                if (!isAndroid() || isStandalone() || wasDismissedRecently() || !deferredPrompt) {
                    return;
                }

                promptElement.classList.add('is-visible');
                promptElement.setAttribute('aria-hidden', 'false');
            }

            function hidePrompt(persistDismissal) {
                promptElement.classList.remove('is-visible');
                promptElement.setAttribute('aria-hidden', 'true');

                if (persistDismissal) {
                    window.localStorage.setItem(promptKey, String(Date.now()));
                }
            }

            window.addEventListener('beforeinstallprompt', function (event) {
                event.preventDefault();
                deferredPrompt = event;

                window.setTimeout(showPrompt, 2500);
            });

            window.addEventListener('appinstalled', function () {
                deferredPrompt = null;
                hidePrompt(false);
                window.localStorage.removeItem(promptKey);
            });

            dismissButton.addEventListener('click', function () {
                hidePrompt(true);
            });

            installButton.addEventListener('click', async function () {
                if (!deferredPrompt) {
                    hidePrompt(true);
                    return;
                }

                deferredPrompt.prompt();

                var choiceResult = await deferredPrompt.userChoice;

                if (choiceResult && choiceResult.outcome !== 'accepted') {
                    hidePrompt(true);
                } else {
                    hidePrompt(false);
                }

                deferredPrompt = null;
            });
        })();
    </script>

    </body>
</html>
