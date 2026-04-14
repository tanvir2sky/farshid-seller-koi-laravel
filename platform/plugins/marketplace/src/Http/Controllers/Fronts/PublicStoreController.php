<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Services\Products\GetProductService;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Forms\ContactStoreForm;
use Botble\Marketplace\Http\Requests\Fronts\CheckStoreUrlRequest;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Models\StoreCategory;
use Botble\Media\Facades\RvMedia;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\SeoHelper\SeoOpenGraph;
use Botble\Slug\Facades\SlugHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PublicStoreController extends BaseController
{
    public function getStores(Request $request)
    {
        Theme::breadcrumb()
            ->add(__('Stores'), route('public.stores'));

        $pageTitle = __('Stores');
        $pageDescription = __('Stores');

        $condition = [];

        $search = BaseHelper::stringify(BaseHelper::clean($request->input('q')));
        if ($search) {
            $condition[] = ['name', 'LIKE', '%' . $search . '%'];
        }

        $activeStoreCategory = null;

        $categoryId = $request->query('category', $request->input('category'));
        if ($categoryId !== null && $categoryId !== '') {
            $categoryId = (int) $categoryId;
            if ($categoryId > 0) {
                $activeStoreCategory = StoreCategory::query()
                    ->wherePublished()
                    ->whereKey($categoryId)
                    ->first();
            }
        }

        if ($activeStoreCategory) {
            Theme::breadcrumb()->add(
                $activeStoreCategory->name,
                route('public.stores', array_filter([
                    'category' => $activeStoreCategory->getKey(),
                    'q' => $request->query('q'),
                ]))
            );
            $pageTitle = $activeStoreCategory->name . ' — ' . __('Stores');
            $pageDescription = $activeStoreCategory->description
                ? Str::limit(strip_tags((string) $activeStoreCategory->description), 300)
                : $pageTitle;
        }

        SeoHelper::setTitle($pageTitle)->setDescription($pageDescription);

        $with = [
            'slugable',
            'categories' => function ($query): void {
                $query
                    ->where('mp_store_categories.status', BaseStatusEnum::PUBLISHED)
                    ->with('slugable');
            },
        ];
        if (EcommerceHelper::isReviewEnabled()) {
            $with['reviews'] = function ($query): void {
                $query->where([
                    'ec_products.status' => BaseStatusEnum::PUBLISHED,
                    'ec_reviews.status' => BaseStatusEnum::PUBLISHED,
                ]);
            };
        }

        $storesQuery = Store::query()
            ->wherePublished()
            ->where($condition)
            ->with($with)
            ->withCount([
                'products' => function ($query): void {
                    $query
                        ->where('is_variation', 0)
                        ->wherePublished();
                },
            ]);

        if ($activeStoreCategory) {
            $categoryIds = StoreCategory::getSelfAndDescendantIds($activeStoreCategory);
            $storesQuery->whereHas('categories', function ($query) use ($categoryIds): void {
                $query
                    ->whereIn('mp_store_categories.id', $categoryIds)
                    ->where('mp_store_categories.status', BaseStatusEnum::PUBLISHED);
            });
        }

        $stores = $storesQuery
            ->orderByDesc('priority')
            ->orderByDesc('created_at')
            ->paginate(12);

        $storeFilterCategories = StoreCategory::query()
            ->wherePublished()
            ->with('slugable')
            ->orderBy('order')
            ->get();

        return Theme::scope(
            'marketplace.stores',
            compact('stores', 'storeFilterCategories', 'activeStoreCategory'),
            MarketplaceHelper::viewPath('stores', false)
        )->render();
    }

    public function getStoresByCategory(Request $request, string $slug)
    {
        $slug = BaseHelper::clean((string) $slug);
        $categorySlug = $slug !== ''
            ? SlugHelper::getSlug($slug, SlugHelper::getPrefix(StoreCategory::class, '', false))
            : null;

        if (! $categorySlug) {
            abort(404);
        }

        $category = StoreCategory::query()
            ->wherePublished()
            ->whereKey($categorySlug->reference_id)
            ->first();

        if (! $category) {
            abort(404);
        }

        return redirect()->to(
            route('public.stores', array_filter([
                'category' => $category->getKey(),
                'q' => $request->query('q'),
            ]))
        );
    }

    public function getStore(
        string $key,
        Request $request,
        GetProductService $productService
    ) {
        $slug = SlugHelper::getSlug($key, SlugHelper::getPrefix(Store::class));

        abort_unless($slug, 404);

        $condition = [
            'mp_stores.id' => $slug->reference_id,
            'mp_stores.status' => BaseStatusEnum::PUBLISHED,
        ];

        if (Auth::check() && $request->input('preview')) {
            Arr::forget($condition, 'status');
        }

        $store = Store::query()
            ->wherePublished()
            ->with(['slugable', 'metadata'])
            ->where($condition)
            ->firstOrFail();

        if ($store->slugable->key !== $slug->key) {
            return redirect()->to($store->url);
        }

        SeoHelper::setTitle($store->name)->setDescription($store->description);

        $meta = new SeoOpenGraph();

        if ($store->logo) {
            $meta->setImage(RvMedia::getImageUrl($store->logo));
        }
        $meta->setDescription($store->description);
        $meta->setUrl($store->url);
        $meta->setTitle($store->name);

        SeoHelper::setSeoOpenGraph($meta);

        Theme::breadcrumb()
            ->add(__('Stores'), route('public.stores'))
            ->add($store->name, $store->url);

        $storeProductCategories = ProductCategory::query()
            ->wherePublished()
            ->whereHas('products', function ($query) use ($store): void {
                $query
                    ->where('ec_products.store_id', $store->getKey())
                    ->wherePublished();
            })
            ->with('slugable')
            ->orderBy('name')
            ->get();

        $allowedCategoryIds = $storeProductCategories->pluck('id')->all();
        $incomingCategories = array_values(array_unique(array_filter(array_map(
            static fn ($id): int => (int) $id,
            (array) $request->input('categories', [])
        ))));
        $request->merge([
            'categories' => array_values(array_intersect($incomingCategories, $allowedCategoryIds)),
        ]);

        $filterCategoryIds = array_values(array_map(
            static fn ($id): int => (int) $id,
            (array) $request->input('categories', [])
        ));
        $storeProductFilterCategoryNames = $storeProductCategories
            ->filter(fn (ProductCategory $category) => in_array((int) $category->getKey(), $filterCategoryIds, true))
            ->sortBy(function (ProductCategory $category) use ($filterCategoryIds): int {
                $position = array_search((int) $category->getKey(), $filterCategoryIds, true);

                return $position === false ? PHP_INT_MAX : $position;
            })
            ->map(fn (ProductCategory $category) => (string) $category->name)
            ->values()
            ->all();

        $with = EcommerceHelper::withProductEagerLoadingRelations();

        $products = $productService->getProduct(
            $request,
            null,
            null,
            $with,
            [],
            ['is_variation' => 0, 'store_id' => $store->getKey()]
        );

        if ($request->ajax()) {
            $total = $products->total();
            $message = $total > 1 ? __(':total Products found', compact('total')) : __(
                ':total Product found',
                compact('total')
            );

            $view = Theme::getThemeNamespace('views.marketplace.stores.items');

            if (! view()->exists($view)) {
                $view = MarketplaceHelper::viewPath('stores.items', false);
            }

            return $this
                ->httpResponse()
                ->setData(view($view, compact('products', 'store'))->render())
                ->setMessage($message);
        }

        if (function_exists('admin_bar')) {
            admin_bar()
                ->registerLink(
                    trans('plugins/marketplace::store.edit_this_store'),
                    route('marketplace.store.edit', $store->getKey()),
                    null,
                    'marketplace.store.edit'
                );
        }

        $contactForm = ContactStoreForm::createFromArray(['id' => $store->getKey()]);

        return Theme::scope(
            'marketplace.store',
            compact('store', 'products', 'contactForm', 'storeProductCategories', 'storeProductFilterCategoryNames'),
            MarketplaceHelper::viewPath('store', false)
        )->render();
    }

    public function checkStoreUrl(CheckStoreUrlRequest $request)
    {
        abort_unless($request->ajax(), 404);

        $slug = $request->input('url');
        $slug = Str::slug($slug, '-', ! SlugHelper::turnOffAutomaticUrlTranslationIntoLatin() ? 'en' : false);

        $existing = SlugHelper::getSlug($slug, SlugHelper::getPrefix(Store::class));

        $this->httpResponse()->setData(['slug' => $slug]);

        if ($existing && $existing->reference_id != $request->input('reference_id')) {
            return $this->httpResponse()
                ->setError()
                ->setMessage(__('Not Available'));
        }

        return $this->httpResponse()->setMessage(__('Available'));
    }
}
