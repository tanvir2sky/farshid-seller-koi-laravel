<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Enums\ProductTypeEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Services\Products\StoreProductService;
use Botble\Ecommerce\Services\StoreProductTagService;
use Botble\Marketplace\Enums\StoreStatusEnum;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\FeedCommentRequest;
use Botble\Marketplace\Http\Requests\FeedProductRequest;
use Botble\Marketplace\Models\FeedComment;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Models\StoreFollower;
use Botble\Marketplace\Services\FeedQueryService;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class FeedController extends BaseController
{
    public function index(Request $request, FeedQueryService $feedQueryService)
    {
        $customer = auth('customer')->user();
        $products = $feedQueryService->getProducts($customer);
        $feedData = $this->buildFeedData($products, $customer->id, $feedQueryService);

        SeoHelper::setTitle(__('Feed'));
        Theme::breadcrumb()->add(__('Feed'), route('public.feed'));

        $categories = ProductCategory::query()->wherePublished()->orderBy('name')->get();
        $nextFeedItemsUrl = $products->hasMorePages()
            ? route('public.feed.items', ['page' => $products->currentPage() + 1])
            : null;

        return Theme::scope(
            'feed',
            [
                'products' => $products,
                'categories' => $categories,
                'nextFeedItemsUrl' => $nextFeedItemsUrl,
                ...$feedData,
            ]
        )->render();
    }

    public function followedStores()
    {
        $customer = auth('customer')->user();

        $stores = Store::query()
            ->whereHas('followers', function ($query) use ($customer): void {
                $query->where('customer_id', $customer->id);
            })
            ->with('slugable')
            ->orderBy('name')
            ->paginate(24);

        SeoHelper::setTitle(__('Followed stores'));
        Theme::breadcrumb()
            ->add(__('Feed'), route('public.feed'))
            ->add(__('Followed stores'), route('public.feed.followed-stores'));

        return Theme::scope('followed-stores', compact('stores'))->render();
    }

    public function loadMore(Request $request, FeedQueryService $feedQueryService): JsonResponse
    {
        $customer = auth('customer')->user();
        $products = $feedQueryService->getProducts($customer);
        $feedData = $this->buildFeedData($products, $customer->id, $feedQueryService);

        $html = Theme::partial('feed-items', [
            'products' => $products,
            ...$feedData,
        ]);

        $nextPageUrl = $products->hasMorePages()
            ? route('public.feed.items', ['page' => $products->currentPage() + 1])
            : null;

        return response()->json([
            'html' => $html,
            'next_page_url' => $nextPageUrl,
        ]);
    }

    public function comment(FeedCommentRequest $request): JsonResponse
    {
        $product = Product::query()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->whereNotNull('store_id')
            ->findOrFail($request->integer('product_id'));

        $comment = FeedComment::query()->create([
            'product_id' => $product->getKey(),
            'customer_id' => auth('customer')->id(),
            'content' => $request->input('content'),
        ]);

        $comment->load('customer');

        return response()->json([
            'message' => __('Comment added successfully.'),
            'comment' => [
                'author' => $comment->customer->name,
                'content' => BaseHelper::clean($comment->content),
                'created_at' => $comment->created_at?->diffForHumans(),
            ],
        ]);
    }

    public function follow(int|string $store): JsonResponse
    {
        $store = Store::query()
            ->whereKey($store)
            ->where('status', StoreStatusEnum::PUBLISHED)
            ->firstOrFail();

        StoreFollower::query()->firstOrCreate([
            'store_id' => $store->getKey(),
            'customer_id' => auth('customer')->id(),
        ]);

        return response()->json(['message' => __('You are now following this vendor.')]);
    }

    public function unfollow(int|string $store): JsonResponse
    {
        $store = Store::query()->whereKey($store)->firstOrFail();

        StoreFollower::query()
            ->where('store_id', $store->getKey())
            ->where('customer_id', auth('customer')->id())
            ->delete();

        return response()->json(['message' => __('Vendor unfollowed.')]);
    }

    public function storeProduct(
        FeedProductRequest $request,
        StoreProductService $storeProductService,
        StoreProductTagService $storeProductTagService
    ): JsonResponse {
        $customer = auth('customer')->user();
        abort_unless($customer->is_vendor && $customer->store?->id, 403);

        $product = new Product();
        $product->status = MarketplaceHelper::getSetting('enable_product_approval', 1)
            ? BaseStatusEnum::PENDING
            : BaseStatusEnum::PUBLISHED;
        $product->product_type = EcommerceHelper::getCurrentCreationContextProductType() === ProductTypeEnum::DIGITAL
            ? ProductTypeEnum::DIGITAL
            : ProductTypeEnum::PHYSICAL;

        $payload = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'content' => $request->input('content'),
            'categories' => $request->input('categories', []),
            'price' => $request->input('price', 0),
            'quantity' => $request->input('quantity', 0),
            'sku' => $request->input('sku') ?: $product->generateSku(),
            'sale_type' => 0,
            'stock_status' => 'in_stock',
            'with_storehouse_management' => 1,
            'allow_checkout_when_out_of_stock' => 1,
            'images' => array_filter((array) $request->input('images', [])),
        ];

        $request->merge($payload);

        $product = $storeProductService->execute($request, $product);
        $product->store_id = $customer->store->id;
        $product->created_by_id = $customer->getKey();
        $product->created_by_type = get_class($customer);
        $product->save();

        $storeProductTagService->execute($request, $product);

        return response()->json([
            'message' => __('Product created successfully.'),
            'redirect' => route('public.feed'),
        ]);
    }

    protected function buildFeedData($products, int $customerId, FeedQueryService $feedQueryService): array
    {
        $productIds = $products->getCollection()->pluck('id');
        $storeIds = $products->getCollection()->pluck('store_id')->filter()->unique();

        $likedProductIds = auth('customer')->user()->wishlist()
            ->whereIn('product_id', $productIds->all())
            ->pluck('product_id')
            ->flip();

        $likeCounts = $feedQueryService->getLikeCounts($productIds);

        $followedStores = StoreFollower::query()
            ->where('customer_id', $customerId)
            ->whereIn('store_id', $storeIds->all())
            ->pluck('store_id')
            ->flip();

        $comments = FeedComment::query()
            ->with('customer')
            ->whereIn('product_id', $productIds->all())
            ->latest()
            ->get()
            ->groupBy('product_id')
            ->map(fn ($items) => $items->take(5));

        $commentCounts = FeedComment::query()
            ->selectRaw('product_id, COUNT(*) as total')
            ->whereIn('product_id', $productIds->all())
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        return compact('likedProductIds', 'likeCounts', 'followedStores', 'comments', 'commentCounts');
    }
}
