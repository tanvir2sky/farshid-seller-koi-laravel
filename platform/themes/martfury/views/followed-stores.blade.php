@php
    Theme::layout('default');
@endphp

<section class="py-5">
    <div class="container">
        <div class="feed-wrapper">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">{{ __('Followed stores') }}</h3>
                <a class="ps-btn ps-btn--sm" href="{{ route('public.feed') }}">{{ __('Back to feed') }}</a>
            </div>

            @if($stores->isEmpty())
                <div class="feed-card">
                    <p class="mb-0">{{ __('You are not following any stores yet.') }}</p>
                </div>
            @else
                <div class="row">
                    @foreach($stores as $store)
                        <div class="col-md-6 mb-3">
                            <div class="feed-card h-100 d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">{{ $store->name }}</h5>
                                    <small class="text-muted">{{ __('Store') }}</small>
                                </div>
                                <a class="ps-btn ps-btn--sm" href="{{ $store->url }}">{{ __('Visit') }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="ps-pagination">
                    {!! $stores->withQueryString()->links() !!}
                </div>
            @endif
        </div>
    </div>
</section>
