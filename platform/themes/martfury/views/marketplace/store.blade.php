<div class="ps-page--single ps-page--vendor">
    <style>
        .ps-block--store-banner .ps-block__user-social li,
        .ps-block--store-banner .ps-block__user-social li a,
        .ps-block--store-banner .ps-block__user-social li:hover,
        .ps-block--store-banner .ps-block__user-social li a:hover {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }
    </style>

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
                                            <img src="{{ asset('themes/martfury/img/facebook.svg') }}" alt="Facebook">
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
                                            <img src="{{ asset('themes/martfury/img/whatsapp.svg') }}" alt="Whatsapp">
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
            @php
                $canContactStore = MarketplaceHelper::isEnabledMessagingSystem() && (! auth('customer')->check() || $store->id != auth('customer')->user()->store->id);
                $storeProductCategories = $storeProductCategories ?? collect();
                $storeProductFilterCategoryNames = $storeProductFilterCategoryNames ?? [];
                $hasCategoryFilter = $storeProductCategories->isNotEmpty();
                $useStoreSidebar = $canContactStore || $hasCategoryFilter;
            @endphp
            <div class="ps-section__wrapper">
                @if ($useStoreSidebar)
                    <div class="ps-layout--shop">
                        <div class="ps-layout__left">
                            @if ($hasCategoryFilter)
                                <div class="d-none d-md-block">
                                    @include(Theme::getThemeNamespace('views.marketplace.includes.store-product-category-filter'))
                                </div>
                            @endif
                            @if ($canContactStore)
                                <div class="store-contact-form mb-4 bg-light p-4">
                                    <h3 class="fs-4">
                                        {{ auth('customer')->check() ? __('Message :store', ['store' => $store->name]) : __('Email :store', ['store' => $store->name]) }}
                                    </h3>

                                    @if (auth('customer')->check())
                                        <p>{{ __('Your conversation with this store will open after the first message. New replies appear automatically while you are on the message page.') }}</p>

                                        @if (auth('customer')->id() && $store->id != auth('customer')->user()->store->id)
                                            <p class="mb-3">
                                                <a class="text-link" href="{{ route('customer.messages.show', $store->getKey()) }}">
                                                    {{ __('Open conversation') }}
                                                </a>
                                            </p>
                                        @endif
                                    @else
                                        <p>{{ __('All messages are recorded and spam is not tolerated. Your email address will be shown to the recipient.') }}</p>
                                    @endif

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
                            @endif
                        </div>
                        <div class="ps-layout__right">
                @endif
                    <div class="ps-shopping ps-tab-root">
                            <div class="ps-section__search">
                                <div class="mb-3">
                                    <form
                                        class="products-filter-form-vendor"
                                        action="{{ $store->url }}"
                                        method="GET"
                                    >
                                        @foreach ((array) request()->input('categories', []) as $categoryFilterId)
                                            @continue(! (int) $categoryFilterId)
                                            <input type="hidden" name="categories[]" value="{{ (int) $categoryFilterId }}">
                                        @endforeach
                                        <div class="form-group mb-5">
                                            <button><i class="icon-magnifier"></i></button>
                                            <input class="form-control" name="q" value="{{ BaseHelper::stringify(request()->query('q')) }}" type="text" placeholder="{{ __('Search in this store...') }}">
                                        </div>
                                    </form>
                                </div>
                                <!-- Mobile Only: Show product categories filter at the top -->
                                @if ($hasCategoryFilter)
                                    <div class="d-block d-md-none mb-3">
                                        @include(Theme::getThemeNamespace('views.marketplace.includes.store-product-category-filter'))
                                    </div>
                                @endif
                            </div>
                            <div class="ps-shopping__header">
                                <p>
                                    <strong>{{ $products->total() }}</strong>
                                    @if (! empty($storeProductFilterCategoryNames))
                                        @if ($products->total() === 1)
                                            {{ trans('plugins/marketplace::store.product_found_in_category', ['categories' => implode(', ', $storeProductFilterCategoryNames)]) }}
                                        @else
                                            {{ trans('plugins/marketplace::store.products_found_in_category', ['categories' => implode(', ', $storeProductFilterCategoryNames)]) }}
                                        @endif
                                    @else
                                        {{ __('Products found') }}
                                    @endif
                                </p>
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
                @if ($useStoreSidebar)
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
