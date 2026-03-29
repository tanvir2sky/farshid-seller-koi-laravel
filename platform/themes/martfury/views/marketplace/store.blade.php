<div class="ps-page--single ps-page--vendor">
    <section class="ps-store-list">
        <div class="container">
            @php $coverImage = $store->getMetaData('cover_image', true); @endphp
            <aside class="ps-block--store-banner" @if ($coverImage) style="background-image: url({{ RvMedia::getImageUrl($coverImage) }}); background-repeat: no-repeat;
                background-size: cover;
                background-position: center;background-color: #2f2f2f;" @else style="background-color: #2f2f2f;" @endif>
                <div class="ps-block__user" @if ($coverImage) style="background: rgba(0, 0, 0, 0.3)" @endif>
                    <div class="ps-block__user-avatar">
                        <img src="{{ $store->logo_url }}" alt="{{ $store->name }}">
                        @if (EcommerceHelper::isReviewEnabled())
                            <div class="rating_wrap">
                                <div class="rating">
                                    <div class="product_rate" style="width: {{ $store->reviews()->avg('star') * 20 }}%"></div>
                                </div>
                                <span class="rating_num">({{ $store->reviews()->count() }})</span>
                            </div>
                        @endif
                    </div>
                    <div class="ps-block__user-content">
                        <h3 class="text-white">{{ $store->name }}</h3>
                        @if (! MarketplaceHelper::hideStoreAddress() && $store->full_address)
                            <p><i class="icon-map-marker" @if ($coverImage) style="color: #fff" @endif></i>&nbsp;{{ $store->full_address }}</p>
                        @endif
                        @if (!MarketplaceHelper::hideStorePhoneNumber() && $store->phone)
                            <p><i class="icon-telephone" @if ($coverImage) style="color: #fff" @endif></i>&nbsp;{{ $store->phone }}</p>
                        @endif
                        @if (!MarketplaceHelper::hideStoreEmail() && $store->email)
                            <p><i class="icon-envelope" @if ($coverImage) style="color: #fff" @endif></i>&nbsp;<a href="mailto:{{ $store->email }}">{{ $store->email }}</a></p>
                        @endif

                        @php
                            $social_links = $store->getMetaData('social_links', true);
                        @endphp
                        @if(
                            (!empty($social_links['facebook']) && $social_links['facebook']) ||
                            (!empty($social_links['instagram']) && $social_links['instagram']) ||
                            (!empty($social_links['whatsapp']) && $social_links['whatsapp'])
                        )
                            <ul class="ps-block__user-social mt-3">
                                @if (!empty($social_links['facebook']))
                                    <li>
                                        <a href="https://facebook.com/{{ ltrim($social_links['facebook'], '/') }}" target="_blank" title="Facebook">
                                            <svg class="icon svg-icon-ti-ti-brand-facebook" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3"></path></svg>
                                        </a>
                                    </li>
                                @endif
                                @if (!empty($social_links['instagram']))
                                    <li>
                                        <a href="https://instagram.com/{{ ltrim($social_links['instagram'], '/') }}" target="_blank" title="Instagram">
                                            <svg class="icon svg-icon-ti-ti-brand-instagram" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4l0 -8"></path><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path><path d="M16.5 7.5v.01"></path></svg>
                                        </a>
                                    </li>
                                @endif
                                @if (!empty($social_links['whatsapp']))
                                    <li>
                                        <a href="{{ $social_links['whatsapp'] }}" target="_blank" title="Whatsapp">
                                            <svg class="icon svg-icon-ti-ti-brand-whatsapp" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9"></path><path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1"></path></svg>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>
                </div>
                @php
                    $description = BaseHelper::clean($store->description);
                    $content = BaseHelper::clean($store->content);
                @endphp

                @if ($description || $content)
                    <div class="py-4 mb-4 bg-light">
                        <div class="px-4">
                            @if ($content)
                                <div
                                    id="store-content"
                                    class="d-none"
                                >
                                    {!! $content !!}
                                </div>
                            @endif

                            <div id="store-short-description">
                                {!! $description ?: Str::limit($content, 250) !!}
                            </div>

                            <a
                                class="text-link toggle-show-more ms-1"
                                href="#"
                            >{{ __('show more') }}</a>
                            <a
                                class="text-link toggle-show-less ms-1 d-none"
                                href="#"
                            >{{ __('show less') }}</a>
                        </div>
                    </div>
                @endif
            </aside>
            <div class="ps-section__wrapper">
                @if ($canContactStore = (MarketplaceHelper::isEnabledMessagingSystem() && (! auth('customer')->check() || $store->id != auth('customer')->user()->store->id)))
                    <div class="ps-layout--shop">
                        <div class="ps-layout__left">
                            <div class="store-contact-form mb-4 bg-light p-4">
                                <h3 class="fs-4">{{ __('Email :store', ['store' => $store->name]) }}</h3>
                                <p>{{ __('All messages are recorded and spam is not tolerated. Your email address will be shown to the recipient.') }}</p>
                                {!!
                                    $contactForm
                                    ->setFormOption('class', 'ps-form--contact-us contact-form bb-contact-store-form')
                                    ->setFormInputClass('form-control')
                                    ->setFormLabelClass('d-none sr-only')
                                    ->modify(
                                        'submit',
                                        'submit',
                                        Botble\Base\Forms\FieldOptions\ButtonFieldOption::make()
                                            ->addAttribute('data-bb-loading', 'button-loading')
                                            ->cssClass('ps-btn')
                                            ->label(__('Send message'))
                                            ->wrapperAttributes(['class' => 'form-group submit'])
                                            ->toArray(),
                                        true
                                    )
                                    ->renderForm()
                                !!}
                        </div>

                        @include(MarketplaceHelper::viewPath('includes.contact-form-script'))
                </div>
                <div class="ps-layout__right">
                    @endif
                    <div class="ps-shopping ps-tab-root">
                            <div class="ps-section__search">
                                <div class="mb-3">
                                    <form
                                        class="products-filter-form-vendor"
                                        action="{{ URL::current() }}"
                                        method="GET"
                                    >
                                        <div class="form-group mb-5">
                                            <button><i class="icon-magnifier"></i></button>
                                            <input class="form-control" name="q" value="{{ BaseHelper::stringify(request()->query('q')) }}" type="text" placeholder="{{ __('Search in this store...') }}">
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="ps-shopping__header">
                                <p><strong> {{ $products->total() }}</strong> {{ __('Products found') }}</p>
                                <div class="ps-shopping__actions">
                                    <div class="ps-shopping__view">
                                        <p>{{ __('View') }}</p>
                                        <ul class="ps-tab-list">
                                            <li class="active"><a href="#tab-1"><i class="icon-grid"></i></a></li>
                                            <li><a href="#tab-2"><i class="icon-list4"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <div class="ps-tabs">
                            <div class="ps-tab active" id="tab-1">
                                <div class="ps-shopping-product">
                                    <div class="row">
                                        @if ($products->isNotEmpty())
                                            @foreach($products as $product)
                                                <div class="col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6 ">
                                                    <div class="ps-product">
                                                        {!! Theme::partial('product-item', ['product' => $product, 'lazy' => false]) !!}
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                                <div class="ps-pagination">
                                    {!! $products->withQueryString()->links() !!}
                                </div>
                            </div>
                            <div class="ps-tab" id="tab-2">
                                <div class="ps-shopping-product">
                                    @if ($products->isNotEmpty())
                                        @foreach($products as $product)
                                            <div class="ps-product ps-product--wide">
                                                {!! Theme::partial('product-item-grid', ['product' => $product, 'lazy' => false]) !!}
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="ps-pagination">
                                    {!! $products->withQueryString()->links() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                        @if ($canContactStore)
                </div>
                    @endif
            </div>
        </div>
    </section>
</div>