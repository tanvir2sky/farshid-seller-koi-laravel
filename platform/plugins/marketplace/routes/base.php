<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Marketplace\Http\Controllers\MessageController;
use Botble\Marketplace\Http\Controllers\VendorBlockedController;
use Botble\Marketplace\Http\Controllers\VendorImpersonationController;
use Botble\Marketplace\Http\Controllers\WithdrawalInvoiceController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function (): void {
    Route::group(['namespace' => 'Botble\Marketplace\Http\Controllers'], function (): void {
        Route::group(['prefix' => 'marketplaces', 'as' => 'marketplace.'], function (): void {
            Route::group(['prefix' => 'stores', 'as' => 'store.'], function (): void {
                Route::post('{store}/login-as-vendor', [VendorImpersonationController::class, 'store'])
                    ->name('login-as-vendor')
                    ->permission('marketplace.store.impersonate_vendor');

                Route::resource('', 'StoreController')->parameters(['' => 'store']);
                Route::post('update-tax-info/{store}', [
                    'as' => 'update-tax-info',
                    'uses' => 'StoreController@updateTaxInformation',
                    'permission' => 'marketplace.store.edit',
                ]);
                Route::post('update-payout-info/{store}', [
                    'as' => 'update-payout-info',
                    'uses' => 'StoreController@updatePayoutInformation',
                    'permission' => 'marketplace.store.edit',
                ]);

                Route::get('view/{id}', [
                    'as' => 'view',
                    'uses' => 'StoreRevenueController@view',
                ])->wherePrimaryKey();

                Route::group(['prefix' => 'revenues', 'as' => 'revenue.'], function (): void {
                    Route::match(['GET', 'POST'], 'list/{id}', [
                        'as' => 'index',
                        'uses' => 'StoreRevenueController@index',
                        'permission' => 'marketplace.store.view',
                    ])->wherePrimaryKey();

                    Route::post('create/{id}', [
                        'as' => 'create',
                        'uses' => 'StoreRevenueController@store',
                    ])->wherePrimaryKey();
                });
            });

            Route::group(['prefix' => 'store-categories', 'as' => 'store-categories.'], function (): void {
                Route::resource('', 'StoreCategoryController')
                    ->parameters(['' => 'store_category']);

                Route::get('get-list-store-categories-for-select', [
                    'as' => 'get-list-store-categories-for-select',
                    'uses' => 'StoreCategoryController@getListForSelect',
                    'permission' => 'marketplace.store-category.index',
                ]);
            });

            Route::group(['prefix' => 'withdrawals', 'as' => 'withdrawal.'], function (): void {
                Route::resource('', 'WithdrawalController')
                    ->parameters(['' => 'withdrawal'])
                    ->except([
                        'create',
                        'store',
                    ]);

                Route::get('{withdrawal}/invoice', [WithdrawalInvoiceController::class, '__invoke'])
                    ->name('invoice');
            });

            Route::group(['prefix' => 'messages', 'as' => 'messages.'], function (): void {
                Route::get('/', [MessageController::class, 'index'])
                    ->name('index')
                    ->permission('marketplace.messages.index');

                Route::get('{message}', [MessageController::class, 'show'])
                    ->name('show')
                    ->permission('marketplace.messages.view');
            });

            Route::get('settings', [
                'as' => 'settings',
                'uses' => 'Settings\MarketplaceSettingController@edit',
            ]);

            Route::put('settings', [
                'as' => 'settings.update',
                'uses' => 'Settings\MarketplaceSettingController@update',
                'permission' => 'marketplace.settings',
            ]);

            Route::group(['prefix' => 'feed-pins', 'as' => 'feed-pins.'], function (): void {
                Route::resource('', 'FeedPinController')
                    ->parameters(['' => 'feed_pin']);
            });

            Route::group(['prefix' => 'unverified-vendors', 'as' => 'unverified-vendors.'], function (): void {
                Route::match(['GET', 'POST'], '/', [
                    'as' => 'index',
                    'uses' => 'UnverifiedVendorController@index',
                ]);

                Route::get('view/{id}', [
                    'as' => 'view',
                    'uses' => 'UnverifiedVendorController@view',
                    'permission' => 'marketplace.unverified-vendors.edit',
                ])->wherePrimaryKey();

                Route::post('approve/{id}', [
                    'as' => 'approve-vendor',
                    'uses' => 'UnverifiedVendorController@approveVendor',
                    'permission' => 'marketplace.unverified-vendors.edit',
                ])->wherePrimaryKey();

                Route::post('reject/{id}', [
                    'as' => 'reject-vendor',
                    'uses' => 'UnverifiedVendorController@rejectVendor',
                    'permission' => 'marketplace.unverified-vendors.edit',
                ])->wherePrimaryKey();

                Route::get('download-certificate/{id}', [
                    'as' => 'download-certificate',
                    'uses' => 'UnverifiedVendorController@downloadCertificate',
                    'permission' => 'marketplace.unverified-vendors.edit',
                ])->wherePrimaryKey();

                Route::get('download-government-id/{id}', [
                    'as' => 'download-government-id',
                    'uses' => 'UnverifiedVendorController@downloadGovernmentId',
                    'permission' => 'marketplace.unverified-vendors.edit',
                ])->wherePrimaryKey();
            });

            Route::group(['prefix' => 'vendors', 'as' => 'vendors.'], function (): void {
                Route::match(['GET', 'POST'], '/', [
                    'as' => 'index',
                    'uses' => 'VendorController@index',
                ]);

                Route::group(['permission' => 'marketplace.vendors.control'], function (): void {
                    Route::post('block/{id}', [VendorBlockedController::class, 'store'])->name('block');
                    Route::post('unblock/{id}', [VendorBlockedController::class, 'destroy'])->name('unblock');
                });
            });

            Route::group([
                'prefix' => 'reports',
                'as' => 'reports.',
                'permission' => 'marketplace.reports',
                'controller' => 'ReportController',
            ], function (): void {
                Route::get('', [
                    'as' => 'index',
                    'uses' => 'index',
                ]);

                Route::post('store-revenues', [
                    'as' => 'store-revenues',
                    'uses' => 'getStoreRevenues',
                ]);
            });
        });

        Route::group(['prefix' => 'ecommerce/products', 'as' => 'products.'], function (): void {
            Route::post('approve-product/{id}', [
                'as' => 'approve-product',
                'uses' => 'ProductController@approveProduct',
                'permission' => 'products.edit',
            ])->wherePrimaryKey();
        });
    });
});
