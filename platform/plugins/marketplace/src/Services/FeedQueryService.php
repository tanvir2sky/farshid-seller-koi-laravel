<?php

namespace Botble\Marketplace\Services;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Enums\StoreStatusEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FeedQueryService
{
    public function getProducts(Customer $customer, int $perPage = 8): LengthAwarePaginator
    {
        return Product::query()
            ->select('ec_products.*')
            ->leftJoin('mp_store_followers as feed_follows', function ($join) use ($customer): void {
                $join
                    ->on('feed_follows.store_id', '=', 'ec_products.store_id')
                    ->where('feed_follows.customer_id', '=', $customer->getKey());
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
            ])
            // Keep feed random while still giving followed stores a mild ranking boost.
            ->orderByRaw('(RAND() + CASE WHEN feed_follows.id IS NULL THEN 0 ELSE 0.35 END) DESC')
            ->paginate($perPage);
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
}
