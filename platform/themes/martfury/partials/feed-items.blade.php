@foreach($products as $product)
    @php
        $store = $product->store;
        $liked = isset($likedProductIds[$product->id]);
        $followed = $store && isset($followedStores[$store->id]);
        $productComments = $comments->get($product->id, collect());
    @endphp
    <article class="feed-card mb-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <strong>{{ $product->name }}</strong>
                <div class="feed-meta">
                    {{ __('By') }}
                    @if($store && $store->url)
                        <a href="{{ $store->url }}">{{ $store->name }}</a>
                    @else
                        <span>{{ __('Vendor') }}</span>
                    @endif
                    · {{ $product->created_at?->diffForHumans() }}
                </div>
            </div>
            @if($store)
                <button
                    class="feed-action-btn feed-follow-btn {{ $followed ? 'is-active' : '' }}"
                    data-feed-follow
                    data-following="{{ $followed ? '1' : '0' }}"
                    data-follow-label="{{ __('Follow') }}"
                    data-following-label="{{ __('Unfollow') }}"
                    data-method="{{ $followed ? 'DELETE' : 'POST' }}"
                    data-url="{{ $followed ? route('public.feed.unfollow', $store->id) : route('public.feed.follow', $store->id) }}"
                    data-follow-url="{{ route('public.feed.follow', $store->id) }}"
                    data-unfollow-url="{{ route('public.feed.unfollow', $store->id) }}"
                >
                    {{ $followed ? __('Unfollow') : __('Follow') }}
                </button>
            @endif
        </div>

        <div class="mt-2">
            <a href="{{ $product->url }}">
                <img
                    src="{{ RvMedia::getImageUrl($product->image, 'medium', false, RvMedia::getDefaultImage()) }}"
                    alt="{{ $product->name }}"
                    loading="lazy"
                    class="w-100"
                    style="border-radius: 8px; max-height: 420px; object-fit: contain;"
                >
            </a>
        </div>

        <div class="feed-actions">
            <button
                class="feed-action-btn {{ $liked ? 'is-active' : '' }}"
                data-feed-like
                data-liked="{{ $liked ? '1' : '0' }}"
                data-url="{{ route('public.wishlist.add', $product->id) }}"
            >
                {{ __('Like') }} (<span data-like-count>{{ (int) ($likeCounts[$product->id] ?? 0) }}</span>)
            </button>
            <span class="feed-meta">{{ __('Comments') }}: <span data-comment-count>{{ (int) ($commentCounts[$product->id] ?? 0) }}</span></span>
        </div>

        <div class="feed-comments">
            <div data-comment-list>
                @foreach($productComments as $comment)
                    <div class="feed-comment-item">
                        <strong>{{ $comment->customer->name }}</strong>
                        {{ $comment->content }}
                    </div>
                @endforeach
            </div>

            <form action="{{ route('public.feed.comments') }}" method="POST" data-feed-comment-form>
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="d-flex align-items-center gap-2">
                    <input class="form-control" name="content" placeholder="{{ __('Write a comment... (emoji supported)') }}" required maxlength="1000">
                    <button class="ps-btn ps-btn--sm">{{ __('Post') }}</button>
                </div>
            </form>
        </div>
    </article>
@endforeach
