@php
    $selectedCategoryIds = collect((array) request()->input('categories', []))
        ->map(static fn ($id) => (int) $id)
        ->filter(static fn (int $id) => $id > 0)
        ->unique()
        ->values()
        ->all();
    $searchOnly = array_filter(['q' => request()->query('q')]);
@endphp

@if ($storeProductCategories->isNotEmpty())
    @once('vendor-store-product-category-filter-styles')
        <style>
            .store-product-categories .store-product-categories__list li.active > a {
                color: #222 !important;
                font-weight: 600;
                background-color: #fff !important;
                border-left: 3px solid var(--color-1st, #fcb800);
                box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.06);
                padding: 5px !important;
                margin-left: 3px !important;
            }
            body[dir="rtl"] .store-product-categories .store-product-categories__list li.active > a {
                border-left: none;
                border-right: 3px solid var(--color-1st, #fcb800);
            }
        </style>
    @endonce
    <div class="widget_shop store-product-categories mb-4">
        <h4 class="widget-title">{{ __('Product categories') }}</h4>
        <nav class="store-product-categories__nav" aria-label="{{ __('Product categories') }}">
            <ul class="store-product-categories__list">
                <li class="{{ empty($selectedCategoryIds) ? 'active' : '' }}">
                    <a
                        href="{{ $store->url }}{{ $searchOnly ? '?' . http_build_query($searchOnly) : '' }}"
                        @if (empty($selectedCategoryIds)) aria-current="true" @endif
                    >{{ __('All products') }}</a>
                </li>
                @foreach ($storeProductCategories as $productCategory)
                    @php
                        $filterParams = $searchOnly + ['categories' => [$productCategory->getKey()]];
                        $isCurrentCategory = in_array((int) $productCategory->getKey(), $selectedCategoryIds, true);
                    @endphp
                    <li class="{{ $isCurrentCategory ? 'active' : '' }}">
                        <a
                            href="{{ $store->url }}?{{ http_build_query($filterParams) }}"
                            @if ($isCurrentCategory) aria-current="true" @endif
                        >{{ $productCategory->name }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
@endif
