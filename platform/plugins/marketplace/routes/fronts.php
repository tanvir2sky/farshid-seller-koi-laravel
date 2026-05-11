<?php

use Botble\Base\Http\Middleware\DisableInDemoModeMiddleware;
use Botble\Base\Http\Middleware\RequiresJsonRequestMiddleware;
use Botble\DataSynchronize\Http\Controllers\UploadController;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Controllers\PrintShippingLabelController;
use Botble\Ecommerce\Http\Middleware\CheckProductSpecificationEnabledMiddleware;
use Botble\Marketplace\Http\Controllers\Fronts\BecomeVendorController;
use Botble\Marketplace\Http\Controllers\Fronts\ContactStoreController;
use Botble\Marketplace\Http\Controllers\Fronts\CustomerMessageController;
use Botble\Marketplace\Http\Controllers\Fronts\ExportProductController;
use Botble\Marketplace\Http\Controllers\Fronts\FeedController;
use Botble\Marketplace\Http\Controllers\Fronts\ImportProductController;
use Botble\Marketplace\Http\Controllers\Fronts\MessageController;
use Botble\Marketplace\Http\Controllers\Fronts\PublicStoreController;
use Botble\Marketplace\Http\Controllers\Fronts\SpecificationAttributeController;
use Botble\Marketplace\Http\Controllers\Fronts\SpecificationGroupController;
use Botble\Marketplace\Http\Controllers\Fronts\SpecificationTableController;
use Botble\Marketplace\Models\Store;
use Botble\Slug\Facades\SlugHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'Botble\Marketplace\Http\Controllers\Fronts',
], function (): void {
    Theme::registerRoutes(function (): void {
        $slugPrefix = SlugHelper::getPrefix(Store::class, 'stores');

        Route::get($slugPrefix, [PublicStoreController::class, 'getStores'])->name('public.stores');
        Route::get("$slugPrefix/category/{slug}", [PublicStoreController::class, 'getStoresByCategory'])
            ->name('public.stores.category');
        Route::get("$slugPrefix/{slug}", [PublicStoreController::class, 'getStore'])->name('public.store');

        Route::prefix('ajax/stores')
            ->name('public.ajax.')
            ->middleware(RequiresJsonRequestMiddleware::class)
            ->group(function (): void {
                Route::post('check-store-url', [PublicStoreController::class, 'checkStoreUrl'])->name('check-store-url');
                Route::post('{id}/contact', [ContactStoreController::class, 'store'])->name('stores.contact');
            });

        Route::middleware('customer')->prefix('customer/become-vendor')->name('marketplace.vendor.')->group(function (): void {
            Route::get('/', [BecomeVendorController::class, 'index'])->name('become-vendor');
            Route::post('/', [BecomeVendorController::class, 'store'])->name('become-vendor.post');
            Route::put('/', [BecomeVendorController::class, 'update'])->name('become-vendor.update');
            Route::get('download-certificate', [BecomeVendorController::class, 'downloadCertificate'])->name('become-vendor.download-certificate');
            Route::get('download-government-id', [BecomeVendorController::class, 'downloadGovernmentId'])->name('become-vendor.download-government-id');
        });

        Route::middleware('customer')->prefix('customer/messages')->name('customer.messages.')->group(function (): void {
            Route::get('/', [CustomerMessageController::class, 'index'])->name('index');
            Route::get('{store}', [CustomerMessageController::class, 'show'])->name('show')->wherePrimaryKey('store');
            Route::post('{store}', [CustomerMessageController::class, 'store'])->name('store')->wherePrimaryKey('store');
            Route::post('{store}/archive', [CustomerMessageController::class, 'archive'])->name('archive')->wherePrimaryKey('store');
            Route::post('{store}/unarchive', [CustomerMessageController::class, 'unarchive'])->name('unarchive')->wherePrimaryKey('store');
            Route::get('{store}/refresh', [CustomerMessageController::class, 'refresh'])->name('refresh')->wherePrimaryKey('store');
        });

        Route::middleware('customer')->get('feed', [FeedController::class, 'index'])->name('public.feed');

        Route::middleware('customer')->prefix('feed')->name('public.feed.')->group(function (): void {
            Route::get('followed-stores', [FeedController::class, 'followedStores'])->name('followed-stores');
            Route::get('items', [FeedController::class, 'loadMore'])->name('items');
            Route::post('comments', [FeedController::class, 'comment'])->name('comments');
            Route::post('follow/{store}', [FeedController::class, 'follow'])->name('follow')->wherePrimaryKey('store');
            Route::delete('follow/{store}', [FeedController::class, 'unfollow'])->name('unfollow')->wherePrimaryKey('store');
            Route::post('products', [FeedController::class, 'storeProduct'])->name('products.store');
        });
    });
});
