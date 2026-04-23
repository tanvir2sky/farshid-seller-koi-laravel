<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\PopupAds\Http\Controllers'], function (): void {
    AdminHelper::registerRoutes(function (): void {
        Route::group(['prefix' => 'popup-ads', 'as' => 'popup-ads.'], function (): void {
            Route::resource('', 'PopupAdController')->parameters(['' => 'popupAd']);

            Route::get('{popupAd}/analytics', [
                'as'         => 'analytics',
                'uses'       => 'PopupAdAnalyticsController@index',
                'permission' => 'popup-ads.analytics',
            ]);
        });
    });

    if (defined('THEME_MODULE_SCREEN_NAME')) {
        Theme::registerRoutes(function (): void {
            Route::post('popup-ads/impression', [
                'as'   => 'public.popup-ads.impression',
                'uses' => 'PublicController@recordImpression',
            ]);

            Route::post('popup-ads/click', [
                'as'   => 'public.popup-ads.click',
                'uses' => 'PublicController@recordClick',
            ]);
        });
    }
});
