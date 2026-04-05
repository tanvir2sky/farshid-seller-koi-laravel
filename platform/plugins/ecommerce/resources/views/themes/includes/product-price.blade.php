@if (! EcommerceHelper::hideProductPrice() || EcommerceHelper::isCartEnabled())
    @php
        $isDisplayPriceOriginal ??= true;
        $priceWrapperClassName ??= null;
        $priceClassName ??= null;
        $priceOriginalClassName ??= null;
        $priceOriginalWrapperClassName ??= null;
    @endphp

    <div class="{{ $priceWrapperClassName === null ? 'bb-product-price mb-3' : $priceWrapperClassName }}">
        @if ($product->hasPriceRange())
            @php
                $maxPrice = $product->max_price;

                if (EcommerceHelper::isDisplayProductIncludingTaxes()) {
                    $maxPrice += $maxPrice * ($product->total_taxes_percentage / 100);
                }
            @endphp

            <span
                class="{{ $priceClassName === null ? 'bb-product-price-text fw-bold' : $priceClassName }}"
                data-bb-value="product-price"
            >
                {{ format_price($product->price_with_taxes) }} - {{ format_price($maxPrice) }}
            </span>
        @else
            <span
                class="{{ $priceClassName === null ? 'bb-product-price-text fw-bold' : $priceClassName }}"
                data-bb-value="product-price"
            >{{ $product->price()->displayAsText() }}</span>

            @if ($isDisplayPriceOriginal && $product->isOnSale())
                @include(EcommerceHelper::viewPath('includes.product-prices.original'), [
                    'priceWrapperClassName' => $priceOriginalWrapperClassName,
                    'priceClassName' => $priceOriginalClassName,
                ])
            @endif
        @endif
    </div>
@endif
