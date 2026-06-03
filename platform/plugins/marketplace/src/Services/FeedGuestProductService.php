<?php

namespace Botble\Marketplace\Services;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Services\Products\StoreProductService;
use Botble\Ecommerce\Services\StoreProductTagService;
use Botble\Marketplace\Enums\StoreStatusEnum;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\FeedGuestProductRequest;
use Botble\Marketplace\Models\Store;
use Botble\Media\Facades\RvMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class FeedGuestProductService
{
    public function __construct(
        protected StoreProductService $storeProductService,
        protected StoreProductTagService $storeProductTagService
    ) {
    }

    public function resolveGuestPostStore(): ?Store
    {
        $storeId = MarketplaceHelper::getGuestFeedPostStoreId();

        if ($storeId <= 0) {
            return null;
        }

        return Store::query()
            ->whereKey($storeId)
            ->where('status', StoreStatusEnum::PUBLISHED)
            ->first();
    }

    public function createFromRequest(FeedGuestProductRequest $request, ?Customer $customer = null): Product
    {
        $store = $this->resolveGuestPostStore();

        if (! $store) {
            throw ValidationException::withMessages([
                'store' => [__('Guest posting is not available. Please contact the site administrator.')],
            ]);
        }

        $product = new Product();
        $product->status = BaseStatusEnum::PUBLISHED;
        $product->product_type = EcommerceHelper::getCurrentCreationContextProductType() === ProductTypeEnum::DIGITAL
            ? ProductTypeEnum::DIGITAL
            : ProductTypeEnum::PHYSICAL;

        $posterLine = $this->buildPosterAttributionLine($request, $customer);
        $description = trim((string) $request->input('description', ''));
        if ($posterLine !== '') {
            $description = $description === ''
                ? $posterLine
                : $description . "\n\n" . $posterLine;
        }

        $payload = [
            'name' => $request->input('name'),
            'description' => $description,
            'content' => $request->input('content'),
            'categories' => $request->input('categories', []),
            'price' => $request->input('price', 0),
            'quantity' => $request->input('quantity', 0),
            'sku' => $request->input('sku') ?: $product->generateSku(),
            'sale_type' => 0,
            'stock_status' => 'in_stock',
            'with_storehouse_management' => 1,
            'allow_checkout_when_out_of_stock' => 1,
            'images' => $this->uploadProductImages($request),
        ];

        $request->merge($payload);

        $product = $this->storeProductService->execute($request, $product);
        $product->store_id = $store->getKey();

        if ($customer) {
            $product->created_by_id = $customer->getKey();
            $product->created_by_type = $customer::class;
        } else {
            $product->created_by_id = 0;
            $product->created_by_type = Customer::class;
        }

        $product->stock_status = 'in_stock';
        $product->status = BaseStatusEnum::PUBLISHED;
        $product->save();

        $this->storeProductTagService->execute($request, $product);

        return $product;
    }

    protected function buildPosterAttributionLine(FeedGuestProductRequest $request, ?Customer $customer): string
    {
        if ($request->boolean('register_as_vendor')) {
            $name = BaseHelper::clean((string) $request->input('vendor_register_name'));
            $email = BaseHelper::clean((string) $request->input('vendor_register_email'));
        } elseif ($customer) {
            $name = $customer->name;
            $email = $customer->email;
        } else {
            return __('Posted by :name', ['name' => __('Guest')]);
        }

        if ($name === '' && $email === '') {
            return '';
        }

        if ($email !== '') {
            return __('Posted by :name (:email)', ['name' => $name ?: __('Guest'), 'email' => $email]);
        }

        return __('Posted by :name', ['name' => $name]);
    }

    /**
     * @return list<string>
     */
    protected function uploadProductImages(FeedGuestProductRequest $request): array
    {
        $files = $request->file('images', []);

        if (! is_array($files)) {
            $files = array_filter([$files]);
        }

        $urls = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $result = RvMedia::handleUpload($file, 0, 'feed-guest-products');

            if (! empty($result['error'])) {
                throw ValidationException::withMessages([
                    'images' => [$result['message'] ?? __('Unable to upload product image.')],
                ]);
            }

            $media = $result['data'] ?? null;
            $url = is_object($media) ? ($media->url ?? null) : null;

            if ($url) {
                $urls[] = $url;
            }
        }

        if ($urls === []) {
            throw ValidationException::withMessages([
                'images' => [__('Please upload a product image.')],
            ]);
        }

        return $urls;
    }
}
