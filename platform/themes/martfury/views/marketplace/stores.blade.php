<div class="ps-page--single ps-page--vendor">
    <section class="ps-store-list">
        <div class="container">
            <div class="ps-section__header">
                <h3>{{ __('Our Stores') }}</h3>
            </div>
            <div class="ps-section__content">
                <div class="ps-section__search row align-items-start">
                    <div class="col-md-4">
                        <form action="{{ route('public.stores') }}" method="get">
                            @if (request()->filled('category'))
                                <input type="hidden" name="category" value="{{ (int) request()->query('category') }}">
                            @endif
                            <div class="form-group mb-3">
                                <button><i class="icon-magnifier"></i></button>
                                <input class="form-control" name="q" value="{{ BaseHelper::stringify(request()->query('q')) }}" type="text" placeholder="{{ __('Search vendor...') }}">
                            </div>
                        </form>
                    </div>
                    @if (isset($storeFilterCategories) && $storeFilterCategories->isNotEmpty())
                        <div class="col-md-8">
                            <p class="mb-2 text-muted">{{ trans('plugins/marketplace::store-category.filter_by_category') }}</p>
                            <ul class="list-inline mb-3">
                                <li class="list-inline-item mb-1">
                                    <a class="ps-btn ps-btn--sm @if (! $activeStoreCategory) ps-btn--primary @else ps-btn--outline @endif" href="{{ route('public.stores', array_filter(['q' => request()->query('q')])) }}">{{ trans('plugins/marketplace::store-category.all_categories') }}</a>
                                </li>
                                @foreach ($storeFilterCategories as $filterCategory)
                                    <li class="list-inline-item mb-1">
                                        <a class="ps-btn ps-btn--sm @if ($activeStoreCategory && $activeStoreCategory->is($filterCategory)) ps-btn--primary @else ps-btn--outline @endif" href="{{ route('public.stores', array_filter(['category' => $filterCategory->getKey(), 'q' => request()->query('q')])) }}">{{ $filterCategory->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                @include(Theme::getThemeNamespace('views.marketplace.includes.store-items'))

                <div class="ps-pagination">
                    {!! $stores->withQueryString()->links() !!}
                </div>
            </div>
        </div>
    </section>
</div>
