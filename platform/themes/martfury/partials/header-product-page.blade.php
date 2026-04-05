<header class="header header--product" data-sticky="true">
    <nav class="navigation">
        <div class="container">
            <article class="ps-product--header-sticky">
                <div class="ps-product__thumbnail">
                    {!! RvMedia::image($product->image, $product->name, 'small') !!}
                </div>
                <div class="ps-product__wrapper">
                    <div class="ps-product__content">
                        <span class="ps-product__title">{!! BaseHelper::clean($product->name) !!}</span>
                        <ul class="ps-tab-list">
                            <li class="active"><a href="#tab-description">{{ __('Description') }}</a></li>
                            @if (EcommerceHelper::isReviewEnabled())
                                <li><a href="#tab-reviews">{{ __('Reviews') }} ({{ $product->reviews_count }})</a></li>
                            @endif
                        </ul>
                    </div>
                    <div class="ps-product__shopping">
                        @if (! EcommerceHelper::hideProductPrice() || EcommerceHelper::isCartEnabled())
                            @if ($product->hasPriceRange())
                                @php
                                    $maxPrice = $product->max_price;

                                    if (EcommerceHelper::isDisplayProductIncludingTaxes()) {
                                        $maxPrice += $maxPrice * ($product->total_taxes_percentage / 100);
                                    }
                                @endphp
                                <span class="ps-product__price">
                                    <span>{{ format_price($product->price_with_taxes) }} - {{ format_price($maxPrice) }}</span>
                                </span>
                            @else
                                <span class="ps-product__price">
                                    <span>{{ format_price($product->front_sale_price_with_taxes) }}</span>
                                    @if ($product->front_sale_price !== $product->price)
                                        <del>{{ format_price($product->price_with_taxes) }}</del>
                                    @endif
                                </span>
                            @endif
                        @endif
                        @if (EcommerceHelper::isCartEnabled())
                            <button class="ps-btn add-to-cart-button @if ($product->isOutOfStock()) btn-disabled @endif" type="button" name="add_to_cart" value="1" {!! EcommerceHelper::jsAttributes('add-to-cart', $product, additional: ['data-bb-toggle' => 'none']) !!}>{{ __('Add to cart') }}</button>
                        @endif
                    </div>
                </div>
            </article>
        </div>
    </nav>
</header>
