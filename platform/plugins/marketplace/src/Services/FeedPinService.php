<?php

namespace Botble\Marketplace\Services;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Enums\FeedPinTypeEnum;
use Botble\Marketplace\Enums\StoreStatusEnum;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\FeedPin;
use Botble\Marketplace\Models\Store;
use Illuminate\Support\Collection;

class FeedPinService
{
    public function activePinsQuery()
    {
        return FeedPin::query()->active()->orderBy('priority')->orderBy('id');
    }

    /**
     * Ordered products to prepend on feed page 1 (published, valid store).
     */
    public function resolvePinnedProducts(): Collection
    {
        $pins = $this->activePinsQuery()->get();
        $seen = [];

        $vendorLimit = (int) MarketplaceHelper::getSetting('feed_vendor_pin_product_limit', 3);
        $vendorLimit = max(1, min(20, $vendorLimit));

        $orderedIds = [];

        foreach ($pins as $pin) {
            $pinType = $pin->pin_type instanceof FeedPinTypeEnum
                ? $pin->pin_type->getValue()
                : (string) $pin->pin_type;

            if ($pinType === FeedPinTypeEnum::PRODUCT) {
                $product = $this->resolveProductPin((int) $pin->target_id);
                if ($product && ! isset($seen[$product->getKey()])) {
                    $seen[$product->getKey()] = true;
                    $orderedIds[] = $product->getKey();
                }
            } elseif ($pinType === FeedPinTypeEnum::VENDOR_STORE) {
                foreach ($this->resolveVendorPinProducts((int) $pin->target_id, $vendorLimit) as $product) {
                    if (! isset($seen[$product->getKey()])) {
                        $seen[$product->getKey()] = true;
                        $orderedIds[] = $product->getKey();
                    }
                }
            }
        }

        if ($orderedIds === []) {
            return collect();
        }

        $loaded = Product::query()
            ->whereIn('id', $orderedIds)
            ->with(['slugable', 'store', 'store.slugable'])
            ->get()
            ->keyBy('id');

        return collect($orderedIds)
            ->map(fn (int $id) => $loaded->get($id))
            ->filter()
            ->values();
    }

    /**
     * All product IDs that should be excluded from organic feed (avoid duplicates).
     */
    public function excludedProductIdsForOrganic(): array
    {
        return $this->resolvePinnedProducts()->pluck('id')->all();
    }

    protected function resolveProductPin(int $productId): ?Product
    {
        return Product::query()
            ->whereKey($productId)
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->where('is_variation', 0)
            ->whereNotNull('store_id')
            ->whereHas('store', fn ($q) => $q->where('status', StoreStatusEnum::PUBLISHED))
            ->select('ec_products.id')
            ->first();
    }

    protected function resolveVendorPinProducts(int $storeId, int $limit): Collection
    {
        $store = Store::query()
            ->whereKey($storeId)
            ->where('status', StoreStatusEnum::PUBLISHED)
            ->first();

        if (! $store) {
            return collect();
        }

        return Product::query()
            ->where('store_id', $store->getKey())
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->where('is_variation', 0)
            ->select('ec_products.id')
            ->latest('id')
            ->limit($limit)
            ->get();
    }
}
