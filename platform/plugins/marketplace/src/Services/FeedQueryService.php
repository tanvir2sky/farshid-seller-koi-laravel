<?php

namespace Botble\Marketplace\Services;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Enums\FeedAlgorithmEnum;
use Botble\Marketplace\Enums\StoreStatusEnum;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FeedQueryService
{
    public function __construct(
        protected FeedPinService $feedPinService
    ) {
    }

    public function getFeedPage(
        ?Customer $customer,
        int $page,
        int $perPage = 8,
        bool $includePinsOnPageOne = true
    ): FeedPageResult {
        $recent = $includePinsOnPageOne && $page === 1
            ? $this->resolveRecentUserPostsFromCookie()
            : collect();

        $pinned = $includePinsOnPageOne && $page === 1
            ? $this->feedPinService->resolvePinnedProducts()
            : collect();

        $excludeIds = array_values(array_unique(array_merge(
            $this->feedPinService->excludedProductIdsForOrganic(),
            $recent->pluck('id')->all(),
            $pinned->pluck('id')->all(),
        )));

        $recentShown = min($recent->count(), $perPage);
        $pinnedShown = min($pinned->count(), max(0, $perPage - $recentShown));
        $organicLimitPage1 = max(0, $perPage - $recentShown - $pinnedShown);

        $organicOffset = $page === 1
            ? 0
            : $organicLimitPage1 + ($page - 2) * $perPage;

        $organicLimit = $page === 1 ? $organicLimitPage1 : $perPage;

        $algorithm = MarketplaceHelper::getFeedAlgorithm();

        if (! session()->has('feed_algo_seed')) {
            session(['feed_algo_seed' => random_int(1, 2147483646)]);
        }

        $seed = (int) session('feed_algo_seed');

        $organicQuery = $this->baseProductQuery($customer)
            ->when($excludeIds !== [], fn (Builder $q) => $q->whereNotIn('ec_products.id', $excludeIds));

        $organicTotal = (clone $organicQuery)->toBase()->getCountForPagination();

        $organicItems = (clone $organicQuery)
            ->tap(fn (Builder $q) => $this->applyAlgorithm($q, $algorithm, $customer, $seed))
            ->offset($organicOffset)
            ->limit($organicLimit)
            ->get();

        $items = $page === 1
            ? $recent
                ->take($recentShown)
                ->concat($pinned->take($pinnedShown))
                ->concat($organicItems)
                ->unique('id')
                ->values()
                ->take($perPage)
            : $organicItems;

        $total = $organicTotal + $pinned->count() + $recent->count();

        $consumedP1 = min($organicLimitPage1, $organicTotal);
        $remaining = max(0, $organicTotal - $consumedP1);
        $additionalPages = $remaining === 0 ? 0 : (int) ceil($remaining / $perPage);
        $lastPage = max(1, 1 + $additionalPages);

        return new FeedPageResult(
            $items,
            $page,
            $lastPage,
            $perPage,
            $total,
        );
    }

    /**
     * @deprecated Use getFeedPage(); kept for tests calling getProducts directly.
     */
    public function getProducts(?Customer $customer, int $perPage = 8): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $result = $this->getFeedPage($customer, request()->integer('page', 1), $perPage, true);

        return $result->toPaginator();
    }

    public function getLikeCounts(Collection $productIds): Collection
    {
        if ($productIds->isEmpty()) {
            return collect();
        }

        return DB::table('ec_wish_lists')
            ->selectRaw('product_id, COUNT(*) as total')
            ->whereIn('product_id', $productIds->all())
            ->groupBy('product_id')
            ->pluck('total', 'product_id');
    }

    protected function baseProductQuery(?Customer $customer): Builder
    {
        return Product::query()
            ->select('ec_products.*')
            ->leftJoin('mp_store_followers as feed_follows', function ($join) use ($customer): void {
                $join->on('feed_follows.store_id', '=', 'ec_products.store_id');

                if ($customer) {
                    $join->where('feed_follows.customer_id', '=', $customer->getKey());
                } else {
                    $join->whereRaw('0 = 1');
                }
            })
            ->where('ec_products.status', BaseStatusEnum::PUBLISHED)
            ->where('ec_products.is_variation', 0)
            ->whereNotNull('ec_products.store_id')
            ->whereHas('store', function (Builder $query): void {
                $query->where('status', StoreStatusEnum::PUBLISHED);
            })
            ->with([
                'slugable',
                'store',
                'store.slugable',
            ]);
    }

    protected function resolveRecentUserPostsFromCookie(): Collection
    {
        $ids = FeedRecentPostsCookie::activeProductIds();

        if ($ids === []) {
            return collect();
        }

        $products = Product::query()
            ->whereIn('ec_products.id', $ids)
            ->where('ec_products.status', BaseStatusEnum::PUBLISHED)
            ->where('ec_products.is_variation', 0)
            ->whereNotNull('ec_products.store_id')
            ->where('ec_products.stock_status', 'in_stock')
            ->whereHas('store', fn (Builder $query) => $query->where('status', StoreStatusEnum::PUBLISHED))
            ->with(['slugable', 'store', 'store.slugable'])
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(fn (int $id) => $products->get($id))
            ->filter()
            ->values();
    }

    protected function applyAlgorithm(Builder $query, string $algorithm, ?Customer $customer, int $seed): void
    {
        switch ($algorithm) {
            case FeedAlgorithmEnum::FOLLOW_FIRST_THEN_RANDOM:
                $query
                    ->orderByRaw('CASE WHEN feed_follows.id IS NULL THEN 1 ELSE 0 END')
                    ->orderByRaw('RAND(' . $seed . ')');

                break;

            case FeedAlgorithmEnum::NEWEST:
                $query->orderByDesc('ec_products.created_at')->orderByDesc('ec_products.id');

                break;

            case FeedAlgorithmEnum::POPULAR_BY_VIEWS:
                $query->orderByDesc('ec_products.views')->orderByDesc('ec_products.created_at');

                break;

            case FeedAlgorithmEnum::FOLLOW_BIASED_RANDOM:
            default:
                $query->orderByRaw('(RAND(' . $seed . ') + CASE WHEN feed_follows.id IS NULL THEN 0 ELSE 0.35 END) DESC');

                break;
        }
    }
}
