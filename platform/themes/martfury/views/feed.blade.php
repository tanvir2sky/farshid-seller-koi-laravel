@php
    Theme::layout('default');
@endphp

<section class="ps-page--feed py-5">
    <div class="container">
        <div class="feed-wrapper">
            <div id="feed-flash-message" class="alert alert-success d-none mb-3" role="alert"></div>

            <div class="feed-card mb-4 d-flex justify-content-end">
                <a class="ps-btn ps-btn--sm" href="{{ route('public.feed.followed-stores') }}">{{ __('View All Followed Store') }}</a>
            </div>

            @if (auth('customer')->user()->is_vendor)
                <div class="feed-card mb-4">
                    <h4 class="mb-3">{{ __('Create product') }}</h4>
                    <form id="feed-create-product-form" action="{{ route('public.feed.products.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>{{ __('Product name') }}</label>
                            <input class="form-control" name="name" required maxlength="250">
                        </div>
                        <div class="form-group">
                            <label>{{ __('Category') }}</label>
                            <select class="form-control" name="categories[]" required>
                                <option value="">{{ __('Select category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <a href="#" class="text-link d-inline-block mb-3" data-toggle-options>{{ __('More options') }}</a>
                        <div class="feed-product-options d-none">
                            <div class="form-group">
                                <label>{{ __('Price') }}</label>
                                <input class="form-control" name="price" type="number" step="0.01" min="0" value="0">
                            </div>
                            <div class="form-group">
                                <label>{{ __('Quantity') }}</label>
                                <input class="form-control" name="quantity" type="number" min="0" value="0">
                            </div>
                            <div class="form-group">
                                <label>{{ __('SKU (optional)') }}</label>
                                <input class="form-control" name="sku">
                            </div>
                            <div class="form-group">
                                <label>{{ __('Short description') }}</label>
                                <textarea class="form-control" name="description" rows="2"></textarea>
                            </div>
                        </div>
                        <button class="ps-btn">{{ __('Post product') }}</button>
                    </form>
                </div>
            @endif

            <div id="feed-items" data-next-page="{{ $nextFeedItemsUrl }}">
                {!! Theme::partial('feed-items', compact('products', 'likedProductIds', 'likeCounts', 'followedStores', 'comments', 'commentCounts')) !!}
            </div>
            <div id="feed-loader" class="text-center py-3 d-none">{{ __('Loading...') }}</div>
        </div>
    </div>
</section>

<style>
    .feed-wrapper { max-width: 760px; margin: 0 auto; }
    .feed-card { background: #fff; border: 1px solid #e6e6e6; border-radius: 8px; padding: 16px; }
    .feed-meta { font-size: 13px; color: #666; }
    .feed-actions { display: flex; gap: 16px; margin: 10px 0; }
    .feed-action-btn { border: 0; background: transparent; padding: 0; color: #333; font-weight: 500; }
    .feed-action-btn.is-active { color: #007bff; }
    .feed-follow-btn {
        border: 1px solid #1877f2;
        background: #1877f2;
        color: #fff;
        border-radius: 999px;
        padding: 6px 14px;
        font-size: 13px;
        line-height: 1.2;
    }
    .feed-follow-btn.is-active {
        border-color: #d0d7e2;
        background: #f0f2f5;
        color: #1c1e21;
    }
    .feed-comments { border-top: 1px solid #efefef; margin-top: 12px; padding-top: 12px; }
    .feed-comment-item { font-size: 13px; margin-bottom: 8px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const feedItems = document.getElementById('feed-items');
    const loader = document.getElementById('feed-loader');
    const createForm = document.getElementById('feed-create-product-form');
    const toggleOptions = document.querySelector('[data-toggle-options]');
    const flashMessage = document.getElementById('feed-flash-message');

    const showFlash = function (message) {
        if (!flashMessage || !message) return;
        flashMessage.textContent = message;
        flashMessage.classList.remove('d-none');
        window.setTimeout(function () {
            flashMessage.classList.add('d-none');
            flashMessage.textContent = '';
        }, 2200);
    };

    if (toggleOptions) {
        toggleOptions.addEventListener('click', function (e) {
            e.preventDefault();
            const box = document.querySelector('.feed-product-options');
            box.classList.toggle('d-none');
        });
    }

    if (createForm) {
        createForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const response = await fetch(createForm.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: new FormData(createForm),
            });
            const data = await response.json();
            if (response.ok) {
                showFlash(data.message);
                window.location.href = data.redirect;
            }
        });
    }

    document.addEventListener('click', async function (e) {
        const likeBtn = e.target.closest('[data-feed-like]');
        const followBtn = e.target.closest('[data-feed-follow]');
        if (likeBtn) {
            e.preventDefault();
            const response = await fetch(likeBtn.dataset.url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            });
            if (response.ok) {
                const data = await response.json();
                const countNode = likeBtn.querySelector('[data-like-count]');
                const currentCount = Number(countNode?.textContent || 0);
                const isLiked = likeBtn.dataset.liked === '1';
                const nextLiked = !isLiked;
                likeBtn.dataset.liked = nextLiked ? '1' : '0';
                likeBtn.classList.toggle('is-active', nextLiked);
                if (countNode) {
                    countNode.textContent = String(Math.max(0, currentCount + (nextLiked ? 1 : -1)));
                }
                showFlash(data.message);
            }
        }
        if (followBtn) {
            e.preventDefault();
            const currentlyFollowing = followBtn.dataset.following === '1';
            const response = await fetch(followBtn.dataset.url, {
                method: currentlyFollowing ? 'DELETE' : 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            });
            if (response.ok) {
                const data = await response.json();
                const nextFollowing = !currentlyFollowing;
                followBtn.dataset.following = nextFollowing ? '1' : '0';
                followBtn.dataset.url = nextFollowing ? followBtn.dataset.unfollowUrl : followBtn.dataset.followUrl;
                followBtn.dataset.method = nextFollowing ? 'DELETE' : 'POST';
                followBtn.classList.toggle('is-active', nextFollowing);
                followBtn.textContent = nextFollowing ? followBtn.dataset.followingLabel : followBtn.dataset.followLabel;
                showFlash(data.message);
            }
        }
    });

    document.addEventListener('submit', async function (e) {
        const form = e.target.closest('[data-feed-comment-form]');
        if (!form) return;
        e.preventDefault();
        const response = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: new FormData(form),
        });
        if (response.ok) {
            const data = await response.json();
            const list = form.closest('.feed-comments')?.querySelector('[data-comment-list]');
            const countNode = form.closest('.feed-card')?.querySelector('[data-comment-count]');
            if (list && data.comment) {
                const item = document.createElement('div');
                item.className = 'feed-comment-item';
                item.innerHTML = '<strong>' + data.comment.author + '</strong> ' + data.comment.content;
                list.prepend(item);
            }
            if (countNode) {
                const nextCount = Number(countNode.textContent || 0) + 1;
                countNode.textContent = String(nextCount);
            }
            form.reset();
            showFlash(data.message);
        }
    });

    const loadNext = async function () {
        const nextPage = feedItems.dataset.nextPage;
        if (!nextPage || loader.classList.contains('is-loading')) return;
        loader.classList.remove('d-none');
        loader.classList.add('is-loading');
        const response = await fetch(nextPage, { headers: { 'Accept': 'application/json' } });
        const data = await response.json();
        feedItems.insertAdjacentHTML('beforeend', data.html);
        feedItems.dataset.nextPage = data.next_page_url || '';
        loader.classList.add('d-none');
        loader.classList.remove('is-loading');
    };

    window.addEventListener('scroll', function () {
        if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 350)) {
            loadNext();
        }
    });
});
</script>
